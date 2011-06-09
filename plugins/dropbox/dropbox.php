<?php
//  Require OAuth extension
    if (!class_exists('OAuth')) {
        throw new WhipPluginException('OAuth extension required.');
    }
    
/**
 * Dropbox plugin.
 *
 * Requires the OAuth extension:
 * http://php.net/manual/en/book.oauth.php
 *
 * Loosely based on the Dropbox class by:
 * @author Evert Pot (http://www.rooftopsolutions.nl/) 
 * @license http://code.google.com/p/dropbox-php/wiki/License MIT
 *
 * Whip : A non-restrictive PHP framework
 * Copyright (c) 2010 Menno van Ens (codefocus.ca) and Shawn Mayzes (mayzes.org)
 * Released under the GNU General Public License, Version 3 
 *
 * @extends WhipPlugin
 */
class Dropbox extends WhipPlugin {
    const HTTP_NOT_MODIFIED     = 304;
    const HTTP_FORBIDDEN        = 403;
    const HTTP_NOT_FOUND        = 404;
    const HTTP_OVER_QUOTA       = 507;
    const DROPBOX_API_URL       = 'http://api.dropbox.com/0';
    const DROPBOX_CONTENT_URL   = 'http://api-content.dropbox.com/0';
    const ROOT_SANDBOX          = 'sandbox';
    const ROOT_DROPBOX          = 'dropbox';
    
    private $_oauth = null;
    private $_root  = self::ROOT_DROPBOX;
    
    
    /**
     * list_files function.
     * List all files in a dropbox folder
     * 
     * @access public
     * @param mixed $folder
     * @return array
     */
    public function list_files($path='', $root=null) {
    //  Determine root
        if (is_null($root)) {
            $root = $this->_root;
        }
    //  Perform REST call
        $rest_url   = self::DROPBOX_API_URL.'/metadata/'.trim($root, '/').'/'.ltrim($path,'/');
        $result     = $this->_fetch($rest_url);
        return json_decode($result['body']);
    }   //  function list_files
    
    
    /**
     * get_account_information function.
     * 
     * @access public
     * @return stdClass
     */
    public function get_account_information() {
        $data = $this->oauth->fetch(self::DROPBOX_API_URL.'/account/info');
        return json_decode($data['body'],true);
    }   //  function get_account_information
    
    
    /**
     * create_account function.
     * Create a new Dropbox account
     * 
     * @access public
     * @param mixed $email
     * @param mixed $first_name
     * @param mixed $last_name
     * @param mixed $password
     * @return bool
     */
    public function create_account($email, $first_name, $last_name, $password) {
        $result = $this->oauth->fetch(
            self::DROPBOX_API_URL.'/account',
            array(
                'email'      => $email,
                'first_name' => $first_name,
                'last_name'  => $last_name,
                'password'   => $password,
            ),
            'POST'
        );
        return $result['body']==='OK';
    }   //  function create_account
    
    
    
    /**
     * get_file function.
     * Get a file's contents
     * 
     * @access public
     * @param string $path. (default: '')
     * @param mixed $root. (default: null)
     * @return string
     */
    public function get_file($path = '', $root = null) {
    //  Determine root
        if (is_null($root)) {
            $root = $this->_root;
        }
        $rest_url   = self::DROPBOX_CONTENT_URL.'files/'.trim($root, '/').'/'.ltrim($path,'/');
        $result     = $this->oauth->fetch($rest_url);
        return $result['body'];
    }   //  function get_file
    
    
    /**
     * Returns file and directory information
     * 
     * @param string $path Path to receive information from 
     * @param bool $list When set to true, this method returns information from all files in a directory. When set to false it will only return infromation from the specified directory.
     * @param string $hash If a hash is supplied, this method simply returns true if nothing has changed since the last request. Good for caching.
     * @param int $fileLimit Maximum number of file-information to receive 
     * @param string $root Use this to override the default root path (sandbox/dropbox) 
     * @return array|true 
     */
    public function get_meta_data($path, $list=true, $hash=null, $fileLimit=null, $root=null) {
    //  Determine root
        if (is_null($root)) {
            $root = $this->_root;
        }
        $args = array(
            'list' => $list,
        );
        if (!is_null($hash)) {
            $args['hash'] = $hash;
        }
        if (!is_null($fileLimit)) {
            $args['file_limit'] = $hash;
        }
        $rest_url   = self::DROPBOX_API_URL.'metadata/'.trim($root, '/').'/'.ltrim($path, '/');
        $response   = $this->_fetch($rest_url, $args);
        if ($response['httpStatus']==self::HTTP_NOT_MODIFIED) {
            return true; 
        } else {
            return json_decode($response['body'],true);
        }

    }   //  function get_meta_data
    
    
    
    
    
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
        if (isset($this->_config['token']) && isset($this->_config['token'])) {
        //  Token and secret are set.
        //  Use default OAuth login procedure
            $this->_oauth->setToken(
                $this->_config['token'],
                $this->_config['secret']
            );
        }
        elseif (isset($this->_config['email']) && isset($this->_config['password'])) {
        //  Token and secret are NOT set, but we have an email and password.
        //  Use these to get tokens from OAuth
        //  and warn the user to put these tokens in the configuration file.
            $tokens = $this->_get_oauth_token($this->_config['email'], $this->_config['password']);
            throw new WhipPluginException(
                "Please add these OAuth tokens to your Whip Dropbox configuration:\n".
                "\$config['Dropbox']['token'] = '".$tokens['token']."';\n".
                "\$config['Dropbox']['secret'] = '".$tokens['secret']."';"
            );
        }
        else {
        //  No login credentials whatsoever
            throw new WhipPluginException('No dropbox login information configured.');
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
    private function _get_oauth_token($email, $password) {
        $data = $this->_fetch(
            self::DROPBOX_API_URL.'/token',
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
                throw new WhipPluginException('Bad OAuth request, or file or folder already exists.');
            case self::HTTP_NOT_FOUND:
            //  Not found
                throw new WhipPluginException('Resource at uri: ' . $uri . ' could not be found.');
            case self::HTTP_OVER_QUOTA:
            //  Over quota
                throw new WhipPluginException('This dropbox is full.');
            default:
            //  Rethrow exception
                throw $e;
            }   //  switch http response code
        }
    }   //  function _fetch
    
}   //  class Dropbox
