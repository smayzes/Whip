<?php

/**
 * Form Field class.
 *
 *
 * Whip : A non-restrictive PHP framework
 * Copyright (c) 2010 Menno van Ens (codefocus.ca) and Shawn Mayzes (mayzes.org)
 * Released under the GNU General Public License, Version 3 
 *
 * @extends WhipPlugin
 */
abstract class FormField {
    protected $_name        = null;
    protected $_class       = null;
    protected $_id          = null;
    
    protected $_value       = null;
    
    public abstract function __toString();
    
    public function __construct() {
        return $this;
    }
    
//  Property: name
    public function name($value) {
        $this->_name = $value;
        return $this;
    }
//  Property: class
    public function class($value) {
        $this->_class = $value;
        return $this;
    }
//  Property: id
    public function id($value) {
        $this->_id = $value;
        return $this;
    }
//  Property: id
    public function value($value) {
        $this->_id = $value;
        return $this;
    }
    
//  Property get
    public function __get($property) {
        switch($property) {
        case 'name':    return $this->_name;
        case 'class':   return $this->_class;
        case 'id':      return $this->_id;
        case 'value':   return $this->_value;
        }
        return false;
    }   //  __get
    
    
}   //  class FormField


class FormFieldText extends FormField {
    /**
     * __toString function.
     *
     * Render the tag
     * 
     * @access public
     */
    public function __toString() {
    //  Build tag
        $tag = '<input type="text" ';
        if ($this->_name) {
            $tag .= 'name="'.$this->_name.'" ';
        }
        if ($this->_class) {
            $tag .= 'class="'.$this->_class.'" ';
        }
        if ($this->_id) {
            $tag .= 'id="'.$this->_id.'" ';
        }
        if ($this->_value) {
            $tag .= 'value="'.htmlentities($this->_value).'" ';
        }
        $tag .= '/>';
        return $tag;
    }   //  __toString
    
    public function __construct() {
        return $this;
    }
}   //  class FormFieldText


class FormFieldPassword extends FormField {
    /**
     * __toString function.
     *
     * Render the tag
     * 
     * @access public
     */
    public function __toString() {
    //  Build tag
        $tag = '<input type="password" ';
        if ($this->_name) {
            $tag .= 'name="'.$this->_name.'" ';
        }
        if ($this->_class) {
            $tag .= 'class="'.$this->_class.'" ';
        }
        if ($this->_id) {
            $tag .= 'id="'.$this->_id.'" ';
        }
        /*
        **  Don't render PASSWORD value
        if ($this->_value) {
            $tag .= 'value="'.htmlentities($this->_value).'" ';
        }
        */
        $tag .= '/>';
        return $tag;
    }   //  __toString
}   //  class FormFieldPassword


