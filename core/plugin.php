<?php

/**
 * WhipPlugin class.
 * 
 * Whip : A non-restrictive PHP framework
 * Copyright (c) 2010 Menno van Ens (codefocus.ca) and Shawn Mayzes (mayzes.org)
 * Released under the GNU General Public License, Version 3
 *
 */
abstract class WhipPlugin {
//  Cross-instance
    protected static $_instances = array();         //  instances of this plugin
    protected static $_require = array('Db');       //  array: names of plugins required to run this plugin
    
//  Per instance
    protected $_config = array();                   //  configuration array
    
    /**
     * __construct function.
     *
     * Prevent users from instantiating the plugin directly
     * 
     * @access private
     * @return void
     */
    protected function __construct($config=null) {
    //  merge configuration array
        $this->set_config($config);
    }   //  __construct
    
    
    /**
     * get_require function.
     *
     * Get names of plugins required to run this plugin
     * 
     * @access public
     * @static
     * @return void
     */
    public static function get_require() {
        return self::$_require;
    }   //  get_require
    
    
    /**
     * get_config function.
     * 
     * @access public
     * @return void
     */
    public function get_config() {
        return $this->_config;
    }   //  get_config
    
    /**
     * set_config function.
     * 
     * @access public
     * @return void
     */
    public function set_config($config=array()) {
        if (is_array($config)) {
            $this->_config = array_merge($this->_config, $config);
        }
        else {
            $this->_config = $config;
        }
    }   //  set_config
    
    
    /**
     * get_instance function.
     *
     * Configure and initialize this plugin.
     * Default instantiation policy is to return a cached instance
     * if one is available with the same config as specified.
     * 
     * Plugins can override this function to define their own instantiation policy;
     * 
     * @access public
     * @return WhipPlugin Current instance of this plugin
     */
    public static function get_instance(array $config_array=array()) {
    //  Get the called class name
        $class_name = get_called_class();
    //  Merge config parameter and global config
    //  into one array which we'll use for configuring this instance
        if (isset($GLOBALS['config'][$class_name]) && is_array($GLOBALS['config'][$class_name])) {
            $config = array_merge($GLOBALS['config'][$class_name], $config_array);
        }
        else {
            $config = &$config_array;
        }
    //  Check if we have instances
        if (isset(WhipPlugin::$_instances[$class_name]) && is_array(WhipPlugin::$_instances[$class_name])) {
            if (count(WhipPlugin::$_instances[$class_name])) {
            //  We have instances.
            //  Loop through instances to see if one of these
            //  should be returned instead of creating a new one.
                foreach(WhipPlugin::$_instances[$class_name] as $instance) {
                    if ($instance instanceof WhipPlugin) {
                        if ($instance->get_config() == $config) {
                        //  Config matches entirely.
                        //  Return cached instance.
                            return $instance;
                        }
                    }
                }   //  each instance
            }
        }
        else {
            WhipPlugin::$_instances[$class_name] = array();
        }
    //  No qualifying instance found;
    //  Create and return a new instance
        $instance = new $class_name($config);
        WhipPlugin::$_instances[$class_name][] = $instance;
        return $instance;
    }   //  get_instance
    
    
}   //  class WhipPlugin



/**
 * Abstract SingletonWhipPlugin class.
 *
 * Instances of these plugins will only ever have ONE active instance.
 * 
 * @extends WhipPlugin
 * @abstract
 */
abstract class SingletonWhipPlugin extends WhipPlugin {
    /**
     * get_instance function.
     *
     * Configure and initialize this plugin.
     * 
     * @access public
     * @return WhipPlugin Current instance of this plugin
     */
    public static function get_instance(array $config_array=array()) {
    //  Get the called class name
        $class_name = get_called_class();
    //  Merge config parameter and global config
    //  into one array which we'll use for configuring this instance
        if (isset($GLOBALS['config'][$class_name])) {
            $config = array_merge($GLOBALS['config'][$class_name], $config_array);
        }
        else {
            $config = &$config_array;
        }
    //  Return cached instance if available
        if (isset(WhipPlugin::$_instances[$class_name]) && is_array(WhipPlugin::$_instances[$class_name])) {
            if (count(WhipPlugin::$_instances[$class_name])) {
                return WhipPlugin::$_instances[$class_name][0];
            }
        }
        else {
            WhipPlugin::$_instances[$class_name] = array();
        }
    //  No qualifying instance found;
    //  Create and return a new instance
        $instance = new $class_name($config);
        WhipPlugin::$_instances[$class_name][] = $instance;
        return $instance;
    }   //  get_instance
    
}   //  class SingletonWhipPlugin


/**
 * Abstract UncachedWhipPlugin class.
 * 
 * Instances of these plugin will never be cached.
 * Every call to get_instance will yield a new instance.
 * 
 * @extends WhipPlugin
 * @abstract
 */
abstract class UncachedWhipPlugin extends WhipPlugin {
    /**
     * get_instance function.
     *
     * Configure and initialize this plugin.
     * 
     * @access public
     * @return WhipPlugin Current instance of this plugin
     */
    public static function get_instance(array $config_array=array()) {
    //  Get the called class name
        $class_name = get_called_class();
    //  Merge config parameter and global config
    //  into one array which we'll use for configuring this instance
        if (isset($GLOBALS['config'][$class_name])) {
            $config = array_merge($GLOBALS['config'][$class_name], $config_array);
        }
        else {
            $config = &$config_array;
        }
    //  Return shiny new instance
        return new $class_name($config);
    }   //  get_instance
    
}   //  class UncachedWhipPlugin



