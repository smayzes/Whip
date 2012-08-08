<?php
/**
 * TemplateModifierCount class.
 *
 * Json-encode an object / array / etc
 * 
 */
class TemplateModifierCount extends TemplateModifier {

	public static function run($value) {
	   if (is_array($value)) {
	       return count($value);
	   }
	   return false;
	}  //  function run
	
	
	public static function minus1($value) {
	   if (is_array($value)) {
	       return count($value) - 1;
	   }
	   return false;
	}  //  function run
    
    
}   //  Count
