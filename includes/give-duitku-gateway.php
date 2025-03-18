<?php

if (!defined('ABSPATH')) {
  exit;
}

class Give_Duitku_Gateway {
	static private $instance;

	const QUERY_VAR           	= 'duitku_givewp_return';
	const QUERY_VAR_BACK		= 'return_back';
	const LISTENER_PASSPHRASE 	= 'duitku_givewp_listener_passphrase';

	public static $log_enabled = false;
	private function __construct() {
	 
		$this->endpoint		= give_get_option( 'duitku_endpoint', false );
		$this->merchantCode	= give_get_option( 'duitku_merchant_code', false );
		$this->apikey		= give_get_option( 'duitku_api_key', false );
		$this->credCode		= give_get_option( 'duitku_credential_code', false);
		self::$log_enabled	= give_get_option( 'duitku_debug', false ) == 'on' ? true : false;
		
		//add url callback
		add_action('init', array($this, 'return_listener'));
		
		//add url return back
		add_action('init', array($this, 'return_back'));
		
		//add payment method inquiry
		add_action('give_gateway_VC', array($this, 'process_payment'));
		add_action('give_gateway_BK', array($this, 'process_payment'));
		add_action('give_gateway_M1', array($this, 'process_payment'));
		add_action('give_gateway_BT', array($this, 'process_payment'));
		add_action('give_gateway_B1', array($this, 'process_payment'));
		add_action('give_gateway_A1', array($this, 'process_payment'));
		add_action('give_gateway_I1', array($this, 'process_payment'));
		add_action('give_gateway_VA', array($this, 'process_payment'));
		add_action('give_gateway_FT', array($this, 'process_payment'));
		add_action('give_gateway_OV', array($this, 'process_payment'));
		add_action('give_gateway_MG', array($this, 'process_payment'));
		
		//remove form cc
		add_action('give_VC_cc_form', '__return_false');
		add_action('give_BK_cc_form', '__return_false');
		add_action('give_M1_cc_form', '__return_false');
		add_action('give_BT_cc_form', '__return_false');
		add_action('give_B1_cc_form', '__return_false');
		add_action('give_A1_cc_form', '__return_false');
		add_action('give_I1_cc_form', '__return_false');
		add_action('give_VA_cc_form', '__return_false');
		add_action('give_FT_cc_form', '__return_false');
		add_action('give_OV_cc_form', '__return_false');
		add_action('give_MG_cc_form', '__return_false');
		
		add_filter('give_enabled_payment_gateways', array($this, 'give_filter_duitku_gateway'), 10, 2);

	}

