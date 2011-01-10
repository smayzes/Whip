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

define('E_MISSING_VALUE', 'You are missing the following parameter: ');

class Http extends SingletonWhipPlugin {
    
    private $_method    = 'GET'; // Default
    private $_header    = null;
    private $_content   = null;
    private $_lang      = 'en';
    private $_username  = null;
    private $_password  = null;
    private $_file      = null;
    private $_url       = null;
    private $_agent     = "Mozilla/4.0 (compatible; MSIE 5.01; Windows NT 5.0)";
    public  $urls		= array();
    
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
    
    /**
     * load function.
     * 
     * Loads Rss URLs into class for processing.
     *
     * @access public
     * @param array $urls. (default: array())
     * @return void
     */
    public function load($urls = array()) {
    	// Check if passed paramater is an array
    	if ( !is_array($urls) ) {
    		$urls = array($urls);
    	} 
    	
    	// In case URLs have been passed, add the new ones to the end of array
    	foreach ( $urls as $url ) {
			array_push($this->urls, $url);
		}
		
		return $this;
    } // load

    public function online($url) {
    	if ( $url ) {
	    	$headers = self::_get_headers($url);
	    }
	    else {
	    	throw new WhipConfigException(E_MISSING_VALUE.'URL');
            return false;
        }
	    
	    return $headers;
    }

    public function get($file) {
        $this->_file    = $file;
        $this->_method  = 'GET';

        return $this->_get_contents();
    }

    public function post($file) {
        $this->_file    = $file;
        $this->_method  = 'POST';

        return $this->_get_contents();
    }
    
    public function language($language) {
        $this->_lang = $language;

        return $this;
    }

    public function header() {
        $this->_header = "Accept-language: ". $this->_lang ."\r\n";
        if ( isset($this->username) && isset($this->password) )
            $this->_header .= "Authorization: Basic ". base64_encode($this->_username, $this->_password) ."\r\n";
        if ( isset($data) ) {
            $this->_header .= "Content-type: application/x-www-form-urlencoded\r\n" .
                                "Content-Length: " . strlen($data) . "\r\n";
        }

        return $this;
    }

    private function _content($data) {
        $this->_content = $data;

        return $this;
    }

    public function authorization($username, $password) {
        $this->_username = $username;
        $this->_password = $password;

        return $this;
    }

    private function _get_contents() {
        if ( !isset($this->_header) ) 
            self::header();

        if ( ini_get('allow_url_fopen') == '1' )
            return $this->_file_get_contents();
        else 
            return $this->_curl();
    }

    private function _file_get_contents() {
        $header = ( isset($this->_header) ) ? $this->_header : self::_get_header();
        $context = stream_context_create(array(
            'http' => array(
                'method' => $this->_method,
                'header' => $header,
            )
        ));

        return file_get_contents($this->_file, false, $context);
    }
    
    private function _get_headers($url) {
    // Check if we are passed a URL paramater
    	if ( !isset($url) ) {
	    	throw new WhipConfigException(E_MISSING_VALUE.'URLS');
            return false;
        }
    // Return the URLs header information
   		return get_headers($url, 1);
    }

    private function _curl() {
       $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL,$this->_url );
        curl_setopt($ch, CURLOPT_USERAGENT, $this->_agent);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_VERBOSE,false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSLVERSION,3);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        
        $page = curl_exec($ch);
        //echo curl_error($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        return array(
                'page'      => $page,
                'http_code' => $http_code,
                );
    }

}   //  class Http

