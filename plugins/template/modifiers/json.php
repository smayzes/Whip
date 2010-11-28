<?php
/**
 * TemplateModifierJson class.
 *
 * Json-encode an object / array / etc
 * 
 */
class TemplateModifierJson extends TemplateModifier {

	public static function run($value) {
	   return json_encode($value);
	}  //  function run
    
    
}   //  Json
