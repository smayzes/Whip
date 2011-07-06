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
    public static function friendly($value, $language='en') {
    //  Translations
        static $i18n = array(
            'en'    => array(
                'in the future' => 'in the future',
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
            throw new WhipPluginException('Unexpected date format: '.$value);
            return false;
        }
    //  Calculate the difference
        $time_now = time();
        $difference = $time_now - $value;
        if ($difference < 0) {
        //  Future
            return $i18n[$language]['in the future'];
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


