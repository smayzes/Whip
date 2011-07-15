<?php
/**
 * TemplateModifierSnip class.
 *
 * Shortens a string and appends "..." if necessary
 * 
 */
class TemplateModifierSnip extends TemplateModifier {

	public static function run($string, $length=32, $use_hellip=true) {
	   if (is_numeric($length)) {
	       if (strlen($string)>$length+2) {
	           $string     = substr($string, 0, $length);
	           if ($use_hellip) {
	               $string .= '&hellip;';
	           }
	           else {
	               $string .= '...';
	           }
	       }
	   }
	   return $string;
	}  //  function run
	
	/**
	 * sentence function.
	 * 
	 * @access public
	 * @static
	 * @param mixed $string
	 * @param int $sentences. (default: 1)
	 * @param int $length. (default: 255)
	 * @param bool $use_hellip. (default: false)
	 * @return void
	 */
	public static function sentence($string, $sentences=1, $length=255, $use_hellip=false) {
    //  Split into sentences
        $splits = preg_split('/[\.\!\?]+\s+\b/i', $string);
        if (count($splits) <= $sentences) {
        //  No need to snip sentences
        //  (unless the sentence is too long)
            return self::run($string, $length, $use_hellip);
        }
    //  Remove empties
        $sentence_strings = array();
        foreach($splits as $sentence_string) {
            $sentence_string = trim($sentence_string);
            if (!empty($sentence_string)) {
                $sentence_strings[] = $sentence_string;
            }
        }
        $num_sentences = count($sentence_strings);
        if ($num_sentences <= $sentences) {
        //  No need to snip sentences
        //  (unless the sentence is too long)
            return self::run($string, $length, $use_hellip);
        }
    //  Return only the first few sentences
        $sentence_strings = array_slice($sentence_strings, 0, $sentences);
        $string = implode('. ', $sentence_strings).'.';
    //  Max length
        return self::run($string, $length, $use_hellip);
	}  //  function sentence
	
	/**
	 * sentences function.
	 * Alias for sentence
	 */
	public static function sentences($string, $sentences=1, $length=255, $use_hellip=false) {
	   return self::sentence($string, $length, $use_hellip);
	}  //  function sentences
    
    
}   //  Snip
