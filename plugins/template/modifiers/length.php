<?php
/**
 * TemplateModifierLength class.
 *
 * Returns the length of a variable
 * 
 */
class TemplateModifierLength extends TemplateModifier {

	public static function run($value) {
	   return strlen($value);
	}  //  function run
    
    
}   //  Length
