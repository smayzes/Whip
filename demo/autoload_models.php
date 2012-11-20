<?php
/**
 * Whip template site
 * 
 * Database model autoloader
 *
 */
	spl_autoload_register(
		function($classname) {
			global $config;
		//	Convert class name to file name
			$classname = strtolower($classname);
			$classname = preg_replace('/[^a-z0-9_]/', '', $classname);
			$class_filename	= $config['Site']['path'].'/models/'.$classname.'.php';
		//	Include model file if it exists
			if (file_exists($class_filename)) {
				require_once($class_filename);
			}
		}
	);
	