<?php


/**
 * TemplateModifier class.
 * 
 */
class TemplateModifierStriptags extends TemplateModifier {
    
    public static function run($value) {
        return strip_tags($value);
    }
    
    
}   //  Date
