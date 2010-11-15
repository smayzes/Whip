<?php    
/**
 * Whip class.
 * 
 * Whip : A non-restrictive PHP framework
 * Copyright (c) 2010 Menno van Ens (codefocus.ca) and Shawn Mayzes (mayzes.org)
 * Released under the GNU General Public License, Version 3
 * 
 */
class Whip {
    protected static $_instance = null;         //  instance of Whip
    protected static $_config = array();        //  configuration array
    protected static $_is_initialized = false;  //  Is Whip initialized?

    protected static $_plugins = array();       //  all loaded plugins
    protected static $_models = array();        //  all loaded models    
    
    /**
     * __construct function.
     *
     * Prevent users from instantiating Whip.
     * 
     * @access private
     * @param mixed array $config. (default: null)
     * @return void
     */
    private function __construct(array $config=array()) {
    //  Make sure we're initialized
        if (!self::$_is_initialized) {
            self::_initialize();
        }
    //  merge configuration array
        if (is_array($config)) {
            array_merge(self::$_config, $config);
        }
    }   //  __construct
    
    
    /**
     * _initialize function.
     * 
     * @access private
     * @static
     * @return void
     */
    private static function _initialize() {
    //  Get Whip path, ending in slash
        self::$_config['path'] = self::_real_path(__FILE__);
    //  Include base classes for exception, model and plugin
        require_once(self::$_config['path'].'core/exception.php');
        require_once(self::$_config['path'].'core/model.php');
        require_once(self::$_config['path'].'core/plugin.php');
    //  Mark Whip as initialized
        self::$_is_initialized = true;
    }   //  _initialize
    
    
    /**
     * get_instance function.
     *
     * Configure and initialize Whip
     * 
     * @access public
     * @return Whip Current instance of Whip
     */
    public static function get_instance(array $config=array()) {
        if (!self::$_instance instanceof Whip) {
            self::$_instance = new Whip();
        }
        return self::$_instance;
    }   //  get_instance
    
    
    /**
     * model function.
     *
     * Load a model
     * 
     * @access public
     * @static
     * @param mixed $name
     * @return void
     */
    public static function model($name) {
        //TODO:
        if (!self::$_instance instanceof Whip) {
            self::$_instance = new Whip();
        }
        return null;
    }   //  model
    
    
    /**
     * plugin function.
     * 
     * @access public
     * @static
     * @param mixed $name
     * @param bool $return. (default: true)
     * @return WhipPlugin
     */
    public static function plugin($name, array $config=array()) {
    //  Load plugin
        self::_plugin_load($name);
        return call_user_func($name.'::get_instance', $config);
    }   //  plugin
    
    
    /**
     * __call function.
     *
     * Shortcut for Whip::plugin($name, $config)
     * 
     * @access public
     * @static
     * @param mixed string $name
     * @param mixed array $config
     * @return void
     */
    public static function __callStatic($name, array $arguments) {
        if (count($arguments)) {
            return self::plugin($name, (array)$arguments[0]);
        }
        else {
            return self::plugin($name);
        }
    }   //  __call
    
    
    /**
     * _real_path function.
     * 
     * @access private
     * @static
     * @param mixed $path
     * @return void
     */
    private static function _real_path($path) {
        $real_path = dirname(realpath($path));
        if (substr($real_path, -1, 1) != '/') {
            $real_path .= '/';
        }
        return $real_path;
    }   //  real_path
    
    
    /**
     * _plugin_load function.
     * 
     * @access private
     * @static
     * @param mixed $name
     * @return void
     */
    private static function _plugin_load($name) {
    //  Check if plugin is already loaded
        if (class_exists($name)) {
            /*
            if ($name instanceof WhipPlugin) {
                return true;
            }
            */
            return true;
        }
    //  Check if plugin filename is valid
        $plugin_filename = strtolower($name);
        if (!preg_match('/[a-z0-9]+/', $plugin_filename)) {
        //  Invalid plugin name
            throw new WhipException(E_PLUGIN_INVALID_NAME);
        }
    //  Make sure we're initialized
        if (!self::$_is_initialized) {
            self::_initialize();
        }
    //  Require plugin
        require_once(self::$_config['path'].'plugins/'.$plugin_filename.'/'.$plugin_filename.'.php');
        
    }   //  _plugin_load
    
        
}   //  class Whip

