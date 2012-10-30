<?php
/**
 * WhipController base class.
 * 
 * Whip : A non-restrictive PHP framework
 * Copyright (c) 2010 Menno van Ens (codefocus.ca) and Shawn Mayzes (mayzes.org)
 * Released under the GNU General Public License, Version 3
 *
 */
	abstract class WhipController {
		public $context				= array();
		
		protected $_template		= 'index.tpl';
		protected $_require_level	= 0;			//	Minimum Role->level required to access this Controller.
													//	Anonymous is 0, User is 16, Administrator is 256
		
		/**
		 * __construct function.
		 * 
		 * @access public
		 * @return void
		 */
		public function __construct() {
		//	Check if the user needs to be logged in
			if (0 == $this->_require_level) {
			//	No need to log in.
			//	Anyone can access this Controller.
				return;
			}
		//	A logged in user is required
		//	to access this Controller.
			if (!Whip::Session()->is_logged_in) {
			//	Redirect to the login page
				Whip::Http()->redirect('/login');
			}
		//	Check if user has the rights to access this page
			if (Whip::Session()->user->level < $this->_require_level) {
			//	User does not have sufficient privileges.
			//	Redirect to the login page.
				Whip::Http()->redirect('/login');
			}
		}	//	constructor
		
		/**
		 * render function.
		 * 
		 * @access public
		 * @param mixed $param. (default: null)
		 * @return void
		 */
		public function render($param=null) {
		//	Default: Render the template
			Whip::Template()->render($this->_template, $this->context);
		}	//	function render
		
		
		protected function exit404() {
			global $config;
			header('Status: 404 Not Found');
			Whip::Template()->render('/errors/404.tpl', $this->context);
			exit();
		}	//	function exit404
		
		/**
		 * load function.
		 * Returns a Controller class for the specified name.
		 * 
		 * @access public
		 * @static
		 * @return void
		 */
		public static function load($name=null, $context=array()) {
			global $config;
		//	Safety check
			if (!preg_match('/^[a-z_\/]+$/i', $name)) {
				$name = 'home';
			}
			$controller_path = $config['Site']['path'].'controllers/';
			
		//	Merge $context with default context
			$context = array_merge_recursive($config['Template']['context'], $context);
			
			if (!file_exists($controller_path.$name.'.php')) {
			//	Exit 404
				Whip::Template()->render('/errors/404.tpl', $context);
				exit();
			}
			require_once($controller_path.$name.'.php');
			$class_name = $name.'Controller';
			if (class_exists($class_name)) {
				return new $class_name;
			}
			throw new WhipException('Could not load Controller: '.$name);
		}	//	function load
		
	}	//	Controller
	