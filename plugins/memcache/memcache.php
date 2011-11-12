<?php

//  Require Memcached extension
	if (!class_exists('Memcached')) {
		throw new WhipPluginException(
			'The Memcached extension is required to run this plugin. '.
			'Refer to http://www.php.net/manual/en/book.memcached.php for more information.'
		);
	}


/**
 * Memcache plugin.
 *
 *
 * Whip : A non-restrictive PHP framework
 * Copyright (c) 2010 Menno van Ens (codefocus.ca) and Shawn Mayzes (mayzes.org)
 * Released under the GNU General Public License, Version 3 
 *
 * @extends WhipPlugin
 */
class Memcache extends SingletonWhipPlugin {

//	@TODO    
    
}   //  class Memcache
