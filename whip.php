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
            self::$_config = array_merge(self::$_config, $config);
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
        self::$_config['path'] = self::real_path(__DIR__);
    //  Include base classes for exception, model and plugin
        require_once(self::$_config['path'].'core/exception.php');
        require_once(self::$_config['path'].'core/model.php');
        require_once(self::$_config['path'].'core/plugin.php');
        require_once(self::$_config['path'].'core/controller.php');
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
            self::$_instance = new Whip($config);
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
     * real_path function.
     * 
     * @access private
     * @static
     * @param mixed $path
     * @return string
     */
    public static function real_path($path) {
    //  Resolve symlinks and ../../
        $real_path = realpath($path);
    //  Reconstruct the path
        $path_info = pathinfo($real_path);
        if (!isset($path_info['dirname'])) {
            return false;
        }
        $real_path = $path_info['dirname'].'/'.$path_info['basename'];
    //  End folders in a slash
        if (is_dir($real_path)) {
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
    
    
    /**
     * gp function.
     *
     * Get a get/post variable
     * 
     * @access public
     * @static
     * @param mixed $n
     * @param mixed $default. (default: null)
     */
    public static function gp($n, $default=null, $regex=null) {
        if (isset($_POST[$n])) {
            $v = $_POST[$n];
        }
        elseif (isset($_GET[$n])) {
            $v = $_GET[$n];
        }
        else {
            $v = $default;
        }
        if (null !== $regex && $v !== $default) {
        //  ...
            if (!preg_match('/'.$regex.'/', $v)) {
                $v = $default;
            }
        }
        return $v;
    }   //  function gp
    
    /**
     * get function.
     *
     * Get a GET variable
     * 
     * @access public
     * @static
     * @param mixed $n
     * @param mixed $default. (default: null)
     */
    public static function get($n, $default=null, $regex=null) {
        if (isset($_GET[$n])) {
            $v = $_GET[$n];
            if (null !== $regex) {
            //  Check if it matches the regex
                if (!preg_match('/'.$regex.'/', $v)) {
                    $v = $default;
                }
            }
        }
        else {
            $v = $default;
        }
        return $v;
    }   //  function get
    
    /**
     * post function.
     *
     * Get a POST variable
     * 
     * @access public
     * @static
     * @param mixed $n
     * @param mixed $default. (default: null)
     */
    public static function post($n, $default=null, $regex=null) {
        if (isset($_POST[$n])) {
            $v = $_POST[$n];
            if (null !== $regex) {
            //  Check if it matches the regex
                if (!preg_match('/'.$regex.'/', $v)) {
                    $v = $default;
                }
            }
        }
        else {
            $v = $default;
        }
        return $v;
    }   //  function post
    
    
    /**
     * is_dev function.
     *
     * Returns true if Whip runs in a dev environment
     * (set via $config['Whip']['dev'])
     * 
     * @access public
     * @static
     * @return void
     */
    public static function is_dev() {
        return (bool)(
            is_array(self::$_config) &&
            isset(self::$_config['dev']) &&
            true === self::$_config['dev']
        );
    }   //  function is_dev
    
        
}   //  class Whip
