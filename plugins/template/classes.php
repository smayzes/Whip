<?php

/**
 * TemplateBlock class.
 *
 * Used by the Template plugin to render portions of a template.
 *
 * Whip : A non-restrictive PHP framework
 * Copyright (c) 2010 Menno van Ens (codefocus.ca) and Shawn Mayzes (mayzes.org)
 * Released under the GNU General Public License, Version 3
 * 
 * @extends WhipPlugin
 */
abstract class TemplateBlock {
    public $content     = '';
    abstract public function render();
}   //  TemplateBlock


/**
 * TemplateBlockRoot class.
 * 
 * @extends TemplateBlock
 */
class TemplateBlockRoot extends TemplateBlock {
    public $children    = array();
    
    public function render() {
        foreach($this->children as &$child) {
            $child->render();
        }
    }   //  function render
    
    /**
     * add_child function.
     * 
     * @access public
     * @param mixed &$node
     */
    public function add_child(&$node) {
        $this->children[] = $node;
    }   //  function add_child
    
}   //  TemplateRoot


/**
 * TODO: Replace with just a simple string?
 *
 * TemplateBlockContent class.
 * 
 * @extends TemplateBlock
 */
/*
class TemplateBlockContent extends TemplateBlock {

    public function __construct($content) {
    //  Set content
        $this->content      = $content;
    }   //  function __construct
    
    public function render() {
        echo $this->content;
    }   //  function render
    
}   //  Content
*/


/**
 * TemplateBlockFunction class.
 * 
 * @extends TemplateBlock
 */
class TemplateBlockFunction extends TemplateBlock {
    public $children    = array();
    public $else        = null;
    public $function    = '';
    public $parameters  = array();
    
    /**
     * __construct function.
     * 
     * @access public
     * @param mixed $token
     * @return void
     */
    public function __construct($token) {
    //  Trim token
        $token_parts        = trim(substr($token, 1, -1));
    //  Split function and parameters
        $token_parts        = preg_split('/[\s]+/', $token_parts);
        $this->function     = array_shift($token_parts);
        $this->parameters   = $token_parts;
    }   //  function __construct
    
    /**
     * render function.
     * 
     * @access public
     * @return void
     */
    public function render() {
        foreach($this->children as &$child) {
            $child->render();
        }
    }   //  function render
    
    /**
     * add_child function.
     * 
     * @access public
     * @param mixed &$node
     */
    public function add_child(&$node) {
        if ($node instanceof TemplateBlockFunction &&
            Template::TOKEN_FUNCTION_ELSE == $node->function &&
            Template::TOKEN_FUNCTION_IF == $this->function) {
            $this->else = $node;
        }
        else {
            $this->children[] = $node;
        }
    }   //  function add_child
    
}   //  TemplateFunction




/**
 * TemplateBlockVariable class.
 * 
 * @extends TemplateBlock
 */
class TemplateBlockVariable extends TemplateBlock {
    public $variable    = '';
    public $parameters  = array();
    public $default     = null;

    /**
     * __construct function.
     * 
     * @access public
     * @param mixed $token
     * @return void
     */
    public function __construct($token) {
    //  Trim token
        $token_parts        = trim($token);
        
    //  Get default value
        $pos_default    = strpos($token_parts, Template::TOKEN_VARIABLE_DEFAULT);
        if (false!==$pos_default) {
            $this->default  = substr($token_parts, $pos_default + 1);
            $token_parts    = substr($token_parts, 0, $pos_default);
        }
        
    //  Split function and parameters
        $token_parts        = explode(Template::TOKEN_VARIABLE_MODIFIER, $token_parts);
        $this->variable     = array_shift($token_parts);
        $this->parameters   = $token_parts;
    }   //  __construct
    
    public function __toString() {
        return $this->variable.'('.implode(',', $this->parameters).')';
    }   //  function __toString
    
    public function render() {
        echo $this->variable;
    }   //  function render

}   //  TemplateVariable



/**
 * TemplateModifier class.
 * 
 */
abstract class TemplateModifier {
    
    static function run($value) {
        return $value;
    }
    
}   //  TemplateModifier



