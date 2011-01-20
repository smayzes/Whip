<?php


/**
 * TemplateModifier class.
 * 
 */
class TemplateModifierLowercase extends TemplateModifier {
    
    public static function run($value) {
        return strtolower($value);
    }
    
    
}   //  Lowercase
