<?php

/**
 * RSS class.
 *
 * Whip : A non-restrictive PHP framework
 * Copyright (c) 2010 Menno van Ens (codefocus.ca) and Shawn Mayzes (mayzes.org)
 * Released under the GNU General Public License, Version 3
 * 
 * @extends WhipPlugin
 */
require_once(Whip::real_path(__DIR__).'models.php');

define('E_URLS_MISSING',                'No RSS URLs Set.');
define('E_URLS_NOT_VALID', 				'This RSS URL is not valid.');

class Rss extends WhipPlugin {

//  Cross-instance
    protected static $_require = array('Db', 'Query');           //  array: names of plugins required to run this plugin
    

    public $urls = array();
    public $file_contents = null;
    public $document = null;
    public $rss = null;
    public $title = null;
    
    private $_attributes;
    
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

    public function display($limit = null) {		
	// If we have no URLs then we shouldn't run this method
    	if ( !isset($this->urls) ) {
    		throw new WhipModelException(E_URLS_MISSING);
    		return false;
    	}
    // Loop through each URL and get the rss data
    	foreach ( $this->urls as $url ) {
    		$hash = md5($url);
    		
   		    $RSS_Channel =  Whip::Db()->get_one(
								'RSS_Channel',
								Whip::Query()->where('url', $url)
						    );
		    if ( !isset($RSS_Channel->id) ) {
    			throw new WhipModelException(E_URLS_NOT_VALID);
    			return false;
    		}
    		
			$query = Whip::Query()->where('channel_id', $RSS_Channel->id);
			if ($limit)
    			$query->limit($limit);

			$results =  Whip::Db()->get_all(
                              'RSS_Item',
                              $query
                          );
		}
    	
    	// TODO Change the above results to handle multiple URLs.
    	// Only take 1 right now.
    	return $results;
    } // display
    
    public function save() {
    // If we have no URLs then we shouldn't run this method
    	if ( !isset($this->urls) ) {
    		throw new WhipModelException(E_URLS_MISSING);
    		return false;
    	}
    	
    // get the xml contents of each URL we are passed
    	foreach ( $this->urls as $url ) {
    		$this->file_contents = $this->_parse_xml(file_get_contents($url), $url);
    	}
    } // save
    
    private function _parse_xml($rss, $url = null) {
    // Load the xml with PHP DOMDocument
    	$document = new DOMDocument;
    	$document->loadXML($rss);
    	
    // Get and Save the rss channel data
       	$RSS_Channel_ID = $this->_save_channel($this->_get_rss_sections($document->getElementsByTagName('channel')), $url);
    // Get and Save the rss item data
       	$this->_save_item($this->_get_rss_sections($document->getElementsByTagName('item')), $RSS_Channel_ID);
    } // _parse_xml
    
