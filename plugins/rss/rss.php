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
define('E_SITE_UNREACHABLE', 			'This URL is unreachable. ');
define('E_URL_NOT_STRING', 				'This URL has not resolved as a string. ');
define('E_ITEMS_NOT_SET', 				'Item values are not set in this array');
define('E_CHANNEL_NOT_SET',				'Channel values are not set in this array');
define('E_XML_VALUES_NOT_SET', 			'XML values are not set');
define('E_XML_ATTRIBUTE_NOT_SET', 		'attribute value is not set');

class Rss extends WhipPlugin {

//  Cross-instance
    protected static $_require = array('Db', 'Query', 'Http');           //  array: names of plugins required to run this plugin
    
    public $urls 			= array();
    public $url 			= null;
    public $file_contents 	= null;
    public $document 		= null;
    public $rss 			= null;
    public $title 			= null;
    private $_encoding 		= 'UTF-8';
    private $_rss_channel_id= 0;
    private $_attributes 	= null;
    
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
    //  Check if passed paramater is an array
    	if ( !is_array($urls) ) {
    		$urls = array($urls);
    	} 
    	
    //  In case URLs have been passed, add the new ones to the end of array
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
   		//  Get the channel information based off the url hash
            $RSS_Channel =  Whip::Db()->get_one(
								'RSS_Channel',
								Whip::Query()->where('hash', $hash)
						    );
		//  If this channel we are trying to display does not exist
		    if ( !isset($RSS_Channel->id) ) {
    			throw new WhipModelException(E_URLS_NOT_VALID);
    			return false;
    		}
    	//  build Whip Query for RSS_Item with a limit
			$query = Whip::Query()->where('channel_id', $RSS_Channel->id);
			if ($limit)
    			$query->limit($limit);