	static function get_instance() {
		if (null === static::$instance) {
		  static::$instance = new static();
		}

		return static::$instance;
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

	private function create_payment($purchase_data) {
	  
		$form_id  = intval($purchase_data['post_data']['give-form-id']);
		$price_id = isset($purchase_data['post_data']['give-price-id']) ? $purchase_data['post_data']['give-price-id'] : '';

		
		// Collect payment data.
		$insert_payment_data = array(
		  'price'           => $purchase_data['price'],
		  'give_form_title' => $purchase_data['post_data']['give-form-title'],
		  'give_form_id'    => $form_id,
		  'give_price_id'   => $price_id,
		  'date'            => $purchase_data['date'],
		  'user_email'      => $purchase_data['user_email'],
		  'purchase_key'    => $purchase_data['purchase_key'],
		  'currency'        => give_get_currency($form_id, $purchase_data),
		  'user_info'       => $purchase_data['user_info'],
		  'status'          => 'pending',
		  'gateway'         => $purchase_data['post_data']["payment-mode"],
		);
		
		$insert_payment_data = apply_filters('give_create_payment', $insert_payment_data);

		// Record the pending payment.
		return give_insert_payment($insert_payment_data);
		
	}


	public static function get_listener_url($form_id) {
	  
		$passphrase = get_option(self::LISTENER_PASSPHRASE, false);
		
		if (!$passphrase) {
		  $passphrase = md5(site_url() . time());
		  update_option(self::LISTENER_PASSPHRASE, $passphrase);
		}

		$arg = array(
		  self::QUERY_VAR => $passphrase,
		  'form_id'       => $form_id,
		);
		
		return add_query_arg($arg, site_url('/'));
	}

	public static function get_return_url($form_id) {
	  
		$passphrase = get_option(self::LISTENER_PASSPHRASE, false);
		
		if (!$passphrase) {
		  $passphrase = md5(site_url() . time());
		  update_option(self::LISTENER_PASSPHRASE, $passphrase);
		}

		$arg = array(
		  self::QUERY_VAR_BACK => $passphrase,
		  'form_id'       => $form_id,
		);
		
		return add_query_arg($arg, site_url('/'));
	}
  
	public function process_payment($purchase_data) {
		give_validate_nonce($purchase_data['gateway_nonce'], 'give-gateway');
		
		if (empty($this->merchantCode) || empty($this->apikey) || empty($this->endpoint))
			exit("Setting API configuration menu <b>Donations -> Settings -> Payment Gateways -> Duitku Settings</b>");
		
		$payment_id = $this->create_payment($purchase_data);

		
		$url = $this->endpoint . '/api/merchant/v2/inquiry';
	
		//generate Signature
		$signature = md5($this->merchantCode . $payment_id . intval($purchase_data["price"]) . $this->apikey);

		$item1 = array(
			'name' => $purchase_data['post_data']['give-form-title'],
			'price' => intval($purchase_data["price"]),
			'quantity' => 1
		);		
		
		$itemDetails = array(
			$item1
		);
		
		// Prepare Parameters
		$params = array(
			'merchantCode' 		=> $this->merchantCode, // API Key Merchant /
			'paymentAmount' 	=> intval($purchase_data["price"]), //transform order into integer
			'paymentMethod' 	=> $purchase_data['post_data']["payment-mode"],
			'merchantOrderId' 	=> $payment_id,
			'productDetails' 	=> get_bloginfo() . ' Order : #' . $payment_id,
			'additionalParam' 	=> '',
			'merchantUserInfo' 	=> $purchase_data['user_info']['first_name'] . " " . $purchase_data['user_info']['last_name'],
			'customerVaName ' 	=> $purchase_data['user_info']['first_name'] . " " . $purchase_data['user_info']['last_name'],
			'email' 			=> $purchase_data['user_email'],
			'phoneNumber' 		=> '',
			'itemDetails' 		=> $itemDetails,
			'signature' 		=> $signature,
			
			'returnUrl' 		=> $this->get_return_url($payment_id),
			'callbackUrl' 		=> $this->get_listener_url($payment_id)
		);

		if ($purchase_data['post_data']["payment-mode"] === "MG") {
			$params['credCode'] = $this->credCode;
			$url = $this->endpoint . '/api/merchant/creditcard/inquiry';
		}

		$headers = array('Content-Type' => 'application/json');

		
		// Send this payload to Authorize.net for processing
		$response = wp_remote_post($url, array(
			'method' => 'POST', 'body' => json_encode($params), 'timeout' => 90, 'sslverify' => false, 'headers' => $headers,
		));
		
		
		// Retrieve the body's resopnse if no errors found
		$response_body = wp_remote_retrieve_body($response);
		$response_code = wp_remote_retrieve_response_code($response);
		
		$this->log('inquiry response', $payment_id, $response);
		if (is_wp_error($response)) {
			$this->log('We are currently experiencing problems
						trying to connect to this payment gateway. Sorry for the
						inconvenience.', $payment_id);
		}

		if (empty($response_body)) {
			$this->log('Duitku\'s Response was empty.', $payment_id);
		}

		// Parse the response into something we can read
		$resp = json_decode($response_body);

		// means the transaction was a success
		if ($response_code == '200') {
			
			// Redirect to thank you page
			wp_redirect( $resp->paymentUrl );
		} else {

			if ($response_code = "400") {	
				$this->log($resp->Message, $payment_id, $response);				
			}
			else
			{
				$this->log( 'Error: error processing payment.',  $payment_id, $response);
			}
			wp_redirect( give_get_failed_transaction_uri('?payment-id=' . $payment_id) );
			
		}
		return;
	}
	
	public function return_listener() {
		
		if (!isset($_GET[self::QUERY_VAR])) {
		  return;
		}
		
		
		if (!isset($_GET['form_id'])) {
		  exit;
		}
		
		
		$passphrase = get_option(self::LISTENER_PASSPHRASE, false);
		if (!$passphrase) {
		  return;
		}

		
		if ($_GET[self::QUERY_VAR] != $passphrase) {
		  return;
		}
		
		
		$order_id 	= trim(stripslashes($_REQUEST['merchantOrderId']));
		$status 	= trim(stripslashes($_REQUEST['resultCode']));
		$reference 	= trim(stripslashes($_REQUEST['reference']));
		
		if ($status == '00' && $this->validate_transaction($order_id, $reference)) {
			
			give_update_payment_status($order_id, 'publish');
			
			//duitku log success
			$this->log("duitku log success", $order_id, ($_REQUEST));
			
			
		} elseif ($status == '01') {
			
			give_update_payment_status( $order_id, 'processing' );
			
			//duitku log processing
			$this->log("duitku log processing.", $order_id, ($_REQUEST));
			
			//back page home
			wp_redirect( site_url('/') );
			
		} else {
			give_update_payment_status( $order_id, 'failed' );
			
			//duitku log failed
			$this->log("duitku log failed.", $order_id, ($_REQUEST));
			
			wp_redirect( give_get_failed_transaction_uri('?payment-id=' . $order_id) );
			
		}

	exit;
	}
  
	protected function validate_transaction($order_id, $reference) {

		//endpoint for transactionStatus
		$url = $this->endpoint . '/api/merchant/transactionStatus';

		//generate Signature
		$signature = md5($this->merchantCode . $order_id . $this->apikey);

		// Prepare Parameters
		$params = array(
			'merchantCode' => $this->merchantCode, // API Key Merchant /
			'merchantOrderId' => $order_id,
			'signature' => $signature,
			'reference' => $reference,
		);

		$headers = array('Content-Type' => 'application/json');;
				
		$response = wp_remote_post($url, array(
			'method' => 'POST', 'body' => json_encode($params), 'timeout' => 90, 'sslverify' => false, 'headers' => $headers,
		));

		$response_body = wp_remote_retrieve_body($response);
		$response_code = wp_remote_retrieve_response_code($response);

		if ($response_code == '200') {
			// Parse the response into something we can read
			$resp = json_decode($response_body);

			if ($resp->statusCode == '00') {
				return true;
			}

		} else {
			$resp = json_decode($response_body);
			$this->log('duitku log check transaction', $order_id, $resp);
		}

		return false;
	}
	
	public function return_back() {
			
		if (!isset($_GET[self::QUERY_VAR_BACK])) {
		  return;
		}
		
			
		if (!isset($_GET['form_id'])) {
		  exit;
		}
		
		
		$passphrase = get_option(self::LISTENER_PASSPHRASE, false);
		if (!$passphrase) {
		  return;
		}
		

		$order_id 	= trim(stripslashes($_REQUEST['merchantOrderId']));
		$status 	= trim(stripslashes($_REQUEST['resultCode']));
				
		
		if ( $status  == "01" ) {
			give_update_payment_status( $order_id, 'processing' );
				
			//duitku log processing
			$this->log("duitku log processing.", $order_id, ($_REQUEST));
				
			$return_url = add_query_arg( array(
				'payment-confirmation' => 'duitku',
				'payment-id'           => $order_id,

			), get_permalink( give_get_option( 'success_page' ) ) );
			
			wp_redirect($return_url);
			exit;
			
		} else if ( $status  == "02" ) {
			give_update_payment_status( $order_id, 'failed' );
				
			//duitku log failed
			$this->log("duitku log failed.", $order_id, ($_REQUEST));
			
			$return_url = give_get_failed_transaction_uri( '?payment-id=' . $payment_id );
			wp_redirect($return_url);
			exit;

		}
		return;
	}
	
	public static function log($message, $payment_id = null, $purchase_data = null) {
		
		if (self::$log_enabled) {
			if($purchase_data != null)
				$logdata = json_encode($purchase_data);
			else
				$logdata = "error";
		
			give_record_gateway_error( __( 'Duitku Logs', 'give-duitku' ),  sprintf( /* translators: %s: payment data */
		__($message . '. Payment data: %s', 'give-duitku'), json_encode($purchase_data)), $payment_id);
		}
	}

}
Give_Duitku_Gateway::get_instance();