<?php    
/**
 * Whip template site
 * 
 * index.php
 *
 */

//  Configuration
    require_once('config.php');

//  Whip
    require_once($config['Whip']['path'].'whip.php');

//  Model autoloader
    require_once($config['Site']['path'].'autoload_models.php');
    
//	Execute the request
//	-------------------
//	This is just an example of simple routing.
//	It is up to the developer to how they want to implement this.
    try {
    //  Parse the request
        if (!isset($_SERVER['REQUEST_URI'])) {
        //	Default uri
            $uri = 'home';
        }
        else {
        //	Trim slash from beginning and end
            $uri = trim($_SERVER['REQUEST_URI'], '/');
        }
        
        if (!strlen($uri)) {
	        $controller_name = 'home';
        }
        else {
            $uri_parts = explode('/', $uri);
            $controller_name = $uri_parts[0];
        }
        
    //  Load the controller
    //	and hand it the default context
    	try {
        $controller = WhipController::load($controller_name);
        $controller->context += $config['Template']['context'];
        }
        catch(Exception $e) {
	        
	        echo '<pre>'.print_r($e->getMessage(), true).'</pre>';
        }
        
    //  Render the controller's view
        if (!isset($uri_parts)) {
        //	Render the controller's default view
            $controller->render();
        }
        else {
            switch(count($uri_parts)) {
            case 2:
            //	Controller->function()
                $function_name = $uri_parts[1];
                if (
                	is_numeric($function_name) OR
                	!method_exists($controller, $function_name)
                ) {
                    $parameter = $uri_parts[1];
                    $controller->render($parameter);
                }
                else {
                	$controller->$function_name();
                }
                break;
            case 3:
            //	Controller->function($argument)
                $function_name = $uri_parts[1];
                $parameter = $uri_parts[2];
                if (!method_exists($controller, $function_name)) {
                    $controller->render($parameter);
                }
                $controller->$function_name($parameter);
                break;
            default:
            //	Controller->render();
                $controller->render();
                break;
            }
        }
    }
    catch (Exception $e) {
    //	An Exception occurred while:
    //	-	parsing the request
    //	-	calling the controller
    //	-	rendering the view
    //  
    //	Render the exception view
    //	
    	$context = $config['Template']['context'];
    	$context['exception'] = $e->getMessage();
    //	Remove site path from trace file(s)
    	$backtrace = $e->getTrace();
    	foreach($backtrace as &$trace) {
	    	$trace['file'] = str_replace($config['Site']['path'], '/', $trace['file']);
    	}
    	$context['backtrace'] = $backtrace;
        Whip::Template()->render('/errors/exception.tpl', $context);
    }
    
    