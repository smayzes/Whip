<?php
/**
 * TemplateModifierMoney class.
 *
 * Json-encode an object / array / etc
 * 
 */
class TemplateModifierMoney extends TemplateModifier {

	public static function run($value, $decimals = 2, $dollar_sign = '$', $dollar_position = 'left', $dec_point = '.', $thousands_sep = '') {
	   if (is_numeric($value)) {
	       $number =  number_format($value, $decimals, $dec_point, $thousands_sep);
           if ( $dollar_position == 'left' ) {
                $number = $dollar_sign . $number;
           }
           else if ( $dollar_position == 'right' ) {
                $number = $number . $dollar_sign;
           }
           return $number;
	   }
	   return false;
	}  //  function run
    
    
}   // Money 
