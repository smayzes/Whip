<?php

/**
 * Browser plugin.
 *
 *
 * Whip : A non-restrictive PHP framework
 * Copyright (c) 2010 Menno van Ens (codefocus.ca) and Shawn Mayzes (mayzes.org)
 * Released under the GNU General Public License, Version 3 
 *
 * @extends WhipPlugin
 */
class Browser extends SingletonWhipPlugin {
    
    private $_ip = null;
    
    const DEVICE_IPAD       = 'ipad';
    const DEVICE_IPOD       = 'ipod';
    const DEVICE_IPHONE     = 'iphone';
    const DEVICE_ANDROID    = 'android';
    const DEVICE_MOBILE     = 'mobile';     //  Generic mobile
    const DEVICE_DESKTOP    = 'desktop';    //  Non-mobile
    
    /**
     * get_ip function.
     *
     * Get remote IP
     * Currently not supporting IPv6.
     * 
     * @access public
     */
    public function get_ip() {
        if (null == $this->_ip) {
        //  Get IP
            if (isset($_SERVER['HTTP_X_FORWARDED_FOR']) && strlen($_SERVER['HTTP_X_FORWARDED_FOR'])>5) {
                $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
            }
            elseif (isset($_SERVER['X_FORWARDED_FOR']) && strlen($_SERVER['X_FORWARDED_FOR'])>5) {
                $ip = $_SERVER['X_FORWARDED_FOR'];
            }
            else {
                $ip = $_SERVER['REMOTE_ADDR'];
            }
            if (ip2long($ip)==0) {
                $ip = @gethostbyname($ip);
            }
            $this->_ip = $ip;
        }
        return $this->_ip;
    }   //  get_ip
    
    
    /**
     * get_device function.
     *
     * Very simple mobile device detection
     * 
     * @access public
     */
    function get_device() {
        if (isset($_SERVER['HTTP_USER_AGENT'])) {
        //  Get user-agent
            $user_agent = $_SERVER['HTTP_USER_AGENT'];
        }
        else {
        //  No user-agent provided; Assume desktop by default
            return Browser::DEVICE_DESKTOP;
        }
    //  Detect (some) mobile devices
        if (false !== strpos($user_agent, 'Mobile')) {
        //  Mobile
            if (false !== strpos($user_agent, 'iPhone')) {
            //  iPhone
                return Browser::DEVICE_IPHONE;
            }
            if (false !== strpos($user_agent, 'Android')) {
            //  Android 1.0+
                return Browser::DEVICE_ANDROID;
            }
            if (false !== strpos($user_agent, 'iPad')) {
            //  iPad
                return Browser::DEVICE_IPAD;
            }
            if (false !== strpos($user_agent, 'iPod')) {
            //  iPod
                return Browser::DEVICE_IPOD;
            }
            return Browser::DEVICE_MOBILE;
        }   //  Mobile
    //  Not a mobile device
        return Browser::DEVICE_DESKTOP;
    }   //  function get_device

}   //  class Browser

