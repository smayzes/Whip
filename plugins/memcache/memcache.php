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
 * Very rudimentary wrapper around Memcached
 * @TODO: Working, but needs lots of work.
 *
 * Whip : A non-restrictive PHP framework
 * Copyright (c) 2010 Menno van Ens (codefocus.ca) and Shawn Mayzes (mayzes.org)
 * Released under the GNU General Public License, Version 3 
 *
 * @extends WhipPlugin
 */
class Memcache extends SingletonWhipPlugin {
	
	const DEFAULT_HOST		= 'localhost';
	const DEFAULT_PORT		= 11211;
	
	private $_memcache		= null;
	
	
	/**
	 * _get_memcache_instance function.
	 * 
	 * @access private
	 * @return Memcached
	 */
	private function _get_memcache_instance() {
		if ($this->_memcache instanceof Memcached) {
		//	Return existing Memcached object
			return $this->_memcache;
		}
	//	Create new Memcached instance
		if (isset($this->_config['id']))
		$this->_memcache = new Memcached($this->_config['id']);
		
		$servers = array();
		if (
			isset($this->_config['servers']) AND
			is_array($this->_config['servers']) AND
			0 < count($this->_config['servers'])
		) {
		//	Add each server to our array
		//	and default to port 11211
			foreach($this->_config['servers'] as $server) {
				if (is_array($server)) {
					if (
						isset($server['host']) AND
						isset($server['port']) AND
						is_numeric($server['port'])
					) {
					//	We have an array of host and port
						$servers[] = array(
							'host' => $server['host'],
							'port' => $server['port'],
						);
					}
					elseif (isset($server['host'])) {
					//	We have an array but only the host is set.
						$servers[] = array(
							'host' => $server['host'],
							'port' => self::DEFAULT_PORT,
						);
					}
				}
				elseif (is_string($server)) {
				//	We have a string.
				//	Treat it as the host
					$servers[] = array(
						'host' => $server,
						'port' => self::DEFAULT_PORT,
					);
				}
			}	//	each configured server
		}	//	if config array
		
		if (0 == count($servers)) {
		//	No servers configured; use default server
			$servers[] = array(
				'host' => self::DEFAULT_HOST,
				'port' => self::DEFAULT_PORT,
			);
		}	//	if no servers
		
	//	Add server(s) to Memcache
		foreach ($servers as $server) {
			$this->_memcache->addServer($server['host'], $server['port']);
		}	//	each server
		
		return $this->_memcache;
		
	}	//	function _get_memcache_instance
	
	
	/**
	 * stats function.
	 * Return Memcached stats.
	 * 
	 * @access public
	 * @return array
	 */
	public function stats() {
		$memcache = $this->_get_memcache_instance();
		return $memcache->getStats();
	}	//	function stats
	
	
	/**
	 * get function.
	 * Retrieves a value from Memcached.
	 * 
	 * @access public
	 * @param string $key
	 * @return string
	 */
	public function get($key) {
		$memcache = $this->_get_memcache_instance();
		return $memcache->get($key);
	}	//	function get
	
	
	/**
	 * set function.
	 * Stores a value in Memcached.
	 * 
	 * @access public
	 * @param string $key
	 * @param mixed $value
	 * @return boolean
	 */
	public function set($key, $value, $expiration=null) {
		$memcache = $this->_get_memcache_instance();
		
		if (null === $expiration) {
			if (isset($this->_config['expiration'])) {
				$expiration = $this->_config['expiration'];
			}
		}
		if (!is_numeric($expiration)) {
			$expiration = 0;
		}
		return $memcache->set($key, $value, $expiration);
	}	//	function set
	
	
	/**
	 * delete function.
	 * 
	 * @access public
	 * @param string $key
	 * @return void
	 */
	public function delete($key) {
		$memcache = $this->_get_memcache_instance();
		return $memcache->delete($key);
	}	//	function delete
	
	
	/**
	 * increment function.
	 * 
	 * @access public
	 * @param mixed $key
	 * @param int $offset. (default: 1)
	 * @return void
	 */
	public function increment($key, $offset=1) {
		$memcache = $this->_get_memcache_instance();
		return $memcache->increment($key, $offset);
	}	//	function increment
	
	
	/**
	 * decrement function.
	 * 
	 * @access public
	 * @param mixed $key
	 * @param int $offset. (default: 1)
	 * @return void
	 */
	public function decrement($key, $offset=1) {
		$memcache = $this->_get_memcache_instance();
		return $memcache->decrement($key, $offset);
	}	//	function decrement
	
	
	/**
	 * flush function.
	 * 
	 * @access public
	 * @return void
	 */
	public function flush() {
		$memcache = $this->_get_memcache_instance();
		return $memcache->flush();
	}	//	function flush
	    
    
}   //  class Memcache

