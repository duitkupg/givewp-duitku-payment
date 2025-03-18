<?php
/**
 * Plugin Name: Duitku for GiveWP
 * Plugin URI:  http://docs.duitku.com/
 * Description: Duitku Payment Gateway
 * Version:     1.3.0
 * Author:      Duitku
 * Author URI:  https://www.duitku.com
 * Contributors: anggiyawan@duitku.com, charisch09, rayhanduitku
 * Text Domain: give-Duitku
 * Domain Path: /languages
 */
// Exit if accessed directly.
if (!defined('ABSPATH')) {
  exit;
}

/**
 * Define constants.
 *
 * Required minimum versions, paths, urls, etc.
 */
if (!defined('GIVE_DUITKU_MIN_GIVE_VER')) {
  define('GIVE_DUITKU_MIN_GIVE_VER', '1.8.3');
}
if (!defined('GIVE_DUITKU_MIN_PHP_VER')) {
  define('GIVE_DUITKU_MIN_PHP_VER', '5.6.0');
}
if (!defined('GIVE_DUITKU_PLUGIN_FILE')) {
  define('GIVE_DUITKU_PLUGIN_FILE', __FILE__);
}
if (!defined('GIVE_DUITKU_PLUGIN_DIR')) {
  define('GIVE_DUITKU_PLUGIN_DIR', dirname(GIVE_DUITKU_PLUGIN_FILE));
}
if (!defined('GIVE_DUITKU_PLUGIN_URL')) {
  define('GIVE_DUITKU_PLUGIN_URL', plugin_dir_url(__FILE__));
}
if (!defined('GIVE_DUITKU_BASENAME')) {
  define('GIVE_DUITKU_BASENAME', plugin_basename(__FILE__));
}

