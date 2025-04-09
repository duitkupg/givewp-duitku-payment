<?php
use Give\Log\LogFactory as Log;
use Give\Log\ValueObjects\LogCategory;
class Duitku_Givewp_Helper {
	public static $log_enabled = false;
	

	public static function get_fields( $form_id, $column, $prefix = '' ) {
		if ( empty( $prefix ) ) {
			return give_get_option( $column );
		}
		return give_get_meta( $form_id, $prefix . $column, true );
	}

	public static function update_fields( $form_id, $column, $value, $prefix = '' ) {
		if ( empty( $prefix ) ) {
			return give_update_option( $column, $value );
		}

		return give_update_meta( $form_id, $prefix . $column, $value );
	}

	public static function log($message, $payment_id = null, $purchase_data = null) {
		self::$log_enabled	= give_get_option( 'duitku_debug', false ) == 'on' ? true : false;
		if (self::$log_enabled) {
			if($purchase_data != null){
				$title = __( $message.' '.$payment_id, 'give-duitku' );
				//$message = sprintf( __($logdata, 'give-duitku'));
				give_record_log( $title, $purchase_data, $payment_id, 'sale' );
			}
				
			else{
				$logdata =json_encode($purchase_data);
				give_record_gateway_error( __( 'Duitku Error Order '.$payment_id, 'give-duitku' ),  sprintf(
		__($message . '. Payment data: %s', 'give-duitku'), $logdata), $payment_id);
			}	
				
		
			
		}
	}

	public static function logRequest($message, $payment_id = null, $purchase_data = null) {
		self::$log_enabled	= give_get_option( 'duitku_debug', false ) == 'on' ? true : false;
		if (self::$log_enabled) {
			if($purchase_data != null){
				$title = __( $message.' '.$payment_id, 'give-duitku' );
				//$message = sprintf( __($logdata, 'give-duitku'));
				give_record_log( $title, $purchase_data, $payment_id, 'sale' );
			}
				
			else{
				$logdata =json_encode($purchase_data);
				give_record_gateway_error( __( 'Duitku Error Order '.$payment_id, 'give-duitku' ),  sprintf(
		__($message . '. Payment data: %s', 'give-duitku'), $logdata), $payment_id);
			}	
				
		
			
		}
	}

	public static function logSuccess($message, $payment_id = null, $purchase_data = null) {
		self::$log_enabled	= give_get_option( 'duitku_debug', false ) == 'on' ? true : false;
		if (self::$log_enabled) {
			if($purchase_data != null){
				$logdata = json_encode($purchase_data, JSON_PRETTY_PRINT);
				$title = __( $message.' '.$payment_id, 'give-duitku' );
				//$message = sprintf( __($logdata, 'give-duitku'));
				give_record_log( $title, $purchase_data, $payment_id, 'sale' );
			}
				
			else{
				$logdata =json_encode($purchase_data);
				give_record_gateway_error( __( 'Duitku Error Logs', 'give-duitku' ),  sprintf(
		__($message . '. Payment data: %s', 'give-duitku'), $logdata), $payment_id);
			}
		}
	}
}