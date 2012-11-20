<?php
//  Require OAuth extension
	if (!class_exists('OAuth')) {
		throw new WhipPluginException(
			'The OAuth extension is required to run this plugin. '.
			'Refer to http://www.php.net/manual/en/book.oauth.php for more information.'
		);
	}
    
/**
 * Twitter plugin 2.
 *
 * Requires the OAuth extension:
 * http://php.net/manual/en/book.oauth.php
 *
 * Whip : A non-restrictive PHP framework
 * Copyright (c) 2010 Menno van Ens (codefocus.ca) and Shawn Mayzes (mayzes.org)
 * Released under the GNU General Public License, Version 3 
 *
 * @extends WhipPlugin
 */
class Twitter2 extends WhipPlugin {
    const HTTP_NOT_MODIFIED     = 304;
    const HTTP_FORBIDDEN        = 403;
    const HTTP_NOT_FOUND        = 404;
    const HTTP_OVER_QUOTA       = 507;
    
	const API_URL			= 'https://api.twitter.com/1';
	const SEARCH_API_URL	= 'https://search.twitter.com';
	const SECURE_API_URL	= 'https://api.twitter.com';
    
    private $_oauth = null;
    
    
    /**
     * users_search function.
     * 
     * @access public
     * @param mixed $q
     * @return void
     */
    public function users_search($q) {
        $rest_url   = self::API_URL.'/users/search.json';
        $params		= array(
        	'q'					=> $q,
        );
        $result     = $this->_fetch($rest_url, $params);
        return json_decode($result['body'],true);
    }	//	function users_search
    
    
    /**
     * user_timeline function.
     * 
     * @access public
     * @param string $username
     * @return void
     */
    public function user_timeline($username, $count=3) {
        $rest_url   = self::API_URL.'/statuses/user_timeline.json';
        $params		= array(
        	'screen_name'		=> $username,
        	'count'				=> $count,
        	//'trim_user'			=> true,
        	'include_entities'	=> true,
        );
        $result     = $this->_fetch($rest_url, $params);
        return json_decode($result['body'],true);
    }    //	function user_timeline
    
    
    
    
    
    
    
    /**
     * _initialize_oauth function.
     * 
     * @access private
     * @return void
     */
    private function _initialize_oauth() {
        if ($this->_oauth instanceof OAuth) {
        //  OAuth already initialized
            return;
        }
    //  Initialize OAuth object
        $this->_oauth = new OAuth(
            $this->_config['consumer_key'],
            $this->_config['consumer_secret'],
            OAUTH_SIG_METHOD_HMACSHA1,
            OAUTH_AUTH_TYPE_URI
        );
        $this->_oauth->enableDebug();
    //  Log in
        if (isset($this->_config['oauth_token']) && isset($this->_config['oauth_token'])) {
        //  Token and secret are set.
        //  Use default OAuth login procedure
            $this->_oauth->setToken(
                $this->_config['oauth_token'],
                $this->_config['oauth_token_secret']
            );
        }
        else {
        //  No login credentials whatsoever
            throw new WhipPluginException('No Twitter2 login information configured.');
        }
    }   //  function _initialize_oauth
    
    /**
     * _get_oauth_token function.
     * Retrieve OAuth login tokens using email / password
     * 
     * @access private
     * @param mixed $email
     * @param mixed $password
     * @return array
     */
    /*
    private function _get_oauth_token($email, $password) {
        $data = $this->_fetch(
            self::API_URL.'/token',
            array(
                'email'     => $email,
                'password'  => $password
            ),
            'POST'
        );
        $data = json_decode($data['body']);
        return array(
            'token'         => $data->token,
            'secret'        => $data->secret,
        );
    }   //  function _get_oauth_token
    */
    
    /**
     * _fetch function.
     * Execute an OAuth request
     * 
     * @access private
     * @param string $uri
     * @param array $arguments. (default: array())
     * @param string $method. (default: 'GET')
     * @param array $http_headers. (default: array())
     * @return string
     */
    private function _fetch($uri, $arguments=array(), $method='GET', $http_headers=array()) {
    //  Make sure OAuth is loaded
        $this->_initialize_oauth();
    //  Make OAuth call
        try {
            $this->_oauth->fetch($uri, $arguments, $method, $http_headers);
            $result = $this->_oauth->getLastResponse();
            $last_response_info = $this->_oauth->getLastResponseInfo();
            return array(
                'http_status'   => $last_response_info['http_code'],
                'body'          => $result,
            );
        }
        catch (OAuthException $e) {
            $last_response_info = $this->_oauth->getLastResponseInfo();
            echo '<pre>'.print_r($last_response_info, true).'</pre>';
            switch($last_response_info['http_code']) {
            case self::HTTP_NOT_MODIFIED:
            //  Not modified
                return array(
                    'http_status'   => self::HTTP_NOT_MODIFIED,
                    'body'          => null,
                );
                break;
            case self::HTTP_FORBIDDEN:
            //  Forbidden
                throw new WhipPluginException('Bad OAuth request, forbidden.');
            case self::HTTP_NOT_FOUND:
            //  Not found
                throw new WhipPluginException('Resource at uri: ' . $uri . ' could not be found.');
            case self::HTTP_OVER_QUOTA:
            //  Over quota
                throw new WhipPluginException('Over quota.');
            default:
            //  Rethrow exception
                throw $e;
            }   //  switch http response code
        }
    }   //  function _fetch
    
    
}   //  class Twitter2
