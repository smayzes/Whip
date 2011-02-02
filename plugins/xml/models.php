<?php
/**
*   Models
*   Database models
*/

//  rss_channel
    class RSS_Channel extends WhipModel {
    	public static $_pk          = 'id';
        public static $_table       = 'rss_channel';
        public static $_fields      = array(
			'id', 
			'hash', 
			'url', 
			'title', 
			'link', 
			'description', 
			'language', 
			'copyright',
			'managingeditor', 
			'webmaster', 
			'pubdate', 
			'lastbuilddate', 
			'category', 
			'generator', 
			'docs', 
			'cloud', 
			'ttl', 
			'image', 
			'rating', 
			'textinput',
			'status',
        );
    } // RSS_Channel
	
//  rss_item
    class RSS_Item extends WhipModel {
        public static $_pk          = 'id';
        public static $_table       = 'rss_item';
        public static $_fields      = array(
			'id', 
			'channel_id', 
			'hash',
			'title', 
			'link', 
			'description', 
			'author', 
			'category', 
			'comments', 
			'enclosure', 
			'guid', 
			'pubdate', 
			'source',
        );
    } //RSS_Item