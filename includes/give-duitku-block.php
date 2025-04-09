<?php
use Give\Framework\PaymentGateways\PaymentGatewayRegister;
class Duitku_Givewp_Block {
	private static $_instance;
	public static function get_instance() {
		if ( static::$_instance == null ) {
			static::$_instance = new static();
		}
		return static::$_instance;
	}
	public function __construct() {
		$this->add_actions();
		$this->add_filters();
	}

	/**
     * Register the payment gateway here, each of Payment Gateway Class must be defined in here
     */
	public function add_actions() {
        add_action('givewp_register_payment_gateway', static function ($paymentGatewayRegister) {
            include 'give-duitku-gateway-I1.php';
			include 'give-duitku-gateway-BT.php';
			include 'give-duitku-gateway-BV.php';
			include 'give-duitku-gateway-VC.php';
			include 'give-duitku-gateway-BK.php';
			include 'give-duitku-gateway-B1.php';
			include 'give-duitku-gateway-A1.php';
			include 'give-duitku-gateway-VA.php';
			include 'give-duitku-gateway-FT.php';
			include 'give-duitku-gateway-OV.php';
			include 'give-duitku-gateway-MG.php';
			include 'give-duitku-gateway-BC.php';
			include 'give-duitku-gateway-M2.php';
			include 'give-duitku-gateway-SP.php';
			include 'give-duitku-gateway-SA.php';
			include 'give-duitku-gateway-AG.php';
			include 'give-duitku-gateway-S1.php';
			include 'give-duitku-gateway-LA.php';
			include 'give-duitku-gateway-LF.php';
			include 'give-duitku-gateway-LQ.php';
			include 'give-duitku-gateway-NC.php';
			include 'give-duitku-gateway-BR.php';
			include 'give-duitku-gateway-A2.php';
			include 'give-duitku-gateway-IR.php';
			include 'give-duitku-gateway-DA.php';
			include 'give-duitku-gateway-NQ.php';
			include 'give-duitku-gateway-JP.php';
			include 'give-duitku-gateway-DM.php';
			include 'give-duitku-gateway-GQ.php';
			include 'give-duitku-gateway-SQ.php';
			
			$paymentGatewayRegister->registerGateway( DuitkuGatewayI1::class);
			$paymentGatewayRegister->registerGateway( DuitkuGatewayVC::class);
			$paymentGatewayRegister->registerGateway( DuitkuGatewayBK::class);
			$paymentGatewayRegister->registerGateway( DuitkuGatewayBT::class);
			$paymentGatewayRegister->registerGateway( DuitkuGatewayBV::class);
			$paymentGatewayRegister->registerGateway( DuitkuGatewayB1::class);
			$paymentGatewayRegister->registerGateway( DuitkuGatewayA1::class);
			$paymentGatewayRegister->registerGateway( DuitkuGatewayVA::class);
			$paymentGatewayRegister->registerGateway( DuitkuGatewayFT::class);
			$paymentGatewayRegister->registerGateway( DuitkuGatewayOV::class);
			$paymentGatewayRegister->registerGateway( DuitkuGatewayMG::class);
			$paymentGatewayRegister->registerGateway( DuitkuGatewayBC::class);
			$paymentGatewayRegister->registerGateway( DuitkuGatewayM2::class);
			$paymentGatewayRegister->registerGateway( DuitkuGatewaySP::class);
			$paymentGatewayRegister->registerGateway( DuitkuGatewaySA::class);
			$paymentGatewayRegister->registerGateway( DuitkuGatewayAG::class);
			$paymentGatewayRegister->registerGateway( DuitkuGatewayS1::class);
			$paymentGatewayRegister->registerGateway( DuitkuGatewayLA::class);
			$paymentGatewayRegister->registerGateway( DuitkuGatewayLF::class);
			$paymentGatewayRegister->registerGateway( DuitkuGatewayLQ::class);
			$paymentGatewayRegister->registerGateway( DuitkuGatewayNC::class);
			$paymentGatewayRegister->registerGateway( DuitkuGatewayBR::class);
			$paymentGatewayRegister->registerGateway( DuitkuGatewayA2::class);
			$paymentGatewayRegister->registerGateway( DuitkuGatewayIR::class);
			$paymentGatewayRegister->registerGateway( DuitkuGatewayDA::class);
			$paymentGatewayRegister->registerGateway( DuitkuGatewayNQ::class);
			$paymentGatewayRegister->registerGateway( DuitkuGatewayJP::class);
			$paymentGatewayRegister->registerGateway( DuitkuGatewayDM::class);
			$paymentGatewayRegister->registerGateway( DuitkuGatewayGQ::class);
			$paymentGatewayRegister->registerGateway( DuitkuGatewaySQ::class);
        });
		
	}

	public function add_filters() {
		// add_filter("")
	}
}
Duitku_Givewp_Block::get_instance();