    private function _save_channel($channel, $url = null) {
    // We only ever want the first channel
    	$channel = $channel[0];
    	
        // Check if we are passed a URL before we do anything
    	if ( isset($channel['link']) ) {
    	// Create a hash of the URL for unique identification
    		$hash = md5($channel['link']);
    	
    	// Check if the hash returns a rss channel
		    $RSS_Channel 				=  Whip::Db()->get_one(
												'RSS_Channel',
												Whip::Query()->where('hash', $hash)
										   );
						   							   
		// Check if this item is not in our database
		// If it is not, then we load a new/clean instance of RSS_Channel	 		    
		    if ( !isset($RSS_Channel->id) ) {
		    	$RSS_Channel 			= new RSS_Channel();
		    	$RSS_Channel->id	 	= 0;  // TODO: Remove when Menno fixes bug
		    }		   
			
		// Set the RSS_Channel values to insert/update
			$RSS_Channel->url	 		= ( $url ) ? $url : $channel['link'];
			$RSS_Channel->link	 		= $channel['link'];
			$RSS_Channel->hash	 		= $hash;
			$RSS_Channel->language	 	= ( isset($channel['language']) ) ? $channel['language'] : null;
			$RSS_Channel->copyright	 	= ( isset($channel['copyright']) ) ? $channel['copyright'] : null;
			$RSS_Channel->managingeditor= ( isset($channel['managingEditor']) ) ? $channel['managingEditor'] : null;
			$RSS_Channel->webmaster	 	= ( isset($channel['webmaster']) ) ? $channel['webmaster'] : null;
			$RSS_Channel->pubdate	 	= ( isset($channel['pubDate']) ) ? $channel['pubDate'] : null;
			$RSS_Channel->lastbuilddate	= ( isset($channel['lastBuildDate']) ) ? $channel['lastBuildDate'] : null;
			$RSS_Channel->category	 	= ( isset($channel['category']) ) ? $channel['category'] : null;
			$RSS_Channel->docs	 		= ( isset($channel['docs']) ) ? $channel['docs'] : null;
			$RSS_Channel->ttl	 		= ( isset($channel['ttl']) ) ? $channel['ttl'] : null;
			$RSS_Channel->title 		= ( isset($channel['title']) ) ? $channel['title'] : null;
			$RSS_Channel->description 	= ( isset($channel['description']) ) ? $channel['description'] : null;
			$RSS_Channel->generator 	= ( isset($channel['generator']) ) ? $channel['generator'] : null;
			
			$RSS_Channel->save();
			
		// TODO: Remove when we return PK ID in save method
			$RSS_Channel 				=  Whip::Db()->get_one(
												'RSS_Channel',
												Whip::Query()->where('hash', $hash)
										   );

			
		// Return this channel information for the saving of the rss items
			return $RSS_Channel->id;
    	}
    	return false;
    } // function _save_channel
    
    private function _save_item($items, $rss_channel_id) {
    	// Loop throuch each item from the rss feed
    	foreach ( $items as $item ) {
    		$RSS_Item 				= new RSS_Item();


    	//   Use guid or link for unique key
    		$hash = ( !empty($item['guid']) ) ? md5($item['guid']) : md5($item['link']);
    		
    	// Check if this feed item exists in our database already
    		$RSS_Item 				=  Whip::Db()->get_one(
											'RSS_Item',
											Whip::Query()->where('hash', $hash)
									   );
            
		// Check if this item is not in our database
		// If it is not, then we load a new/clean instance of RSS_Item						   		    
			if ( !isset($RSS_Item->id) ) {
				$RSS_Item 			= new RSS_Item;
				$RSS_Item->id	 		= 0; // TODO: Remove when Menno fixes bug
			}


		// Set the RSS_Item values to insert/update
    		$RSS_Item->channel_id 	= $rss_channel_id;
    		$RSS_Item->hash		 	= $hash;
			$RSS_Item->title	 	= ( isset($item['title']) ) ? $item['title'] : null;
			$RSS_Item->link	 		= ( isset($item['link']) ) ? $item['link'] : null;
			$RSS_Item->description	= ( isset($item['description']) ) ? $item['description'] : null;
			$RSS_Item->author	 	= ( isset($item['author']) ) ? $item['author'] : null;
			$RSS_Item->category	 	= ( isset($item['category']) ) ? $item['category'] : null;
			$RSS_Item->comments	 	= ( isset($item['comments']) ) ? $item['comments'] : null;
			$RSS_Item->enclosure	= ( isset($item['enclosure']) ) ? $item['enclosure'] : null;
			$RSS_Item->guid	 		= ( isset($item['guid']) ) ? $item['guid'] : null;
			$RSS_Item->pubdate	 	= ( isset($item['pubdate']) ) ? $item['pubdate'] : date('Y-m-d H:i:s');
			$RSS_Item->source	 	= ( isset($item['source']) ) ? $item['source'] : null;
			
			$RSS_Item->save();
    	}
    } // _save_item
    
    
    private function _get_rss_sections($rss) {
    // Loop through each rss node and parse values into an array
    	foreach ( $rss as $r ) {	       
	        if ( $r->childNodes->length ) {
	            foreach ( $r->childNodes as $i ) {
	                $result[$i->nodeName] = $i->nodeValue;
	            }
	        }
	        $results[] = $result;   
		}
		return $results;
    } // _get_rss_sections
    
}   //  class Rss
