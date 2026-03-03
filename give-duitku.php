<?php
/**
 * Plugin Name: Duitku for GiveWP
 * Plugin URI:  http://docs.duitku.com/
 * Description: Duitku Payment Gateway
 * Version:     1.3.6
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
      $this->includes();
    }

    /**
     * Init the plugin after plugins_loaded so environment variables are set.
     */
    public function init() {

      // Don't hook anything else in the plugin if we're in an incompatible environment.
      if (self::get_environment_warning()) {
        return;
      }      
      add_action('init', array($this, 'register_post_statuses'), 110);
      add_filter('give_enabled_payment_gateways', array($this, 'give_filter_duitku_gateway'), 10, 2);
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
     * Give Duitku Includes all Function.
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
      
      //include GIVE_DUITKU_PLUGIN_DIR . '/includes/give-duitku-gateway.php'; //for Payment Gateway on Option Based Form Editor
      include GIVE_DUITKU_PLUGIN_DIR . '/includes/give-duitku-helper.php'; //for creating Duitku Logging
      include GIVE_DUITKU_PLUGIN_DIR . '/includes/give-duitku-listener.php'; //for creating Duitku Callback Listener
      include GIVE_DUITKU_PLUGIN_DIR . '/includes/give-duitku-block.php'; //for creating Payment Gateway and for Payment Gateway on Visual Form Builder
    }

    /**
     * Only have this method as it is mandatory from Give.
     */
    public function register_post_statuses() {

    }

    public function give_filter_duitku_gateway($gateway_list, $form_id) {
	  
      if ((false === strpos($_SERVER['REQUEST_URI'], '/wp-admin/post-new.php?post_type=give_forms'))
        
        && $form_id
        && !give_is_setting_enabled(give_get_meta($form_id, 'duitku_customize_duitku_donations', true, 'global'), array('enabled', 'global'))
      
      ) {
      
        unset($gateway_list['duitku']);
      
      }
      
      return $gateway_list;
    }
  }

  $GLOBALS['give_duitku'] = Give_Duitku::get_instance();
  register_activation_hook(__FILE__, array('Give_Duitku', 'activation_check'));

endif; // End if class_exists check.