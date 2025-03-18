<?php


/**
 * Request params filters.
 *
 * It truncate fields that have length limit, remove not allowed characters from other fields
 *
 * This feature is optional, you can control it with $sanitized (default: true)
 */
class Give_Gateway_Duitku_Validation
{
	
    // private $filters;

    // public function __construct()
    // {
        // $this->filters = array();
    // }
	
    /**
     * Validates and modify data
     * 
     * @param mixed[] $json
     */
    public static function duitkuRequest(&$json)
    {
		if (empty($json['email'])) {
			throw new Exception(__('Email is required', 'wc-duitku'));
		}
            
        if (!filter_var($json['email'], FILTER_VALIDATE_EMAIL)) {
        throw new Exception(__('The email address entered did not appear to be a valid email address. Please enter a valid email address.', 'wc-duitku'));
        }
		
        // if (filter_var($json['phoneNumber'], FILTER_VALIDATE_INT)) {
        //     throw new Exception(__('The phone number entered did not appear to be a valid phoneNumber address. Please enter a valid phone number.', 'wc-duitku'));
        // }

        if (filter_var($json['phoneNumber'], FILTER_VALIDATE_REGEXP, ["options" => ["regexp" => '/[a-zA-Z\~\!\@\#\$\%\^\&\*\{\}\;\:\,\<\.\>\?]/']])) {
            throw new Exception(__('The phone number entered did not appear to be a valid phoneNumber address. Please enter a valid phone number.', 'wc-duitku'));
        } 
	
        if (strlen($json['phoneNumber']) < 7) {
            throw new Exception(__('The phone number length minimum is 7 digits.', 'wc-duitku'));
        } 
	

  
}
}
