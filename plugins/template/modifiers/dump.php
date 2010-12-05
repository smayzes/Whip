<?php
/**
 * TemplateModifierDump class.
 *
 * Json-encode an object / array / etc
 * 
 */
class TemplateModifierDump extends TemplateModifier {

	public static function run($value) {
	   $output =
	       '<pre>'.
	       print_r($value, true).
	       '</pre>';
	   return $output;
	}  //  function run
    
    
}   //  Dump
