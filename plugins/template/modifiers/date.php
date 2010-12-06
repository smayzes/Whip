<?php


/**
 * TemplateModifier class.
 * 
 */
class TemplateModifierDate extends TemplateModifier {
    
    const SECOND    = 1;
    const MINUTE    = 60;       //  60 seconds
    const HOUR      = 3600;     //  60 minutes
    const DAY       = 86400;    //  24 hours
    const WEEK      = 604800;   //   7 days
    const MONTH     = 2592000;  //  30 days
    const YEAR      = 31536000; //  365 days

    
    
    /**
     * run function.
     * 
     * @access public
     * @static
     * @param mixed $value
     */
    public static function run($value) {
        return $value;
    }   //  function run
    
    
    /**
     * format function.
     * 
     * @access public
     * @static
     * @param mixed $value
     * @param mixed $format
     */
    public static function format($value, $format) {
    //  Format date if necessary
        if (!is_numeric($value)) {
            $value = strtotime($value);
        }
        if (false === $value) {
            throw new WhipPluginException('Unexpected date format: '.$value);
            return false;
        }
    //  Return formatted date
        return date($format, $value);
    }   //  function format
    
    
    /**
     * friendly function.
     * 
     * @access public
     * @static
     * @param mixed $value
     */
    public static function friendly($value) {
    //  Format date if necessary
        if (!is_numeric($value)) {
            $value = strtotime($value);
        }
        if (false === $value) {
            throw new WhipPluginException('Unexpected date format: '.$value);
            return false;
        }
    //  Calculate the difference
        $time_now = time();
        $difference = $time_now - $value;
        if ($difference < 0) {
        //  Future
            return 'in the future';
        }
        elseif ($difference < self::MINUTE) {
        //  x seconds
            if (1===$difference) {
                return 'a second ago';
            }
            else {
                return $difference.' seconds ago';
            }
        }
        elseif ($difference < 2*self::MINUTE) {
        //  1 minute
            return 'a minute ago';
        }
        elseif ($difference < 45*self::MINUTE) {
        //  x minutes
            return ceil($difference/self::MINUTE).' minutes ago';
        }
        elseif ($difference < 90*self::MINUTE) {
        //  1 hour
            return 'an hour ago';
        }
        elseif ($difference < 24*self::HOUR) {
        //  x hours
            return ceil($difference/self::HOUR).' hours ago';
        }
        elseif ($difference < 48*self::HOUR) {
        //  yesterday
            return 'yesterday';
        }
        elseif ($difference < 14*self::DAY) {
        //  x days
            return ceil($difference/self::DAY).' days ago';
        }
        elseif ($difference < self::MONTH) {
        //  x weeks
            return ceil($difference/self::WEEK).' weeks ago';
        }
        elseif ($difference < self::YEAR) {
        //  x months
            return ceil($difference/self::MONTH).' months ago';
        }
        elseif ($difference < 2*self::YEAR) {
        //  last year
            return 'last year';
        }
        else {
            return ceil($difference/self::YEAR).' years ago';
        }
    }   //  function friendly

    
    
}   //  Date