if (!class_exists('Give_Duitku')):

  /**
   * Class Give_Duitku.
   */
  class Give_Duitku {

    /**
     * @var Give_Duitku The reference the *Singleton* instance of this class.
     */
    private static $instance;

    /**
     * Returns the *Singleton* instance of this class.
     *
     * @return Give_Duitku The *Singleton* instance.
     */
    public static function get_instance() {
      if (null === self::$instance) {
        self::$instance = new self();
      }

      return self::$instance;
    }

    /**
     * Private clone method to prevent cloning of the instance of the
     * *Singleton* instance.
     *
     * @return void
     */
    private function __clone() {

    }

    /**
     * Give_Duitku constructor.
     *
     * Protected constructor to prevent creating a new instance of the
     * *Singleton* via the `new` operator from outside of this class.
     */
    protected function __construct() {
      add_action('admin_init', array($this, 'check_environment'));
      add_action('plugins_loaded', array($this, 'init'));
    }

    /**
     * Init the plugin after plugins_loaded so environment variables are set.
     */
    public function init() {

      // Don't hook anything else in the plugin if we're in an incompatible environment.
      if (self::get_environment_warning()) {
        return;
      }

      add_filter('give_payment_gateways', array($this, 'register_gateway'));
      add_action('init', array($this, 'register_post_statuses'), 110);
	  
      $this->includes();
    }

    /**
     * The primary sanity check, automatically disable the plugin on activation if it doesn't
     * meet minimum requirements.
     *
     * Based on http://wptavern.com/how-to-prevent-wordpress-plugins-from-activating-on-sites-with-incompatible-hosting-environments
     */
    public static function activation_check() {
      $environment_warning = self::get_environment_warning(true);
      if ($environment_warning) {
        deactivate_plugins(plugin_basename(__FILE__));
        wp_die($environment_warning);
      }
    }

    /**
     * Check the server environment.
     *
     * The backup sanity check, in case the plugin is activated in a weird way,
     * or the environment changes after activation.
     */
    public function check_environment() {

      $environment_warning = self::get_environment_warning();
      if ($environment_warning && is_plugin_active(plugin_basename(__FILE__))) {
        deactivate_plugins(plugin_basename(__FILE__));
        $this->add_admin_notice('bad_environment', 'error', $environment_warning);
        if (isset($_GET['activate'])) {
          unset($_GET['activate']);
        }
      }

      // Check for if give plugin activate or not.
      $is_give_active = defined('GIVE_PLUGIN_BASENAME') ? is_plugin_active(GIVE_PLUGIN_BASENAME) : false;
      // Check to see if Give is activated, if it isn't deactivate and show a banner.
      if (is_admin() && current_user_can('activate_plugins') && !$is_give_active) {

        $this->add_admin_notice('prompt_give_activate', 'error', sprintf(__('<strong>Activation Error:</strong> You must have the <a href="%s" target="_blank">Give</a> plugin installed and activated for Duitku to activate.', 'give-duitku'), 'https://givewp.com'));

        // Don't let this plugin activate
        deactivate_plugins(plugin_basename(__FILE__));

        if (isset($_GET['activate'])) {
          unset($_GET['activate']);
        }

        return false;
      }

      // Check min Give version.
      if (defined('GIVE_DUITKU_MIN_GIVE_VER') && version_compare(GIVE_VERSION, GIVE_DUITKU_MIN_GIVE_VER, '<')) {

        $this->add_admin_notice('prompt_give_version_update', 'error', sprintf(__('<strong>Activation Error:</strong> You must have the <a href="%s" target="_blank">Give</a> core version %s+ for the Give Duitku add-on to activate.', 'give-duitku'), 'https://givewp.com', GIVE_DUITKU_MIN_GIVE_VER));

        // Don't let this plugin activate.
        deactivate_plugins(plugin_basename(__FILE__));

        if (isset($_GET['activate'])) {
          unset($_GET['activate']);
        }

        return false;
      }
    }

	
    public static function get_environment_warning($during_activation = false) {

      if (version_compare(phpversion(), GIVE_DUITKU_MIN_PHP_VER, '<')) {
        if ($during_activation) {
          $message = __('The plugin could not be activated. The minimum PHP version required for this plugin is %1$s. You are running %2$s. Please contact your web host to upgrade your server\'s PHP version.', 'give-duitku');
        } else {
          $message = __('The plugin has been deactivated. The minimum PHP version required for this plugin is %1$s. You are running %2$s.', 'give-duitku');
        }

        return sprintf($message, GIVE_DUITKU_MIN_PHP_VER, phpversion());
      }

      if (!function_exists('curl_init')) {

        if ($during_activation) {
          return __('The plugin could not be activated. cURL is not installed. Please contact your web host to install cURL.', 'give-duitku');
        }

        return __('The plugin has been deactivated. cURL is not installed. Please contact your web host to install cURL.', 'give-duitku');
      }

      return false;
    }

    /**
     * Give Duitku Includes.
     */
    private function includes() {

      // Checks if Give is installed.
      if (!class_exists('Give')) {
        return false;
      }

      if (is_admin()) {
        include GIVE_DUITKU_PLUGIN_DIR . '/includes/admin/give-duitku-activation.php';
        include GIVE_DUITKU_PLUGIN_DIR . '/includes/admin/give-duitku-settings.php';
      }

      include GIVE_DUITKU_PLUGIN_DIR . '/includes/give-duitku-gateway.php';
    }

    /**
     * Only have this method as it is mandatory from Give.
     */
    public function register_post_statuses() {

    }

    /**
     * Register the Duitku.
     *
     * @access      public
     * @since       1.0
     *
     * @param $gateways array
     *
     * @return array
     */
    public function register_gateway($gateways) {

		$gateways_add = array(
			'VC'  => array(
				'admin_label'    => __( 'Duitku Credit Card', 'give-duitku' ),
				'checkout_label' => __( 'Credit Card', 'give-duitku' ),
			),
			'BK'  => array(
				'admin_label'    => __( 'Duitku BCA KlikPay', 'give-duitku' ),
				'checkout_label' => __( 'BCA KlikPay', 'give-duitku' ),
			),
			'BT'  => array(
				'admin_label'    => __( 'Duitku Permata VA', 'give-duitku' ),
				'checkout_label' => __( 'Permata VA', 'give-duitku' ),
			),
			'B1'  => array(
				'admin_label'    => __( 'Duitku CIMB Niaga VA', 'give-duitku' ),
				'checkout_label' => __( 'CIMB Niaga VA', 'give-duitku' ),
			),
			'A1'  => array(
				'admin_label'    => __( 'Duitku ATM Bersama VA', 'give-duitku' ),
				'checkout_label' => __( 'ATM Bersama VA', 'give-duitku' ),
			),
			'I1'  => array(
				'admin_label'    => __( 'Duitku BNI VA', 'give-duitku' ),
				'checkout_label' => __( 'BNI VA', 'give-duitku' ),
			),
			'VA'  => array(
				'admin_label'    => __( 'Duitku Maybank VA', 'give-duitku' ),
				'checkout_label' => __( 'Maybank VA', 'give-duitku' ),
			),
			'FT'  => array(
				'admin_label'    => __( 'Duitku Retail', 'give-duitku' ),
				'checkout_label' => __( 'Ritel', 'give-duitku' ),
			),
			'OV'  => array(
				'admin_label'    => __( 'Duitku OVO Payment', 'give-duitku' ),
				'checkout_label' => __( 'OVO Payment', 'give-duitku' ),
			),
      'MG'  => array(
        'admin_label'    => __( 'Duitku Credit Card Facilitator', 'give-duitku' ),
        'checkout_label' => __( 'Credit Card Facilitator', 'give-duitku' ),
      ),
      'BC'  => array(
        'admin_label'    => __( 'Duitku BCA Virtual Account', 'give-duitku' ),
        'checkout_label' => __( 'BCA Virtual Account', 'give-duitku' ),
      ),
      'M2'  => array(
        'admin_label'    => __( 'Duitku Mandiri Virtual Account', 'give-duitku' ),
        'checkout_label' => __( 'Mandiri Virtual Account', 'give-duitku' ),
      ),
      'SP'  => array(
        'admin_label'    => __( 'Duitku Shopee Pay', 'give-duitku' ),
        'checkout_label' => __( 'Shopee Pay', 'give-duitku' ),
      ),
      'SA'  => array(
        'admin_label'    => __( 'Duitku Shopee Pay Apps', 'give-duitku' ),
        'checkout_label' => __( 'Shopee Pay Apps', 'give-duitku' ),
      ),
      'AG'  => array(
        'admin_label'    => __( 'Duitku Bank Artha Graha', 'give-duitku' ),
        'checkout_label' => __( 'Bank Artha Graha', 'give-duitku' ),
      ),
      'LA'  => array(
        'admin_label'    => __( 'Duitku LinkAja Apps (Percentage Fee)', 'give-duitku' ),
        'checkout_label' => __( 'LinkAja Apps', 'give-duitku' ),
      ),
      'LF'  => array(
        'admin_label'    => __( 'Duitku LinkAja Apps (Fixed Fee)', 'give-duitku' ),
        'checkout_label' => __( 'LinkAja Apps', 'give-duitku' ),
      ),
      'NC'  => array(
        'admin_label'    => __( 'Duitku Bank Neo Commerce', 'give-duitku' ),
        'checkout_label' => __( 'Bank Neo Commerce', 'give-duitku' ),
      ),
      'BR'  => array(
        'admin_label'    => __( 'Duitku BRIVA', 'give-duitku' ),
        'checkout_label' => __( 'BRIVA', 'give-duitku' ),
      ),
      'A2'  => array(
        'admin_label'    => __( 'Duitku POS Indonesia', 'give-duitku' ),
        'checkout_label' => __( 'POS Indonesia', 'give-duitku' ),
      ),
      'IR'  => array(
        'admin_label'    => __( 'Duitku Indomaret', 'give-duitku' ),
        'checkout_label' => __( 'Indomaret', 'give-duitku' ),
      ),
      'DA'  => array(
        'admin_label'    => __( 'Duitku DANA', 'give-duitku' ),
        'checkout_label' => __( 'DANA', 'give-duitku' ),
      ),
      'LQ'  => array(
        'admin_label'    => __( 'Duitku LinkAja', 'give-duitku' ),
        'checkout_label' => __( 'LinkAja', 'give-duitku' ),
      ),
      'NQ'  => array(
        'admin_label'    => __( 'Duitku Nobu', 'give-duitku' ),
        'checkout_label' => __( 'Nobu', 'give-duitku' ),
      ),
      'JP'  => array(
        'admin_label'    => __( 'Duitku Jenius Pay', 'give-duitku' ),
        'checkout_label' => __( 'Jenius Pay', 'give-duitku' ),
      ),
      'GQ'  => array(
        'admin_label'    => __( 'Duitku Gudang Voucher QRIS', 'give-duitku' ),
        'checkout_label' => __( 'Gudang Voucher QRIS', 'give-duitku' ),
      )

		);
			
		 $gateways = array_merge($gateways, $gateways_add);
		 $gateways = apply_filters( 'give_duitku_label', $gateways );
		 return $gateways;
    }
  }

  $GLOBALS['give_duitku'] = Give_Duitku::get_instance();
  register_activation_hook(__FILE__, array('Give_Duitku', 'activation_check'));

endif; // End if class_exists check.