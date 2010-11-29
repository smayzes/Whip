<?php


/**
 * TemplateModifier class.
 * 
 */
class TemplateModifierSnip extends TemplateModifier {
    
    public static function run($value) {
        return strip_tags($value);
    }
    
    
}   //  Snip
