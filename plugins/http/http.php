<?php

/**
 * Http plugin.
 *
 *
 * Whip : A non-restrictive PHP framework
 * Copyright (c) 2010 Menno van Ens (codefocus.ca) and Shawn Mayzes (mayzes.org)
 * Released under the GNU General Public License, Version 3 
 *
 * @extends WhipPlugin
 */
class Http extends SingletonWhipPlugin {
    
    const HTTP_MOVED_PERMANENTLY = 301;
    const HTTP_FOUND = 302;
    const HTTP_SEE_OTHER = 303;
    
    const HTTP_TEMPORARY_REDIRECT = 307;
    
    
    public function redirect($url, $code = self::HTTP_SEE_OTHER, $exit = true) {
        
        
        header('Location: '.$url, true, $code);
        if ($exit) {
            exit();
        }
    }
    
    
}   //  class Http

