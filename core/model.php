<?php

/**
 * Abstract WhipModel class.
 * 
 * @abstract
 * 
 * Whip : A non-restrictive PHP framework
 * Copyright (c) 2010 Menno van Ens (codefocus.ca) and Shawn Mayzes (mayzes.org)
 * Released under the GNU General Public License, Version 3
 */
abstract class WhipModel {
    /*
    protected $_pk              = 'id';
    protected $_table           = '';
    protected $_fields          = array();
    protected $_values          = array();
    */
    
    public static $_pk          = 'id';
    public static $_table       = 'foo';
    public static $_fields      = array();
    public $_values             = array();
    
    
    
    public static function get_table() {
        return self::$_table;
    }
    
    
    /**
     * __set function.
     * 
     * @access public
     * @param mixed $name
     * @param mixed $value
     * @return void
     */
    public function __set($field, $value) {
        $class_name = get_called_class();
        if (in_array($field, $class_name::$_fields)) {
        #   Set field value
            $this->_values[$field] = $value;
        }
        else {
        #   Create a new property
        #   Auto-create field / property
            throw new WhipException(E_MODEL_FIELD_NOT_FOUND);
            $this->_values[$field] = $value;
        }
    }   //  __set
    
    /**
     * __get function.
     * 
     * @access public
     * @param mixed $name
     * @return void
     */
    public function __get($field) {
        $class_name = get_called_class();
        if (in_array($field, array_keys($class_name::$_fields))) {
            if (isset($this->_values[$field])) {
            #   Return field value
                return $this->_values[$field];
            }
            else {
            #   Value not set. Throw Exception
                throw new WhipException(E_MODEL_FIELD_NOT_FOUND);
                return null;
            }
            
        }
        else {
            //debug_print_backtrace();
            //trigger_error(ERROR_FIELD_NOT_DEFINED, E_USER_WARNING);
            return null;
        }
    }   //  __get
    

    /**
     * __isset function.
     * 
     * @access public
     * @param mixed $name
     * @return void
     */
    public function __isset($name) {
        return isset($this->_values[$name]);
    }   //  __isset


}   //  model
