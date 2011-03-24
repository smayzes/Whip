<?php


/**
 * ValidateModifierEmail class.
 * 
 */
class ValidatorEmail {
    
    /**
     * run the email validation rules.
     * 
     * @access public
     * @static
     * @param mixed $value
     * @param array $params. (default: array())
     * @return void
     */
    public static function run($value, $params = array()) {
    	return filter_var($value, FILTER_VALIDATE_EMAIL);
    	
    	// TODO: Some extra domain level validation
    }
    
    
}   //  Email
