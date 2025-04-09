<?php

/**
 * Class Give_Duitku_Settings
 *
 * @since 1.0.0
 */
class Give_Duitku_Settings {

  /**
   * @access private
   * @var Give_Duitku_Settings $instance
   */
  static private $instance;

  /**
   * @access private
   * @var string $section_id
   */
  private $section_id = '';

  /**
   * @access private
   *
   * @var string $section_label
   */
  private $section_label = '';

  /**
   * Give_Duitku_Settings constructor.
   */
  private function __construct() {

  }

  /**
   * get class object.
   *
   * @return Give_Duitku_Settings
   */
  static function get_instance() {
    if (null === static::$instance) {
      static::$instance = new static();
    }

    return static::$instance;
  }

  /**
   * Setup hooks.
   */
  public function setup_hooks() {

    $this->section_id    = 'duitku';
    $this->section_label = esc_html('Duitku', 'give-duitku');

    // Add payment gateway to payment gateways list.
    // add_filter( 'give_payment_gateways', array( $this, 'add_gateways' ) );

    if (is_admin()) {
      // Add settings.
      // add_filter('give_settings_gateways', array($this, 'add_settings'), 99);

      // Add section settings.
      add_filter( 'give_get_settings_gateways', array( $this, 'add_settings' ) );

      // Add section to payment gateways tab.
      add_filter( 'give_get_sections_gateways', array( $this, 'add_section' ) );

    }
  }
	
 
	
  /**
   * Add plugin settings.
   *
   * @param array $settings Array of setting fields.
   *
   * @return array
   */
  public function add_settings($settings) {

    if ( $this->section_id !== give_get_current_setting_section() ) {
      return $settings;
    }

    $give_duitku_settings = array(
		array(
			'id'   => $this->section_id,
      'type' => 'title',
		),
		array(
			'title' 	=> esc_html('Merchant Code', 'give-duitku'),
			'desc' 		=> esc_html('Masukkan kode merchant anda.', 'give-duitku'),
			'id' 		=> 'duitku_merchant_code',
			'type' 		=> 'text',
			'default' 	=> '',
		),
		array(
			'title' 	=> esc_html('API Key', 'give-duitku'),
			'desc' 		=> __(' Dapatkan API Key <a href=https://passport.duitku.com/merchant/Project>disini</a></small>.', 'give-duitku'),
			'id' 		=> 'duitku_api_key',
			'type' 		=> 'text',
			'css' 		=> 'width:25em;',
			'default' 	=> '',
		),
		array(
			'title' 	=> esc_html('Environment', 'give-duitku'),
			'desc' 		=> esc_html('Isi Merchant Code dan Api Key terlebih dahulu, sesuai dengan Environment.', 'give-duitku'),
			'id' 		=> 'duitku_environment',
			'css' 		=> 'width:25em;',
      'type'     => 'select',
      'class'    => 'wc-enhanced-select',
      'default'  => 'Sandbox',
      'options'  => [
          'sandbox' => __('Sandbox', 'give-duitku'),
          'production' => __('Production', 'give-duitku'),
      ],
		),
    array(
			'title' 	=> esc_html('Duitku Prefix', 'give-duitku'),
			'desc' 		=> esc_html('Prefix merchantOrderId. (Opsional).', 'give-duitku'),
			'id' 		=> 'duitku_merchant_prefix',
			'type' 		=> 'text',
			'css' 		=> 'width:25em;',
			'default' 	=> '',
		),
    array(
			'title' 	=> esc_html('Expiry Period', 'give-duitku'),
			'desc' 		=> esc_html('Expiry Period dalam satuan menit. Default: 1440.', 'give-duitku'),
			'id' 		=> 'duitku_expiry_period',
			'type' 		=> 'number',
			'css' 		=> 'width:25em;',
      'min'     => 1,
      'max'     => 1440,
			'default' 	=> 1440,
		),
		array(
				'title' => esc_html('Duitku Debug', 'give-duitku'),
				'desc' => sprintf(__('Duitku Log dapat digunakan untuk melihat event, seperti notifikasi pembayaran.
                 <code>menu tools -> logs</code> ', ''), ""),
				'id' => 'duitku_debug',
				'type' => 'checkbox',
				'default' => 'no',
		),
    array(
        'id'   => $this->section_id,
        'type' => 'sectionend',
      ),

    );
    
    return $give_duitku_settings;
  }

   /**
   * Add setting section.
   *
   * @param array $sections Array of section.
   *
   * @return array
   */
  public function add_section($sections) {
    $sections[$this->section_id] = $this->section_label;

    return $sections;
  }
}

Give_Duitku_Settings::get_instance()->setup_hooks();
