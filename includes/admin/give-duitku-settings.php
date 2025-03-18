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
  private $section_id;

  /**
   * @access private
   *
   * @var string $section_label
   */
  private $section_label;

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
    $this->section_label = __('duitku', 'give-duitku');

    if (is_admin()) {
      // Add settings.
      add_filter('give_settings_gateways', array($this, 'add_settings'), 99);
    }
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
	
  /**
   * Add plugin settings.
   *
   * @param array $settings Array of setting fields.
   *
   * @return array
   */
  public function add_settings($settings) {

    $give_duitku_settings = array(
		array(
			'name' => __('Duitku Settings', 'give-duitku'),
			'id'   => 'give_title_duitku',
			'type' => 'give_title',
		),
		array(
			'title' 	=> __('Merchant Code', 'give-duitku'),
			'desc' 		=> __('masukkan kode merchant anda.', 'give-duitku'),
			'id' 		=> 'duitku_merchant_code',
			'type' 		=> 'text',
			'default' 	=> '',
		),
		array(
			'title' 	=> __('API Key', 'give-duitku'),
			'desc' 		=> __(' Dapatkan API Key <a href=https://duitku.com>disini</a></small>.', 'give-duitku'),
			'id' 		=> 'duitku_api_key',
			'type' 		=> 'text',
			'css' 		=> 'width:25em;',
			'default' 	=> '',
		),
    array(
      'title'   => __('Credential Code', 'give-duitku'),
      'desc'    => __('Dapatkan Credential Code <a href=https://duitku.com>disini</a></small>. Credential Code digunakan untuk melakukan pembayaran via Credit Card Facilitator', 'give-duitku'),
      'id'    => 'duitku_credential_code',
      'type'    => 'text',
      'css'     => 'width:25em;',
      'default'   => '',
    ),
		array(
			'title' 	=> __('Endpoint', 'give-duitku'),
			'desc' 		=> __('Duitku endpoint API. Mohon isi merchant code dan api key sebelum mengakses endpoint.', 'give-duitku'),
			'id' 		=> 'duitku_endpoint',
			'type' 		=> 'text',
			'css' 		=> 'width:25em;',
			'default' 	=> '',
		),
		array(
				'title' => __('Duitku Debug', 'give-duitku'),
				'desc' => sprintf(__('Duitku Log dapat digunakan untuk melihat event, seperti notifikasi pembayaran.
                 <code>menu tools -> logs</code> ', ''), ""),
				'id' => 'duitku_debug',
				'type' => 'checkbox',
				'default' => 'no',
		),

    );

    return array_merge($settings, $give_duitku_settings);
  }
}

Give_Duitku_Settings::get_instance()->setup_hooks();