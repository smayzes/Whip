<?php

/**
 * HTML class.
 *
 * Whip : A non-restrictive PHP framework
 * Copyright (c) 2010 Menno van Ens (codefocus.ca) and Shawn Mayzes (mayzes.org)
 * Released under the GNU General Public License, Version 3
 * 
 * @extends WhipPlugin
 */
 
class Html extends WhipPlugin {
    
    public function clean($html) {
    	return strip_tags($html);
    }
}   //  class Html
