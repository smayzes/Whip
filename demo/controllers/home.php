<?php
/**
 * Whip template site
 * 
 * Home Controller
 *
 */
    class homeController extends WhipController {
        protected $_template        = '/index.tpl';
        
        /**
         * render function.
         * 
         * @access public
         * @param mixed $param. (default: null)
         * @return void
         */
        public function render($param=null) {
		//	Load unique assets
			//$this->context['css'][]	= '/css/home.css';
			
        //  Render template
            Whip::Template()->render($this->_template, $this->context);
        
        }   //  function render        
                
    }   //  Controller
    
    