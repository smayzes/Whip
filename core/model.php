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
    private $_fields_dirty      = array();
    
    
    public static function get_table() {
        $class_name = get_called_class();
        return $class_name::$_table;
    }
    public static function get_pk() {
        $class_name = get_called_class();
        return $class_name::$_pk;
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
        //  If new value equals old value, do nothing.
        //  This prevents fields from being marked as dirty.
            if (isset($this->_values[$field]) && $this->_values[$field] === $value) {
                return;
            }
        //  Set field value
            $this->_values[$field] = $value;
            if ($field != $class_name::$_pk) {
            //  If not the PK, mark field as dirty
                $this->mark_dirty($field);
            }
        }
        else {
        //  Auto-create property, but NOT the field
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
        if (isset($this->_values[$field])) {
        //  Return field value
            return $this->_values[$field];
        }
        else {
        //  Value not set.
        //  Check if field even exists.
            if (!in_array($field, array_keys($class_name::$_fields))) {
            //  Field does not exist. Throw Exception
                throw new WhipException(E_MODEL_FIELD_NOT_FOUND);
            }
            return null;
        }
    }   //  __get
    
    
    /**
     * mark_all_clean function.
     * 
     * Mark all fields as "clean", meaning they will NOT get
     * updated when the save() method is called.
     *
     * @access public
     */
    public function mark_all_clean() {
        $this->_fields_dirty = array();
    }   //  mark_all_clean
    
    /**
     * mark_clean function.
     * 
     * Mark a field as "clean", meaning it will NOT get
     * updated when the save() method is called.
     *
     * @access public
     * @param mixed $field_name
     */
    public function mark_clean($field_name) {
        if (in_array($field_name, $this->_fields_dirty)) {
            unset($this->_fields_dirty[$field_name]);
        }
    }   //  mark_clean
    
    /**
     * mark_dirty function.
     * 
     * Mark a field as "dirty", meaning it WILL get
     * updated when the save() method is called.
     *
     * @access public
     * @param mixed $field_name
     */
    public function mark_dirty($field_name) {
        $this->_fields_dirty[$field_name] = $field_name;
    }   //  mark_dirty
    
    /**
     * mark_all_dirty function.
     * 
     * Mark all fields as "dirty", meaning they WILL get
     * updated when the save() method is called.
     *
     * @access public
     */
    public function mark_all_dirty() {
        $class_name = get_called_class();
        $this->_fields_dirty = array();
        foreach($class_name::$_fields as $field) {
        //  Mark all except the primary key
            if ($field==self::$_pk) continue;
            $this->_fields_dirty[$field] = $field;
        }
    }   //  mark_all_dirty
    
    /**
     * get_dirty_fields function.
     * 
     * Return all field names that contain dirty values.
     *
     * @access public
     */
    public function get_dirty_fields() {
        return $this->_fields_dirty;
    }   //  get_dirty_fields
    
    
    /**
     * is_dirty function.
     * 
     * @access public
     * @return bool
     */
    public function is_dirty() {
        if (is_array($this->_fields_dirty) && count($this->_fields_dirty) > 0) {
            return true;
        }
        return false;
    }   //  is_dirty
    
    /**
     * is_clean function.
     * 
     * @access public
     * @return bool
     */
    public function is_clean() {
        return !$this->is_dirty();
    }   //  is_clean
    
    
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
