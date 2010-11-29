<?php
/**
 * TemplateModifierSnip class.
 *
 * Shortens a string and appends "..." if necessary
 * 
 */
class TemplateModifierSnip extends TemplateModifier {

	public static function run($string, $length=32, $use_hellip=true) {
	   if (is_numeric($length)) {
	       if (strlen($string)>$length+2) {
	           $string     = substr($string, 0, $length);
	           if ($use_hellip) {
	               $string .= '&hellip;';
	           }
	           else {
	               $string .= '...';
	           }
	       }
	   }
	   return $string;
	}  //  function run
    
    
}   //  Snip
