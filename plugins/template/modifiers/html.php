<?php


/**
 * TemplateModifier class.
 * 
 */
class TemplateModifierHtml extends TemplateModifier {
    
    public static function run($value, $method = null) {
		switch ( $method ) {
			case 'clean' :
				return Whip::html()->clean($value);
			break;
		}
        return $value;
    }
    
    
}   //  Html
