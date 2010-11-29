<?php
/**
 * TemplateModifierPrettyurl class.
 *
 * Replaces newlines with <br />
 * 
 */
class TemplateModifierPrettyurl extends TemplateModifier {
    const REGEX_URL_PROTOCOL = 'http[s]?:\/\/';
    const REGEX_URL_HOST = '(([a-z0-9\-]+\.)+([a-z0-9]{2,5}))';

	public static function run($url) {
    //  Grab the domain portion of the url
        $regex = '/^'.self::REGEX_URL_PROTOCOL.self::REGEX_URL_HOST.'(\/.*)?$/i';
        $matches = array();
        if (preg_match($regex, $url, $matches)) {
            return $matches[1];
        }
        return $url;
	}  //  function run
    
    
}   //  Prettyurl
