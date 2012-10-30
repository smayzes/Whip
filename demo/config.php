<?php
/**
 * Whip template site
 * 
 * config.php
 *
 */

//  Display all errors
    error_reporting(E_ALL + E_STRICT);
    ini_set('display_errors', 'stdout');
    
    $config = array();
    
//  Are we working on dev?
//  Feel free to use your own criteria to determine is_dev.
    if (preg_match('/\/dev|demo|test$/', __DIR__)) {
        $config['is_dev'] = true;
    }
    else {
        $config['is_dev'] = false;
    }

//  Whip
    $config['Whip'] = array(
        'path'          => '/var/www/_codebase/menno/whip/',
    );
    
//  Site
    $config['Site'] = array(
        'path'          => realpath(__DIR__).'/',
    );
    
//  Db plugin
    $config['Db'] = array(
        'driver'        => 'pgsql',
        'host'          => 'localhost',
        'port'          => '5432',
        'dbname'        => 'whip',
        'schema'        => 'whip',
        'username'      => 'whip',
        'password'      => 'password',
    );
    
//  Template plugin
    $config['Template'] = array(
        'path'          => $config['Site']['path'].'templates/',
	//  Initialize a default context
	//  These variables are available inside the templates.
	    'context'		=> array(
	        'dev'   => $config['is_dev'],
	        'css'   => array(
	            '/css/whip.css',
	        ),
	        'js'    => array(
	            '//ajax.googleapis.com/ajax/libs/jquery/1.8.2/jquery.min.js',
	            '/js/whip.js',
	        ),
	    ),
    );
    
    