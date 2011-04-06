<?php

/**
 * Validator class.
 *
 * Whip : A non-restrictive PHP framework
 * Copyright (c) 2010 Menno van Ens (codefocus.ca) and Shawn Mayzes (mayzes.org)
 * Released under the GNU General Public License, Version 3
 * 
 * @extends WhipPlugin
 */

class Validator extends WhipPlugin {

    public function validate($type, $value) {
    	if ( !isset($type) || !isset($value) ) {
            throw new WhipPluginException('Improper parameters passed');
            return false;
        }
       	
        $modifier_class_name = $this->_load_validator($type);
    //  Execute the modifier
        $value = call_user_func($modifier_class_name.'::run', $value);
        
        return $value;
	}
	
	/**
     * _load_validator function.
     * 
     * @access private
     * @param mixed $name
     * @return void
     */
    private function _load_validator($name) {
    //  Get validator class name
        $validator_class_name = 'Validator'.ucfirst(strtolower($name));
        if (!class_exists($validator_class_name)) {
        //  Check name for security
            if (!preg_match('/^[a-z0-9_\.-]+$/i', $name)) {
                throw new WhipPluginException('Unsafe validator used: '.$name);
                return false;
            }
        //  Load validator file
            $validator_file_name = Whip::real_path(__DIR__).'validators/'.$name.'.php';
            
            if (!file_exists($validator_file_name)) {
                throw new WhipPluginException('Validator not found: '.$name);
                return false;
            }
            include_once($validator_file_name);
        }
        return $validator_class_name;
    }   //  function _load_validator
	
} // Validator