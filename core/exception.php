<?php
/**
 * Exception classes.
 * 
 * Whip : A non-restrictive PHP framework
 * Copyright (c) 2010 Menno van Ens (codefocus.ca) and Shawn Mayzes (mayzes.org)
 * Released under the GNU General Public License, Version 3
 * 
 */
    
    
//  Model errors
    define('E_MODEL_INVALID',                       'WhipModel class expected');
    define('E_MODEL_FIELD_NOT_FOUND',               'Field not found');
    define('E_MODEL_VALUE_NOT_FOUND',               'Value not found');
    
    
//  Plugin errors
    define('E_PLUGIN_INVALID',                      'WhipPlugin class expected');
    define('E_PLUGIN_INVALID_NAME',                 'Invalid plugin name');
    
//  Configuration errors
    define('E_CONFIG_MISSING_VALUE',                'Missing config value: ');
    define('E_CONFIG_INCORRECT_VALUE',              'Incorrect config value: ');
    
//  Data exceptions
    define('E_DATA_INVALID_COLUMN_OR_TABLE_NAME',   'Invalid column or table name');
    define('E_DATA_MORE_THAN_ONE_RESULT',           'More than one result returned');
    define('E_DATA_INVALID_ORDER_DIRECTION',        'Order direction should be ASC or DESC');
    
    
    
    class WhipException         extends Exception { }
    
    class WhipModelException    extends WhipException {
        public function __construct($message = null, $code = 0) {
            if (!$message) {
                throw new $this('Whip Model Exception: '. get_class($this));
            }
            parent::__construct($message, $code);
        }
    }
    
    class WhipPluginException   extends WhipException {
        public function __construct($message = null, $code = 0) {
            if (!$message) {
                throw new $this('Whip Plugin Exception: '. get_class($this));
            }
            parent::__construct($message, $code);
        }
    }
    
    class WhipConfigException   extends WhipException {
        public function __construct($message = null, $code = 0) {
            if (!$message) {
                throw new $this('Whip Config Exception: '. get_class($this));
            }
            parent::__construct($message, $code);
        }
    }
    
    class WhipDataException     extends WhipException {
        public function __construct($message = null, $code = 0) {
            if (!$message) {
                throw new $this('Whip Data Exception: '. get_class($this));
            }
            parent::__construct($message, $code);
        }
    }
    