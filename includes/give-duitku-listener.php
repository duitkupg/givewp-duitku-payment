<?php

use Give\Framework\PaymentGateways\Commands\PaymentRefunded;
use Give\Framework\PaymentGateways\PaymentGateway;
use Give\Donations\Models\Donation;
use Give\Framework\PaymentGateways\Commands\RedirectOffsite;
use Give\Framework\PaymentGateways\Exceptions\PaymentGatewayException;
use Give\Log\ValueObjects\LogType;
use Give\Donations\ValueObjects\DonationStatus;
use Give\Framework\Http\Response\Types\RedirectResponse;
use Give\Helpers\Language;
use Give\Helpers\Form\Template;
use Give\Donations\Models\DonationNote;

class Duitku_Givewp_Listener {

	private static $_instance;
	
    public $environment;
	public $merchantCode;
	public $apikey;

	public $prefix;
	public static $log_enabled = false;

	public static function get_instance() {
		if ( self::$_instance == null ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	public function __construct() {
		add_action('rest_api_init', [$this, 'register_routes']);
	}

	public function register_routes() {
		register_rest_route('duitku', '/callback/', array(
			'methods' => 'POST',
			'callback' => [$this, 'listener_callback'],
			'args' => [
				'donationId' => [
					'required' => false,
					'validate_callback' => function ($param, $request, $key) {
						return is_string($param);
					}
				]
			],
			'permission_callback' => '__return_true', // Adjust security as needed
		));
		//error_log('success Registering REST API route'); // Debugging log
    }

	public function listener_callback(WP_REST_Request $queryParams) {

		$this->prefix		= give_get_option( 'duitku_merchant_prefix', false );
		$scheme = is_ssl() ? 'https' : 'http';
		$host = $queryParams->get_header( 'host' );
		$path = $queryParams->get_route();
		$query_string = $_SERVER['QUERY_STRING'] ?? '';
	
		$full_url = $scheme . '://' . $host . '/wp-json' . $path;

		$logCallback = $queryParams->get_params();
		$merchantOrderId = $queryParams->get_param('merchantOrderId');
		$resultCode = $queryParams->get_param('resultCode');
		$signature = $queryParams->get_param('signature');
		$amount = $queryParams->get_param('amount');
		//$donationId = absint($queryParams->get_param('donationId'));
		$transactionId = isset($merchantOrderId)? sanitize_text_field($merchantOrderId): null;

		$donationId = str_replace($this->prefix, '', $transactionId);

		// FIX CALLBACK URL make sure all work

		$par = array();
		$par['url'] = $full_url;
		$par['callback'] = $logCallback;
		Duitku_Givewp_Helper::logSuccess('Retrieve Callback', $transactionId, $par);

		$this->merchantCode	= give_get_option( 'duitku_merchant_code', false );
		$this->apikey		= give_get_option( 'duitku_api_key', false );
		$donation = Donation::find($donationId);

		$donationAmountCheck = $donation->amount->formatToDecimal();
		$signatureCheck = md5($this->merchantCode . $amount . $merchantOrderId . $this->apikey);

		if ($donation->status->getValue() == 'publish'){
			$message = 'Payment already success';
		} else if ($donationAmountCheck != $amount){
			$message = 'Wrong Amount';
		} else if ( $signature != $signatureCheck ) {
			$message = 'Wrong Signature';
		} else if ($resultCode == "00"){
			$message = $this->validate_transaction($transactionId);
		} else {
			$donation->status = DonationStatus::FAILED();
			$donation->save();
			$message = 'Payment not yet implemented';
		}       
	
		return new WP_REST_Response([
			'message' => $message,
			'donationId' => $donationId,
			'data' => [
				'merchantOrderId' => $transactionId,
				'resultcode' => $resultCode,
			]
		]);		
	}

	// Check Transaction and update statuses
	protected function validate_transaction($transactionId) {
		$this->environment		= give_get_option( 'duitku_environment', "sandbox" );
		$this->merchantCode	= give_get_option( 'duitku_merchant_code', false );
		$this->apikey		= give_get_option( 'duitku_api_key', false );
		$this->prefix		= give_get_option( 'duitku_merchant_prefix', false );
		self::$log_enabled	= give_get_option( 'duitku_debug', false ) == 'on' ? true : false;

		//environment for transactionStatus
		if($this->environment == "production") {
			$url = 'https://passport.duitku.com/webapi/api/merchant/transactionStatus';
		} else {
			$url = 'https://sandbox.duitku.com/webapi/api/merchant/transactionStatus';
		}

		//generate Signature
		$signature = md5($this->merchantCode . $transactionId . $this->apikey);

		// Prepare Parameters
		$params = array(
			'merchantCode' => $this->merchantCode, // API Key Merchant /
			'merchantOrderId' => $transactionId,
			'signature' => $signature,
		);

		$donationId = str_replace($this->prefix, '', $transactionId);
		$donation = Donation::find($donationId);

		// Set header to application/json
		$headers = array('Content-Type' => 'application/json');
		$response = wp_remote_post($url, array(
			'method' => 'POST', 'body' => json_encode($params), 'timeout' => 90, 'sslverify' => false, 'headers' => $headers,
		));
		
		$response_body = wp_remote_retrieve_body($response);
		$resp = json_decode($response_body);

		$par = array();
		$par['url'] = $url;
		$par['request'] = $params;
		$par['response'] = $resp;

		$response_code = wp_remote_retrieve_Response_Code($response);
		//Duitku_givewp_helper::log('Check Transaction', $order_id, $params);
		if ($response_code != '200') {
			Duitku_Givewp_Helper::log('Check Transaction Failed', $transactionId, $par);
		} else if ($resp->statusCode == '00') {
			Duitku_Givewp_Helper::logSuccess('Check Transaction Success', $transactionId, $par);

			//change donation status
			$message = 'Payment received successfully';
			$donation->status = DonationStatus::COMPLETE();
			DonationNote::create([
				'donationId' => $donation->id,
				'content' => 'Donation Completed from Duitku Gateway.'
			]);
			$donation->save();
			
			return $message;
		} else {
			Duitku_Givewp_Helper::log('Check Transaction Pending', $transactionId, $par);
		}
		
		$message = 'Payment not yet implemented';
		return $message;	
	}
	
}

Duitku_Givewp_Listener::get_instance();