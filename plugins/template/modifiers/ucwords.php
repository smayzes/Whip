<?php


/**
 * TemplateModifier class.
 * 
 */
class TemplateModifierUcwords extends TemplateModifier {
    
    public static function run($value) {
        return ucwords($value);
    }
    
    
}   //  Ucwords