			$results =  Whip::Db()->get_all(
                              'RSS_Item',
                              $query
                          );
		}
    	
    // TODO Change the above results to handle multiple URLs.
  	// Only takes 1 right now.
    	return $results;
    } // display
    
    public function save() {
   	//  Loop through URLs and check if the URL is valid
   		foreach ( $this->urls as $url ) {
   		//  Get the header data
   			$headers = Whip::Http()->online($url);
   		//  Check if we return a 200 response
   			if ( substr($headers[0], 9, 3) != 200 ) {
   			// Site is not reachable
   				throw new WhipConfigException(E_SITE_UNREACHABLE . ' ' . $url);
            	return false;
   			}
   		//  Get XML data from URL
   			$xml = file_get_contents($url);
   		//  Set the current URL we are on
   			$this->url = $url;
   		//  Parse the XML data into an array. 
   			$results = $this->_parse_xml($xml);
   		}
    } // save
    
    private function _parse_xml($xml) {
    //  Check if xml data is a string
    	if ( !is_string($xml) ) {
			throw new WhipConfigException(E_URL_NOT_STRING . ' ' . $url);
        	return false;
		}
	//  Load XML into DOMDocument()
    	$xml_doc = new DOMDocument();
		$xml_doc->loadXML($xml);
		
		foreach ( $xml_doc->getElementsByTagName('channel') as $channel ) {
		// Get the Link values
			$link = $this->_get_dom_value($channel->getElementsByTagName('link'));
			if ( empty($link) ) {
				$link = $this->_get_dom_attribute($channel->getElementsByTagName('link'), 'href');
			}

		// Channel array
			$channel = array(
				'url'			=> $this->url,
				'title'			=> $this->_get_dom_value($channel->getElementsByTagName('title')),
				'link'			=> $link,
				'description'	=> $this->_get_dom_value($channel->getElementsByTagName('description')),
				'language'		=> $this->_get_dom_value($channel->getElementsByTagName('language')),
				'copyright'		=> $this->_get_dom_value($channel->getElementsByTagName('copyright')),
				'managingeditor'=> $this->_get_dom_value($channel->getElementsByTagName('managingEditor')),
				'webmaster'		=> $this->_get_dom_value($channel->getElementsByTagName('webmaster')),
				'pubdate'		=> $this->_get_dom_value($channel->getElementsByTagName('pubDate')),
				'lastbuilddate'	=> $this->_get_dom_value($channel->getElementsByTagName('lastBuildDate')),
				'category'		=> $this->_get_dom_value($channel->getElementsByTagName('category')),
				'generator'		=> $this->_get_dom_value($channel->getElementsByTagName('generator')),
				'docs'			=> $this->_get_dom_value($channel->getElementsByTagName('docs')),
				'cloud'			=> $this->_get_dom_value($channel->getElementsByTagName('cloud')),
				'ttl'			=> $this->_get_dom_value($channel->getElementsByTagName('ttl')),
				'image'			=> $this->_get_dom_value($channel->getElementsByTagName('image')),
				'rating'		=> $this->_get_dom_value($channel->getElementsByTagName('rating')),
				'textinput'		=> $this->_get_dom_value($channel->getElementsByTagName('textInput')),
			);
		}
		
	//  Save the Channel information
		$this->_rss_channel_id = $this->_save_channel($channel);
		
		foreach ( $xml_doc->getElementsByTagName('item') as $item ) {
			$items[] = array(
				'title' 	 => ( isset($item->getElementsByTagName('title')->item(0)->nodeValue) ) ? $item->getElementsByTagName('title')->item(0)->nodeValue : null,
				'link' 		 => ( isset($item->getElementsByTagName('link')->item(0)->nodeValue) ) ? $item->getElementsByTagName('link')->item(0)->nodeValue : null,
				'description'=> ( isset($item->getElementsByTagName('description')->item(0)->nodeValue) ) ? $item->getElementsByTagName('description')->item(0)->nodeValue : null,
				'author'	 => ( isset($item->getElementsByTagName('author')->item(0)->nodeValue) ) ? $item->getElementsByTagName('author')->item(0)->nodeValue : null,
				'category'	 => ( isset($item->getElementsByTagName('category')->item(0)->nodeValue) ) ? $item->getElementsByTagName('category')->item(0)->nodeValue : null,
				'comments'	 => ( isset($item->getElementsByTagName('comments')->item(0)->nodeValue) ) ? $item->getElementsByTagName('comments')->item(0)->nodeValue : null,
				'enclosure'	 => ( isset($item->getElementsByTagName('enclosure')->item(0)->nodeValue) ) ? $item->getElementsByTagName('enclosure')->item(0)->nodeValue : null,
				'guid'		 => ( isset($item->getElementsByTagName('guid')->item(0)->nodeValue) ) ? $item->getElementsByTagName('guid')->item(0)->nodeValue : null,
				'pubdate'	 => ( isset($item->getElementsByTagName('pubDate')->item(0)->nodeValue) ) ? $item->getElementsByTagName('pubDate')->item(0)->nodeValue : null,
				'source'	 => ( isset($item->getElementsByTagName('source')->item(0)->nodeValue) ) ? $item->getElementsByTagName('source')->item(0)->nodeValue : null,
			);
			
		}
		
		$this->_save_items($items);
    } // function _parse_xml
    
    private function _save_items($items) {
    //  Check if xml data is set
    	if ( !isset($items) && is_array($items) ) {
			throw new WhipConfigException(E_ITEMS_NOT_SET);
        	return false;
		}
		
	//  Loop through each item in array
		foreach ( $items as $item ) {
		//  Use guid or link for unique item key
    		$hash = ( !empty($item['guid']) ) ? md5($item['guid']) : md5($item['link']);
    	//  Run DB Query to see if this item exists
			$RSS_Item = Whip::Db()->get_one(
						'RSS_Item',
						Whip::Query()->where('hash', $hash)
			);
		//  Check if this item is not in our database
		//  If it is not, then we load a new/clean instance of RSS_Item
			if ( !isset($RSS_Item->id) ) {
				$RSS_Item 			= new RSS_Item();
				$RSS_Item->id	 	= 0; // TODO: Remove when Menno fixes bug
			}
			
		//  Set the RSS_Item values to insert/update
    		$RSS_Item->channel_id 	= $this->_rss_channel_id;
    		$RSS_Item->hash		 	= $hash;
			$RSS_Item->title	 	= $item['title'];
			$RSS_Item->link	 		= $item['link'];
			$RSS_Item->description	= $item['description'];
			$RSS_Item->author	 	= $item['author'];
			$RSS_Item->category	 	= $item['category'];
			$RSS_Item->comments	 	= $item['comments'];
			$RSS_Item->enclosure	= $item['enclosure'];
			$RSS_Item->guid	 		= $item['guid'];
			$RSS_Item->pubdate	 	= ( isset($item['pubdate']) ) ? date( 'Y-m-d H:i:s', strtotime($item['pubdate']) ) : date('Y-m-d H:i:s');
			$RSS_Item->source	 	= $item['source'];
			
			$RSS_Item->save();
		}
    }
    
    private function _save_channel($channel) {
    //  Check if xml data is set
    	if ( !isset($channel) ) {
			throw new WhipConfigException(E_CHANNEL_NOT_SET);
        	return false;
		}
		
	//  Use guid or link for unique item key
		$hash = ( !empty($channel['link']) ) ? md5($channel['link']) : md5($channel['title']);
		
	//  Run DB Query to see if this channel exists
		$RSS_Channel = Whip::Db()->get_one(
					'RSS_Channel',
					Whip::Query()->where('hash', $hash)
					//Whip::Query()->where('url', $channel['url'])
		);
		
	//  Check if this channel is not in our database
	//  If it is not, then we load a new/clean instance of RSS_Channel						   		    
		if ( !isset($RSS_Channel->id) ) {
			$RSS_Channel 		= new RSS_Channel();
			$RSS_Channel->id	= 0; // TODO: Remove when Menno fixes bug
		}
		
	//  Set the RSS_Channel values to insert/update
		$RSS_Channel->url 			= $channel['url'];
		$RSS_Channel->hash 			= $hash;
		$RSS_Channel->title 		= $channel['title'];
		$RSS_Channel->link 			= $channel['link'];
		$RSS_Channel->description 	= $channel['description'];
		$RSS_Channel->language 		= $channel['language'];
		$RSS_Channel->copyright 	= $channel['copyright'];
		$RSS_Channel->managingeditor= $channel['managingeditor'];
		$RSS_Channel->webmaster 	= $channel['webmaster'];
		$RSS_Channel->pubdate 		= ( isset($channel['pubdate']) ) ? date( 'Y-m-d H:i:s', strtotime($channel['pubdate']) ) : date('Y-m-d H:i:s');
		$RSS_Channel->lastbuilddate = ( isset($channel['lastbuilddate']) ) ? date( 'Y-m-d H:i:s', strtotime($channel['lastbuilddate']) ) : date('Y-m-d H:i:s');
		$RSS_Channel->category 		= $channel['category'];
		$RSS_Channel->generator 	= $channel['generator'];
		$RSS_Channel->docs 			= $channel['docs'];
		$RSS_Channel->cloud			= $channel['cloud'];
		$RSS_Channel->ttl			= $channel['ttl'];
		$RSS_Channel->image 		= $channel['image'];
		$RSS_Channel->rating 		= $channel['rating'];
		$RSS_Channel->textinput 	= $channel['textinput'];
		
		$RSS_Channel->save();
		
	// 	TODO: Remove when we return PK ID in save method
		$RSS_Channel 				=  Whip::Db()->get_one(
											'RSS_Channel',
											Whip::Query()->where('hash', $hash)
									   ); 
	// 	Return this channel information for the saving of the rss items
		return $RSS_Channel->id;
    }
    
    private function _get_encoding($xml) {
        preg_match("~\<\?xml.*encoding=[\"\'](.*)[\"\'].*\?\>~i", $xml, $matches);
        return ($matches[1]) ? $matches[1] : '';
    } // function _get_encoding
    
    private function _get_dom_attribute($dom_value = null, $attribute = null) {
    //  Check if xml data is sent
    	if ( !isset($dom_value) ) {
			throw new WhipConfigException(E_XML_VALUES_NOT_SET);
        	return false;
		}
	//  Check if attribute is sent
    	if ( !isset($attribute) ) {
			throw new WhipConfigException(E_XML_ATTRIBUTE_NOT_SET);
        	return false;
		}
    	foreach ( $dom_value as $params ) {
    		$attribute_value = $params->getAttribute($attribute);
			return ( isset($attribute_value) ) ? $attribute_value : null;
		}
    } // function _get_dom_attribute
    
    
    private function _get_dom_value($dom_value = null) {
    //  Check if xml data is sent
    	if ( !isset($dom_value) ) {
			throw new WhipConfigException(E_XML_VALUES_NOT_SET);
        	return false;
		}
		
		return ( isset($dom_value->item(0)->nodeValue) ) ? $dom_value->item(0)->nodeValue : null;
    } // function _get_dom_value
}   //  class Rss