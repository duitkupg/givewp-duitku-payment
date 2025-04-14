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

class DuitkuGatewayBT extends PaymentGateway {
	/**
	 * Initialize variabel, avoid depreceated in newer version php
	 */
    public $environment;
	public $merchantCode;
	public $apikey;
	public $credCode;
	public $expiryPeriod;
	public $merchantPrefix;
	public $sanitized;
	public $validation;
	public static $log_enabled = false;
	private static bool $script_loaded = false;

	/**
	 * Initialize id for Payment Gateway method, each one need to be different
	 */
	public static function id(): string {
		return 'BT';
	}

	public function getId(): string {
		return self::id();
	}

	/**
	 * Create Name for Payment Gateway
	 */
	public function getName(): string {
		return __( 'Duitku Permata VA', 'give-duitku' );
	}

	/**
	 * Create Label for Payment Gateway
	 */
	public function getPaymentMethodLabel(): string {
		return __( 'Permata VA', 'give-duitku' );
	}

	/**
	 * Display Gateway Fields or Information for v2 Donation Form (can be filled with instruction)
	 */
	public function getLegacyFormFieldMarkup( $formId, $args ) {
		return "<div class=''>
            <p>You will be redirected to Duitku Permata VA Payment Gateway</p>
        </div>";
	}

	/**
	 * Register a js file to Display Gateway Fields or Information for v3 Donation Form 
	 * (can be filled with instruction)
	 * The new Visual Donation Form Builder GiveWP need to use js file
	 */
	public function enqueueScript( int $formId ) {

		// Ensure loaded once
		if ( self::$script_loaded ) {
			return;
		}

		// Get handle
		$handle = $this::id();

		// Set script_loaded to TRUE
		self::$script_loaded = true;

		wp_enqueue_script(
			$handle,
			plugin_dir_url( __FILE__ ) . 'js/duitku-BT-gateway.js',
			[ 'react', 'wp-element' ],
			'1.0.0',
			true );
	}

	//  /**
    //  * Set the secure payment method for the Visual Donation Form Builder
    //  */
	public $secureRouteMethods = [
        'handleCreatePaymentRedirect',
    ];

