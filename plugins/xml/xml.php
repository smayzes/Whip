<?php

/**
 * XML class.
 *
 * Whip : A non-restrictive PHP framework
 * Copyright (c) 2010 Menno van Ens (codefocus.ca) and Shawn Mayzes (mayzes.org)
 * Released under the GNU General Public License, Version 3
 * 
 * @extends WhipPlugin
 */
//require_once(Whip::real_path(__DIR__).'models.php');
define('E_URL_UNREACHABLE',         'Failed to connect to the address.');
define('E_URL_EMPTY',               'Failed to find any usable addresses.');
define('E_URL_NOT_XML',             'Failed to load well-formed XML with this address.');
define('E_XML_NOT_OBJECT',          'Failed to load XML data as an Object.');
define('E_NAMESPACE_EMPTY',         'Failed to load a namespace');


class Xml extends UncachedWhipPlugin {
//class Xml extends WhipPlugin {
    public $urls;

//  Cross-instance
    protected static $_require = array('Db', 'Query', 'Http');           //  array: names of plugins required to run this plugin

    public function load($urls = array()) {    	
    // Check if the Urls paramater is an array
        if ( !is_array($urls) ) {
            $urls = array($urls);
        }
    // Loop through array of Urls
        foreach ( $urls as $url ) {
            $headers = Whip::Http()->online($url);
            $code = substr($headers[0], 9, 3);
        // Check if Url loads
            if ( $code != 200 ) {
            // Url is not reachable
                throw new WhipConfigException(E_URL_UNREACHABLE . ' ' . $url . ' HTTP Code: ' . $code . ' ' . $headers[0]);
                return false;
            }
            $this->urls[] = $url;
        }
        return $this;
    }

    public function save() {
        $xml = self::_get_xml();
        
        foreach ( $xml as $key => $value ){
            if ( isset($value['channe']) ) {
                // parse RSS 2.0
            }
            else if ( isset($value['entry']) ) {
                // parse Atom
            }
            echo "<pre>";
            print_r($value);
            die;
        }
        /*id
        hash
        url
        title
        link            
        description
        language
        copyright
        managingeditor
        webmaster
        pubdate
        lastbuilddate
        category 
        generator
        docs     
        cloud    
        ttl      
        image    
        rating   
        textinput*/
    }
    
    public function display() {
        $xml = self::_get_xml();
        
        return $xml;
    }
    
    public function json() {
        $xml = self::_get_xml();
        return json_encode($xml);
    }
    
    private function _get_xml() {
        // If we have no URLs then we shouldn't run this method
        if ( !isset($this->urls) ) {
            throw new WhipModelException(E_URL_EMPTY);
            return false;
        }

    // Loop through each URL and get the rss data
        foreach ( $this->urls as $url ) {
        // Get the XML Data
            $xml = self::_load_xml($url);
        // Check if the data is returned as formed XML
            if ( !isset($xml) ) {
                foreach ( libxml_get_errors() as $error) {
                    $err .= "\t" . $error->message;
                }
                throw new WhipConfigException(E_URL_NOT_XML . ' ' . $url . '[' . $err . ']');
                return false;
            }
            $results[] = self::_parse_xml($xml);
        }
        return $results;
    }
    
    private function _load_xml(&$url) {
        $xml = simplexml_load_file($url, 'SimpleXMLElement', LIBXML_NOCDATA);
        if ( $xml !== false ) {
            return $xml;
        }
        return false;
    }

    private function _get_namespaces(&$xml) {
        $namespaces = $xml->getNamespaces(TRUE);
        if ( is_array($namespaces) ) {
            return $namespaces;
        }
        return false;
    }

    private function _parse_xml(&$xml, $results = array()) {
        if ( !is_object($xml) ) {
            throw new WhipConfigException(E_XML_NOT_OBJECT);
            return false;
        }
        $namespaces = array_merge(array('' => ''), self::_get_namespaces($xml));
        $count      = 0;

        foreach ( $namespaces as $ns => $ns_url) {
            $iteration = 0;
            foreach ($xml->children($ns_url) as $value) {
                $element_name = $value->getName();

                if ($value->children($ns_url)) {
                    $results[$element_name][$count] = array();
                    $results[$element_name][$count] = self::_parse_xml($value, $results[$element_name][$count]);
                }
                else if ( $value->attributes() ) {
                    if (!isset($results[$element_name])) {
                        $results[$element_name] = array();
                        $element = &$results[$element_name];
                    }                    
                    elseif (isset($results[$element_name]['attributes'])) {
                    //  Already exists
                        $results[$element_name] = array($results[$element_name]);
                        $element = &$results[$element_name][];
                    }
                    
                    foreach ( $value->attributes() as $attribute => $attribute_value ) {
                        $element['attributes'][$attribute] = (string) $attribute_value;
                        if ( count($value[0]) > 0 ) {
                             $results['value'][$iteration] = (string) $value;
                             $iteration++;
                        }
                    }
                    $element['value'] = (string)  $value;

                }
                else {
                    if (isset($results[$element_name]['value'])) {
                    //  Value with this name already exists.
                    //  Create an array
                        $results[$element_name]['value'] = array($results[$element_name]['value']);
                        $results[$element_name]['value'][] = (string) $value;
                    }
                    else {
                        $results[$element_name]['value'] = (string) $value;
                    }
                }
                $count++;
            }
        }
        return $results;
    }
} // Class Xml
