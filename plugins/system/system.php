<?php

/**
 * System plugin.
 *
 *
 * Whip : A non-restrictive PHP framework
 * Copyright (c) 2010 Menno van Ens (codefocus.ca) and Shawn Mayzes (mayzes.org)
 * Released under the GNU General Public License, Version 3 
 *
 * @extends WhipPlugin
 */
class System extends SingletonWhipPlugin {
    
    /**
     * loadavg function.
     * Returns the server's load average as a float
     * 
     * @access public
     * @return void
     */
    public function loadavg() {
    //  Attempt to get the server's load average
        $output     = @exec('cat /proc/loadavg');
        $matches    = array();
        $regex      =
            '/^'.
                '([0-9]{1,4}\.[0-9]{2}) '.  //   1 min
                '[0-9]{1,4}\.[0-9]{2} '.    //   5 min
                '[0-9]{1,4}\.[0-9]{2} '.    //  15 min
                '[0-9]{1,6}\/[0-9]{1,6} '.  //  processes
                '[0-9]{1,9}'.               //  last procid
            '$/s';
        if (preg_match($regex, $output, $matches)) {
            return (float)$matches[1];
        }
        throw new WhipPluginException('Operation not supported on this server.');
        return false;
    }   //  function loadavg
    
    
    
    
}   //  class System

