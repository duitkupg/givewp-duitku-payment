<?php


/**
 * Request params filters.
 *
 * It truncate fields that have length limit, remove not allowed characters from other fields
 *
 * This feature is optional
 */
class Give_Gateway_Duitku_Sanitized

{
    //  private $filters;

    // public function __construct()
    // {
    //     $this->filters = array();
    // }
    
    /**
     * Validates and modify data
     * 
     * @param mixed[] $json
     */

    public static function duitkuRequest(&$json)
    {
        if (isset($json['merchantUserInfo'])){            
            $json['merchantUserInfo'] = preg_replace('/[^a-z0-9 \.]/i', '', $json['merchantUserInfo']); //remove special characters
            $json['merchantUserInfo'] = substr($json['merchantUserInfo'],0, 255);            
       }

       if (isset($json['customerVaName'])){            
            $json['customerVaName'] = preg_replace('/[^a-z0-9 \.]/i', '', $json['customerVaName']); //remove special characters
            $json['customerVaName'] = substr($json['customerVaName'],0, 255);            
       }

       if (isset($json['productDetails'])){            
            $json['productDetails'] = preg_replace('/[^a-z0-9 \.]/i', '', $json['productDetails']); //remove special characters
            $json['productDetails'] = substr($json['productDetails'],0, 255);            
       }	   	   
	   
	     if (isset($json['expiryPeriod'])) {
		   $json['expiryPeriod'] = preg_replace('/[^0-9]/', '', $json['expiryPeriod']);
	     }	  	  

       if (isset($json['phoneNumber'])){
            $json['phoneNumber'] = preg_replace('/[^0-9]/', '', $json['phoneNumber']);            
       }

       if (isset($json['email'])){
        $json['email'] = filter_var($json['email'], FILTER_SANITIZE_EMAIL);
        $json['email'] = substr($json['email'],0, 255);        
       }

     return $json;
}
}
