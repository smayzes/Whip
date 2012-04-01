<?php

/**
 * Reddit plugin.
 *
 *
 * Whip : A non-restrictive PHP framework
 * Copyright (c) 2010 Menno van Ens (codefocus.ca) and Shawn Mayzes (mayzes.org)
 * Released under the GNU General Public License, Version 3 
 *
 * @extends WhipPlugin
 */
class Reddit extends WhipPlugin {
	const THING_TYPE_COMMENT	= 't1';
	const THING_TYPE_ACCOUNT	= 't2';
	const THING_TYPE_LINK		= 't3';
	const THING_TYPE_MESSAGE	= 't4';
	const THING_TYPE_SUBREDDIT	= 't5';
	
//	Defaults
	protected $_config = array(
		'user-agent'	=> 'Reddit bot constructed using the Whip framework (http://www.whipframework.com/)',
		//'cookie'		=> '',
		//'modhash'		=> '',
	);
	
	
	public function posts($subreddit='all') {
	//	POST to reddit
		$url = 'http://www.reddit.com/r/'.urlencode($subreddit).'.json';
		return $this->_get($url);
		//http://www.reddit.com/r/all
		
		
	}	//	function frontpage
	
	
	/**
	 * comment function.
	 * 
	 * @access public
	 * @param mixed $thing_id
	 * @param mixed $text
	 * @return void
	 */
	public function comment($thing_id, $thing_type, $text) {
	//	POST to reddit
		$url = 'http://www.reddit.com/api/comment';
		$post_fields = array(
			'thing_id'	=> urlencode($thing_type.'_'.$thing_id),
			'text'		=> urlencode($text),
			'uh'		=> $this->_config['modhash'],
			//'api_type'	=> 'json',
		);
		return $this->_post($url, $post_fields);
	}	//	function comment
	
	
	/**
	 * login function.
	 * This should only be executed once.
	 * Store the returned reddit cookie and modhash in the config file.
	 * These values last forever.
	 * 
	 * @access public
	 * @param mixed $username. (default: null)
	 * @param mixed $password. (default: null)
	 * @return void
	 */
	public function login($username=null, $password=null) {
	//	Set username / password
		if (null != $username) {
			$this->_config['username'] = $username;
		}
		if (null != $password) {
			$this->_config['password'] = $password;
		}
	//	Require username / password
		if (!isset($this->_config['username']) OR !isset($this->_config['password'])) {
			throw new WhipPluginException('Username and password required');
		}
	//	POST to reddit
		$url = 'http://www.reddit.com/api/login/'.urlencode($this->_config['username']);
		$post_fields = array(
			'user'		=> urlencode($this->_config['username']),
			'passwd'	=> urlencode($this->_config['password']),
			'api_type'	=> 'json',
		);
		$result = $this->_post($url, $post_fields);
	//	TODO: return cookie and modhash
		$result = json_decode($result);
		echo '<pre>'.print_r($result, true).'</pre>';
		return true;
	}	//	function login
	
	
	
	
	
	
	private function _get($url) {
	//	GET
		try {
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_USERAGENT, $this->_config['user-agent']);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_TIMEOUT, 4);
			curl_setopt($ch, CURLOPT_POST, false);
			$result = curl_exec($ch);
			curl_close($ch);
		}
		catch(Exception $e) {
			echo '<pre>CURL ERROR!'.print_r($e, true).'</pre>';
			return false;
		}
		
	//	Decode JSON if necessary
		if (!is_object($result)) {
			$result = json_decode($result);
		}
	//	Check response for errors
		if (isset($result->json) AND isset($result->json->errors) AND count($result->json->errors)) {
			echo '<h1>Reddit error</h1>';
			echo '<pre>'.print_r($result, true).'</pre>';
			throw new WhipPluginException($result->json->errors[0][1]);
			return false;
		}
	//	Return result
		return $result;
	}	//	function _get
	
	
	
	/**
	 * _post function.
	 * 
	 * @access private
	 * @param mixed $url
	 * @param mixed array $post_fields
	 * @return object
	 */
	private function _post($url, array $post_fields) {
	//	Build POST data
		$post_data = $this->_combine_post_fields($post_fields);
	//	POST
		try {
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_USERAGENT, $this->_config['user-agent']);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_TIMEOUT, 4);
			curl_setopt($ch, CURLOPT_POST, count($post_fields));
			curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
			//curl_setopt($ch, CURLOPT_COOKIEFILE, $this->_config['cookie_file']);
			//curl_setopt($ch, CURLOPT_COOKIEJAR, $this->_config['cookie_file']);
			if (isset($this->_config['cookie']) AND !empty($this->_config['cookie'])) {
			//	Set cookie
				curl_setopt($ch, CURLOPT_COOKIESESSION, true); 
				$cookie = 'reddit_session='.urlencode($this->_config['cookie']).'; Domain=reddit.com; expires=Thu, 31 Dec 2037 23:59:59 GMT; Path=/';
				curl_setopt($ch, CURLOPT_COOKIE, $cookie);
				//echo '<pre>'.print_r($cookie, true).'</pre>';exit();
			}
			$result = curl_exec($ch);
			curl_close($ch);
		}
		catch(Exception $e) {
			echo '<pre>CURL ERROR!'.print_r($e, true).'</pre>';
			return false;
		}
	//	DEBUG
		echo '<pre>DEBUG: REQUEST: '.print_r($post_data, true).'</pre>';
		echo '<pre>DEBUG: RESULT: '.print_r($result, true).'</pre>';
		
	//	Decode JSON if necessary
		if (!is_object($result)) {
			$result = json_decode($result);
		}
	//	Check response for errors
		if (isset($result->json) AND isset($result->json->errors) AND count($result->json->errors)) {
			echo '<h1>Reddit error</h1>';
			echo '<pre>'.print_r($result, true).'</pre>';
			throw new WhipPluginException($result->json->errors[0][1]);
			return false;
		}
	//	Return result
		return $result;
	}	//	function _post
	
	
	/**
	 * _combine_post_fields function.
	 * 
	 * @access private
	 * @param mixed array $post_fields
	 * @return void
	 */
	private function _combine_post_fields(array $post_fields) {
		$post_data = '';
		foreach($post_fields as $key=>$value) {
			$post_data .= $key.'='.$value.'&';
		}
		$post_data = rtrim($post_data,'&');
		return $post_data;
	}	//	function _combine_post_fields
	
	
}	//	class Reddit

