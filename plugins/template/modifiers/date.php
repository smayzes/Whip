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
    public static function format($value, $format, $format2=null, $format3=null, $format4=null, $format5=null, $format6=null, $format7=null, $format8=null) {
    //  Format date if necessary
        if (!is_numeric($value) && !($value instanceof DateTime)) {
            $value = strtotime($value);
        }
        if (false === $value) {
            throw new WhipPluginException('Unexpected date format: '.$value);
            return false;
        }
        
        $date_format = $format;
        if (null !== $format2) {
        	$date_format .= ' '.$format2;
	        if (null !== $format3) {
	        	$date_format .= ' '.$format3;
		        if (null !== $format4) {
		        	$date_format .= ' '.$format4;
			        if (null !== $format5) {
			        	$date_format .= ' '.$format5;
				        if (null !== $format6) {
				        	$date_format .= ' '.$format6;
					        if (null !== $format7) {
					        	$date_format .= ' '.$format7;
						        if (null !== $format8) {
						        	$date_format .= ' '.$format8;
						        }
					        }
				        }
			        }
		        }
	        }
        }
    //  Return formatted date
    	if ($value instanceof DateTime) {
    		$return = (string)$value->format($date_format);
    		return $return;
    	}
    	else {
	    	return date($date_format, $value);
    	}
        
    }   //  function format
    
    
    /**
     * friendly function.
     * 
     * @access public
     * @static
     * @param mixed $value
     */
    public static function friendly($value, $language='en') {
    //  Translations
        static $i18n = array(
            'en'    => array(
                'in the future' => 'in the future',
                'now'           => 'now',
                'never'         => 'never',
                'yesterday'     => 'yesterday',
                'a'             => 'a',
                'an'            => 'an',
                'last'          => 'last',
                'ago'           => 'ago',
                'second'        => 'second',
                'seconds'       => 'seconds',
                'minute'        => 'minute',
                'minutes'       => 'minutes',
                'hour'          => 'hour',
                'hours'         => 'hours',
                'day'           => 'day',
                'days'          => 'days',
                'week'          => 'week',
                'weeks'         => 'weeks',
                'month'         => 'month',
                'months'        => 'months',
                'year'          => 'year',
                'years'         => 'years',
            ),
            'nl'    => array(
                'in the future' => 'in de toekomst',
                'now'           => 'nu',
                'never'         => 'nooit',
                'yesterday'     => 'gisteren',
                'a'             => 'een',
                'an'            => 'een',
                'last'          => 'vorig',
                'ago'           => 'geleden',
                'second'        => 'seconde',
                'seconds'       => 'seconden',
                'minute'        => 'minuut',
                'minutes'       => 'minuten',
                'hour'          => 'uur',
                'hours'         => 'uur',
                'day'           => 'dag',
                'days'          => 'dagen',
                'week'          => 'week',
                'weeks'         => 'weken',
                'month'         => 'maand',
                'months'        => 'maanden',
                'year'          => 'jaar',
                'years'         => 'jaar',
            ),
            
        );
        
    //  Format date if necessary
        if (!is_numeric($value)) {
            $value = strtotime($value);
        }
        if (false === $value) {
        //  0000-00-00 00:00:00 is Never
            return $i18n[$language]['never'];
        }
    //  Calculate the difference
        $time_now = time();
        $difference = $time_now - $value;
        if ($difference < 0) {
        //  Future
            return $i18n[$language]['in the future'];
        }
        elseif ($difference < 10*self::SECOND) {
        //  Now
            return $i18n[$language]['now'];
        }
        elseif ($difference < self::MINUTE) {
        //  x seconds
            if (1===$difference) {
                return $i18n[$language]['a'].' '.$i18n[$language]['second'].' '.$i18n[$language]['ago'];
            }
            else {
                return $difference.' '.$i18n[$language]['seconds'].' '.$i18n[$language]['ago'];
            }
        }
        elseif ($difference < 2*self::MINUTE) {
        //  1 minute
            return $i18n[$language]['a'].' '.$i18n[$language]['minute'].' '.$i18n[$language]['ago'];
        }
        elseif ($difference < 45*self::MINUTE) {
        //  x minutes
            return ceil($difference/self::MINUTE).' '.$i18n[$language]['minutes'].' '.$i18n[$language]['ago'];
        }
        elseif ($difference < 90*self::MINUTE) {
        //  1 hour
            return $i18n[$language]['an'].' '.$i18n[$language]['hour'].' '.$i18n[$language]['ago'];
        }
        elseif ($difference < 24*self::HOUR) {
        //  x hours
            return ceil($difference/self::HOUR).' '.$i18n[$language]['hours'].' '.$i18n[$language]['ago'];
        }
        elseif ($difference < 48*self::HOUR) {
        //  yesterday
            return $i18n[$language]['yesterday'];
        }
        elseif ($difference < 14*self::DAY) {
        //  x days
            return ceil($difference/self::DAY).' '.$i18n[$language]['days'].' '.$i18n[$language]['ago'];
        }
        elseif ($difference < self::MONTH*2) {
        //  x weeks
            return ceil($difference/self::WEEK).' '.$i18n[$language]['weeks'].' '.$i18n[$language]['ago'];
        }
        elseif ($difference < self::YEAR) {
        //  x months
            return ceil($difference/self::MONTH).' '.$i18n[$language]['months'].' '.$i18n[$language]['ago'];
        }
        elseif ($difference < 2*self::YEAR) {
        //  last year
            return $i18n[$language]['last'].' '.$i18n[$language]['year'];
        }
        else {
            return ceil($difference/self::YEAR).' '.$i18n[$language]['years'].' '.$i18n[$language]['ago'];
        }
    }   //  function friendly
    
}   //  Date
