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
// TODO: Uncomment this
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

    public function save($url = '') {
        $xml = self::_get_xml();

        foreach ( $xml as $key => $value ){
            if ( isset($value['channel']) ) {
                // parse RSS 2.0
                foreach ( $value['channel'] as $channel ) {
                	// Save Channel Data
                	$channel_hash = ( !empty($channel['link']['value']) ) ? md5($channel['link']['value']) : md5($channel['title']['value']);
                	try {
	                	$rss_channel = Whip::Db()->get_one(
	            						'RSS_Channel',
	            						Whip::Query()->where('hash', $channel_hash)
	            						//Whip::Query()->where('title', $channel['title']['value'])
	            		);
	            	} catch ( PDOException $e ) {
	            		print $e;
	            		print "\n------------------------------------\n\n\n";
	            		continue;
	            	}
            		
            		if ( !isset($rss_channel->id) ) {
            			$rss_channel = new RSS_Channel();
            			//$rss_channel->id = 0;
            		}
            		
            		$rss_channel->hash 				= $channel_hash;
            		$rss_channel->url 				= $url; // TODO Find a better way to get the current URL here
            		$rss_channel->title 			= ( isset( $channel['title']['value']) ) ? $channel['title']['value'] : null;
                	$rss_channel->link 				= ( isset( $channel['link']['value']) ) ? $channel['link']['value'] : null;
                	$rss_channel->description 		= ( isset( $channel['description']['value']) ) ? $channel['description']['value'] : null;
                	$rss_channel->language 			= ( isset( $channel['language']['value']) ) ? $channel['language']['value'] : null;
                	$rss_channel->copyright 		= ( isset( $channel['copyright']['value']) ) ? $channel['copyright']['value'] : null;
                	$rss_channel->managingeditor 	= ( isset( $channel['managingEditor']['value']) ) ? $channel['managingEditor']['value'] : null;
                	$rss_channel->webmaster 		= ( isset( $channel['webmaster']['value']) ) ? $channel['webmaster']['value'] : null;
                	$rss_channel->pubdate 			= ( isset( $channel['pubDate']['value']) ) ? $channel['pubDate']['value'] : null;
                	$rss_channel->lastbuilddate 	= ( isset( $channel['lastBuildDate']['value']) ) ? $channel['lastBuildDate']['value'] : null;
                	$rss_channel->category 			= ( isset( $channel['category']['value']) ) ? $channel['category']['value'] : null;
                	$rss_channel->generator 		= ( isset( $channel['generator']['value']) ) ? $channel['generator']['value'] : null;
                	$rss_channel->docs 				= ( isset( $channel['docs']['value']) ) ? $channel['docs']['value'] : null;
                	$rss_channel->ttl 				= ( isset( $channel['ttl']['value']) ) ? $channel['ttl']['value'] : null;
                	$rss_channel->image 			= ( isset( $channel['image']['value']) ) ? $channel['image']['value'] : null;
                	$rss_channel->rating 			= ( isset( $channel['rating']['value']) ) ? $channel['rating']['value'] : null;
                	$rss_channel->textinput 		= ( isset( $channel['textInput']['value']) ) ? $channel['textInput']['value'] : null;

                	$rss_channel->save();

				// TODO: Check if the above returns the ID, if so delete the query below
					$rss_channel = Whip::Db()->get_one(
            						'RSS_Channel',
            						Whip::Query()->where('hash', $channel_hash)
            		);
                	
                	foreach ( $channel['item'] as $item ) {
                		// Save Item Data
                		$item_hash = ( !empty($item['guid']['value']) ) ? md5($item['guid']['value']) : md5($item['link']['value']);
                		
                		$rss_item = Whip::Db()->get_one(
                						'RSS_Item',
                						Whip::Query()->where('hash', $item_hash)
                		);
                		
                		if ( !isset($rss_item->id) ) {
                			$rss_item = new RSS_Item();
                			//$rss_item->id = 0;
                		}
                		$rss_item->hash 		= $item_hash;
                		$rss_item->channel_id   = $rss_channel->id;
                		$rss_item->title 		= ( isset( $item['title']['value']) ) ? $item['title']['value'] : null;
                		$rss_item->link 		= ( isset( $item['link']['value']) ) ? $item['link']['value'] : null;
                		$rss_item->description 	= ( isset( $item['description']['value']) ) ? $item['description']['value'] : null;
                		$rss_item->author		= ( isset( $item['author']['value']) ) ? $item['author']['value'] : null;
                		$rss_item->category 	= null;
                		$rss_item->comments		= null;
                		$rss_item->enclosure 	= null;
                		$rss_item->guid 		= ( isset( $item['guid']['value']) ) ? $item['guid']['value'] : null;
                		$rss_item->pubdate 		= ( isset( $item['pubDate']['value']) ) ? $item['pubDate']['value'] : null;
                		$rss_item->source 		= ( isset( $item['source']['value']) ) ? $item['source']['value'] : null;

                		$rss_item->save();
                	}
                }
            }
            else if ( isset($value['entry']) ) {
                // parse Atom
                // Save Channel Data
            	$atom_hash = ( !empty($value['link'][0]['attributes']['href']) ) ? md5($value['link'][0]['attributes']['href']) : md5($value['title']['value'][0]);
            	try {
                	$rss_channel = Whip::Db()->get_one(
            						'RSS_Channel',
            						Whip::Query()->where('hash', $atom_hash)
            						//Whip::Query()->where('title', $channel['title']['value'])
            		);
            	} catch ( PDOException $e ) {
            		print $e;
            		print "\n------------------------------------\n\n\n";
            		continue;
            	}
        		
        		if ( !isset($rss_channel->id) ) {
        			$rss_channel = new RSS_Channel();
        			//$rss_channel->id = 0;
        		}
                
                $rss_channel->hash 				= $atom_hash;
        		$rss_channel->url 				= $url; // TODO Find a better way to get the current URL here
        		$rss_channel->title 			= ( isset( $value['title']['value'][0]) ) ? $value['title']['value'][0] : null;
            	$rss_channel->link 				= ( isset( $value['link'][0]['attributes']['href']) ) ? $value['link'][0]['attributes']['href'] : null;
            	$rss_channel->description 		= ( isset( $value['subtitle']['value'][0]) ) ? $value['subtitle']['value'][0] : null;
            	//$rss_channel->language 			= ( isset( $channel['language']['value']) ) ? $channel['language']['value'][0] : null;
            	
            	//$rss_channel->copyright 		= ( isset( $channel['copyright']['value']) ) ? $channel['copyright']['value'] : null;
            	//$rss_channel->managingeditor 	= ( isset( $channel['managingEditor']['value']) ) ? $channel['managingEditor']['value'] : null;
            	//$rss_channel->webmaster 		= ( isset( $channel['webmaster']['value']) ) ? $channel['webmaster']['value'] : null;
            	$rss_channel->pubdate 			= ( isset( $value['updated']['value'][0]) ) ? $value['updated']['value'][0] : null;
            	$rss_channel->lastbuilddate 	= ( isset( $value['updated']['value'][0]) ) ? $value['updated']['value'][0] : null;
            	//$rss_channel->category 			= ( isset( $channel['category']['value']) ) ? $channel['category']['value'] : null;
            	//$rss_channel->generator 		= ( isset( $channel['generator']['value']) ) ? $channel['generator']['value'] : null;
            	//$rss_channel->docs 				= ( isset( $channel['docs']['value']) ) ? $channel['docs']['value'] : null;
            	//$rss_channel->ttl 				= ( isset( $channel['ttl']['value']) ) ? $channel['ttl']['value'] : null;
            	//$rss_channel->image 			= ( isset( $channel['image']['value']) ) ? $channel['image']['value'] : null;
            	//$rss_channel->rating 			= ( isset( $channel['rating']['value']) ) ? $channel['rating']['value'] : null;
            	//$rss_channel->textinput 		= ( isset( $channel['textInput']['value']) ) ? $channel['textInput']['value'] : null;

                $rss_channel->save();
                
                $rss_channel = Whip::Db()->get_one(
        						'RSS_Channel',
        						Whip::Query()->where('hash', $atom_hash)
        		);
                
                foreach ( $value['entry'] as $entry ) {               	
            	// Save Item Data
            		$entry_hash = ( !empty($entry['id']['value']) ) ? md5($entry['id']['value']) : md5($entry['link']['attributes']['href']);
            		
            		$rss_item = Whip::Db()->get_one(
            						'RSS_Item',
            						Whip::Query()->where('hash', $entry_hash)
            		);
            		
            		if ( !isset($rss_item->id) ) {
            			$rss_item = new RSS_Item();
            			//$rss_item->id = 0;
            		}
            		$rss_item->hash 		= $entry_hash;
            		$rss_item->channel_id   = $rss_channel->id;
            		
            		
            		$rss_item->title 		= ( isset( $entry['title']['value']) ) ? $entry['title']['value'] : null;
            		
            		if ( isset( $entry['link']['attributes']['href']) ) {
            			$rss_item->link = $entry['link']['attributes']['href'];
            		} else if ( isset($entry['origLink']['value']) ) {
            			$rss_item->link = $entry['origLink']['value'];
            		}
            		$rss_item->description 	= ( isset( $entry['content']['value']) ) ? $entry['content']['value'] : null;
            		$rss_item->guid 		= ( isset( $entry['id']['value']) ) ? $entry['id']['value'] : null;
            		$rss_item->pubdate 		= ( isset( $entry['published']['value']) ) ? $entry['published']['value'] : null;

            		$rss_item->save();
                }
               
            }
        }
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
            $results[] = self::_parse_xml($xml, array(), $url);
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

    private function _parse_xml(&$xml, $results = array(), $url = '') {
        if ( !is_object($xml) ) {
            throw new WhipConfigException(E_XML_NOT_OBJECT . ': '.$url);
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
