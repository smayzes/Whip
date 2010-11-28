<?php
/**
 * TemplateModifierNl2br class.
 *
 * Replaces newlines with <br />
 * 
 */
class TemplateModifierNl2br extends TemplateModifier {

	public static function run($string) {
	   return nl2br($string);
	}  //  function run
    
    
}   //  Nl2br
