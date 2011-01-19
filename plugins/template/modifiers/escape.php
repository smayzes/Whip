<?php
/**
 * TemplateModifierEscape class.
 *
 * Escape a string
 * 
 */
class TemplateModifierEscape extends TemplateModifier {
    
    
    public static function js($value) {
        return addslashes($value);
    }   //  function js
    
    public static function html($value) {
        return htmlentities($value);
    }   //  function html
    
    public static function url($value) {
        return urlencode($value);
    }   //  function url
    
	public static function run($value) {
	   return addslashes($value);
	}  //  function run
    
    
}   //  Escape