	/**
     * Process the payment or donations, and hit inquiry to API Duitku
	 * createPayment function is default for the Visual Donation Form Builder
	 * when using V2 Donation Form it will be redirect to Duitku_Givewp_Purchase class
     */
	public function createPayment( Donation $donation, $gatewayData ): RedirectOffsite {
		//Get the configuration from setting
        $this->environment		= give_get_option( 'duitku_environment', "sandbox" );
		$this->merchantCode	= give_get_option( 'duitku_merchant_code', false );
		$this->apikey		= give_get_option( 'duitku_api_key', false );
		$this->credCode		= give_get_option( 'duitku_credential_code', "");
		$this->expiryPeriod = give_get_option( 'duitku_expiry_period', false);
		$this->merchantPrefix = give_get_option( 'duitku_merchant_prefix', false);
		self::$log_enabled	= give_get_option( 'duitku_debug', false ) == 'on' ? true : false;
		$this->sanitized    = true;
		$this->validation	= true;

        include_once dirname(__FILE__) . '/duitku/give-gateway-duitku-sanitized.php';
		include_once dirname(__FILE__) . '/duitku/give-gateway-duitku-validation.php';
		$expiryPeriod = intval($this->expiryPeriod);

		//Set the maximum Expiry Period
		if ($expiryPeriod > 1440 || $expiryPeriod=="" || $expiryPeriod<1) {
			$expiryPeriod = 1440;
		}

		//This will run if the configuration didnt set yet
		if (empty($this->merchantCode) || empty($this->apikey) || empty($this->environment))
			exit("Setting API configuration menu <b>Donations -> Settings -> Payment Gateways -> Duitku Settings</b>");

		if(!empty($this->merchantPrefix)){
			$payment_id = $this->merchantPrefix.$donation->id;
		} else {
			$payment_id = $donation->id;
		}

		//set the Duitku URL for inquiry
		if($this->environment == "production") {
			$url = 'https://passport.duitku.com/webapi/api/merchant/v2/inquiry';
		} else {
			$url = 'https://sandbox.duitku.com/webapi/api/merchant/v2/inquiry';
		} 

		//generate Signature
		$signature = md5($this->merchantCode . $payment_id . intval($donation->amount->formatToDecimal()) . $this->apikey);

		//Set the item detail or donation detail
		$item1 = array(
			'name' => $donation->formTitle,
			'price' => intval($donation->amount->formatToDecimal(),),
			'quantity' => 1
		);
		$itemDetails = array(
			$item1
		);

		//set the Merchant URL Callback
		$callbackUrl = site_url('/wp-json/duitku/callback');

		// Prepare Parameters
		$params = array(
			'merchantCode' 		=> $this->merchantCode, 
			'paymentAmount' 	=> intval($donation->amount->formatToDecimal()), 
			'paymentMethod' 	=> $donation->gatewayId,
			'merchantOrderId' 	=> $payment_id, //Your Merchant Order ID
			'productDetails' 	=> get_bloginfo() . ' Order : #' . $payment_id, //Your Product Detail
			'additionalParam' 	=> '', //Add some Information here
			'merchantUserInfo' 	=> '', //Your Merchant Info
			'customerVaName ' 	=> $donation->firstName . " " . $donation->lastName, // Customer Name
			'email' 			=> $donation->email, // Customer Email
			'phoneNumber' 		=> $donation->phone, // Customer Phone Number
			'itemDetails' 		=> $itemDetails, 
			'signature' 		=> $signature,
			'expiryPeriod'		=> $expiryPeriod,
			'returnUrl'			=> $this->generateSecureGatewayRouteUrl( // Return URL will hit check transaction later
				'handleCreatePaymentRedirect',
				$donation->id,
				[
					'donation-id' => $donation->id,
					'success-url' => $gatewayData['successUrl'],
					'merchant-order-id' => $payment_id,
				]
				),
			'callbackUrl' 		=> $callbackUrl // Callback URL will be used by Duitku to send HTTP Post, inform Payment Status
		);

		if ($donation->gatewayId === "MG") {
			$params['credCode'] = $this->credCode;
			if($this->environment == "production") {
				$url = 'https://passport.duitku.com/webapi/api/merchant/creditcard/inquiry';
			} else {
				$url = 'https://sandbox.duitku.com/webapi/api/merchant/creditcard/inquiry';
			} 
		}

		// Set header to application/json
		$headers = array('Content-Type' => 'application/json');

		// Sanitize and Validate Merchant and the Order first
		if ($this->sanitized) {
			Give_Gateway_Duitku_Sanitized::duitkuRequest($params);
		}
		if ($this->validation) {
			Give_Gateway_Duitku_Validation::duitkuRequest($params);
		}

		// Send this payload to Authorize.net for processing
		$response = wp_remote_post($url, array(
			'method' => 'POST', 'body' => json_encode($params), 'timeout' => 90, 'sslverify' => false, 'headers' => $headers,
		));

		// Retrieve the body's response if no errors found
		$response_body = wp_remote_retrieve_body($response);
		// Parse the response into something we can read
		$resp = json_decode($response_body);

		$par = array();
		$par['url'] = $url;
		$par['request'] = $params;
		$par['response'] = $resp;

		$response_code = wp_remote_retrieve_response_code($response);
		Duitku_Givewp_Helper::logRequest('Log Transaction', $payment_id, $par); // Log Request param
		//Duitku_Givewp_Helper::logRequest('Transaction Response', $payment_id, $resp); // Log Response param
		if (is_wp_error($response)) {
			Duitku_Givewp_Helper::log('We are currently experiencing problems
						trying to connect to this payment gateway. Sorry for the
						inconvenience.', $payment_id);
		}

		if (empty($response_body)) {
			Duitku_Givewp_Helper::log('Duitku\'s Response was empty.', $payment_id);
		}

		// means the inquiry was a success
		if ($response_code == '200') {
            return new RedirectOffsite( $resp->paymentUrl );
		} else {
			if ($response_code = "400") {	
				 //Duitku_Givewp_Helper::log($resp->Message, $payment_id, $response);	
                if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
                    $status_message = esc_html__('400: Something went wrong, please contact the merchant', 'give-duitku' );
                } else {
                    $status_message = esc_html__('else: Something went wrong, please contact the merchant', 'give-duitku' );
                }
                throw new PaymentGatewayException( $status_message );			
			}
			else
			{
				if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
                    $status_message = $e->getMessage();
                } else {
                    $status_message = esc_html__('else: Something went wrong, please contact the merchant', 'give-duitku' );
                }
                throw new PaymentGatewayException( $status_message );
			}
			return new RedirectOnsite( give_get_failed_transaction_uri('?payment-id=' . $payment_id) );
		}
	}

	//not yet implemented, but need to be here
    public function refundDonation( Donation $donation ): PaymentRefunded {
		// Set donation_id and payment_id
		$donation_id = $donation->id;
		$payment_id = $donation->gatewayTransactionId;
	}
 
	public function handleCreatePaymentRedirect(array $queryParams): RedirectResponse
    {
        $donationId = absint($queryParams['donation-id']);
        $successUrl = sanitize_text_field($queryParams['success-url']);
        $transactionId = sanitize_text_field($queryParams['merchant-order-id']);
		$donation = Donation::find($donationId);
		if ($donation->status->getValue() != 'publish'){
			$donation->status = DonationStatus::PROCESSING();
		}

        //change the status of donation
        $donation->gatewayTransactionId = $transactionId;
        $donation->save();

		// Redirect to receipt page
        return new RedirectResponse($successUrl);
    }
}