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
    public static $_pk          = 'id';
    public static $_table       = 'TABLE_NOT_SET_IN_MODEL';
    public static $_fields      = array();
    public $_values             = array();
    
    
    public static function get_table() {
        return self::$_table;
    }
    public static function get_pk() {
        return self::$_pk;
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
            //throw new WhipException(E_MODEL_FIELD_NOT_FOUND);
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
            //  Return field value
                return $this->_values[$field];
            }
            else {
            //  Value not set.
                //throw new WhipException(E_MODEL_FIELD_NOT_FOUND);
                return null;
            }
            
        }
        else {
        //  Field does not exist. Throw Exception
            throw new WhipException(E_MODEL_FIELD_NOT_FOUND);
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
    
    
    /**
     * save function.
     *
     * Alias for Whip::Db()->save( $model );
     * 
     * @access public
     */
    public function save() {
        return Whip::Db()->save( $this );
    }   //  save
    
    
    
    
    /**
     * autofill function.
     *
     * Auto-fill this model with GET / POST values
     * 
     * @access public
     */
    public function autofill() {
        $class_name = get_called_class();
        foreach($class_name::$_fields as $field) {
            if (isset($_POST[$field])) {
            //  Set value
                $this->$field = $_POST[$field];
            }
            elseif (isset($_GET[$field])) {
            //  Set value
                $this->$field = $_GET[$field];
            }
        }   //  each field
    }   //  function autofill


}   //  model
