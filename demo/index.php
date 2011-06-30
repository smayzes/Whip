<?php    
/**
 * Whip template site
 * 
 * index.php
 *
 */

//  Configuration
    require_once('config.php');

//  Initialise Whip
    require_once($config['Whip']['path'].'whip.php');

//  Load database models
    require_once($config['Site']['path'].'models.php');
    
//  Initialize context
//  These variables are available inside the templates.
    $context = array(
        'dev'   => $config['is_dev'],
        'css'   => array(
            '/css/whip.css',
        ),
        'js'    => array(
            '/js/whip.js',
            'http://ajax.googleapis.com/ajax/libs/jquery/1.6.1/jquery.min.js',
        ),
    );
    
//  Parse request
    try {
    //  --------
    //  Your website routing / code goes here.
    //
    //  Variables added to the $context array
    //  will be available inside the template.
    //
    //  --------
        $template = 'index.tpl';
        
    }
    catch (Exception $e) {
    //  --------
    //  Handle exception here.
    //  --------
        echo '<pre>'.print_r($e, true).'</pre>';
        
    }
    
//  Render template
    Whip::Template()->render($template, $context);
    
    