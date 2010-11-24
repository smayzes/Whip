<?php


/**
 * TemplateModifier class.
 * 
 */
class TemplateModifierUppercase extends TemplateModifier {
    
    public static function run($value) {
        return strtoupper($value);
    }
    
    
}   //  Uppercase
