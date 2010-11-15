<?php

/**
 * Db class.
 *
 * Database class and mini-ORM
 *
 * Whip : A non-restrictive PHP framework
 * Copyright (c) 2010 Menno van Ens (codefocus.ca) and Shawn Mayzes (mayzes.org)
 * Released under the GNU General Public License, Version 3
 * 
 * @extends WhipPlugin
 */
class Db extends WhipPlugin {
//  Cross-instance
    protected static $_require = array();           //  array: names of plugins required to run this plugin
    
//  Per instance
    protected $_link = null;                        //  Database connection
    
    
    /**
     * __destruct function.
     *
     * Clean up
     * 
     * @access public
     * @return void
     */
    public function __destruct() {
        unset($this->_link);
    }   //  destruct
    
    
    /**
     * _connect function.
     * 
     * @access private
     * @return void
     */
    private function _connect() {
    //  Check if we are connected already
        if ($this->_link) return true;
    //  Check if all required config values are present
        if (!isset($this->_config['driver'])) {
            throw new WhipConfigException(E_CONFIG_MISSING_VALUE.'driver');
            return false;
        }
        elseif(!in_array($this->_config['driver'], array('mysql', 'pgsql', 'sqlite'))) {
            throw new WhipConfigException(E_CONFIG_INCORRECT_VALUE.'driver');
            return false;
        }
        if (!isset($this->_config['host'])) {
            throw new WhipConfigException(E_CONFIG_MISSING_VALUE.'host');
            return false;
        }
        if (!isset($this->_config['port'])) {
            throw new WhipConfigException(E_CONFIG_MISSING_VALUE.'port');
            return false;
        }
        if (!isset($this->_config['dbname'])) {
            throw new WhipConfigException(E_CONFIG_MISSING_VALUE.'dbname');
            return false;
        }
        if (!isset($this->_config['username'])) {
            throw new WhipConfigException(E_CONFIG_MISSING_VALUE.'username');
            return false;
        }
        if (!isset($this->_config['password'])) {
            throw new WhipConfigException(E_CONFIG_MISSING_VALUE.'password');
            return false;
        }
    //  Connect
        $dsn = $this->_config['driver'].
            ':host='.$this->_config['host'].
            ';port='.$this->_config['port'].
            ';dbname='.$this->_config['dbname'];
        $this->_link = new PDO($dsn, $this->_config['username'], $this->_config['password']);
        $this->_link->setAttribute(
            PDO::ATTR_ERRMODE,
            PDO::ERRMODE_EXCEPTION
        );
        
    //  If schema is set, select it
        if (isset($this->_config['schema'])) {
            $this->_link->exec(
                'SET search_path TO '.$this->_config['schema']
            );
        }
        return true;
    }   //  _connect
    
    
    
    /**
     * get_one function.
     *
     * Returns one model instance if the query yields one result.
     * 
     * @access public
     * @param mixed $model_name
     * @param mixed $query
     * @return void
     */
    public function get_one($model_name, $query) {
    //  Make sure the model exists
        $results = $this->get_all($model_name, $query);
        if (is_array($results)) {
            $num_results = count($results);
            if ($num_results==1) {
            //  One result exactly. Return it.
                return array_pop($results);
            }
            elseif ($num_results>1) {
            //  More than one result!
            //  Query error.
                throw new WhipException(E_DATA_MORE_THAN_ONE_RESULT);
                return false;
            }
        }
        return $results;
    }   //  function get_one



    /**
     * get_one function.
     *
     * Returns one model instance if the query yields one result.
     * 
     * @access public
     * @param mixed $model_name
     * @param mixed $query
     * @return void
     */
    public function get_all($model_name, $query) {
    //  Make sure the model exists
        try {
            Whip::model($model_name);
        }
        catch(Exception $e) {
            throw $e;
            return false;
        }
    //  Make sure we are connected
        if (!$this->_connect()) {
        //@TODO: Throw Exception!
            return false;
        }
    //  ...
        if ($query instanceof WhipPlugin && $query instanceof Query) {
        //  We have been passed a query class.
        //  Prepare and execute the query.
            $query_string = $query->build_select($model_name);
            $query_values = $query->get_values();
        //  Prepare SQL statement
            $pdo_statement = $this->_link->prepare($query_string);
        //  Bind values
            /*
            $num_values = count($query_values);
            for($idx_value=0; $idx_value<$num_values; ++$idx_value) {
                $pdo_statement->bindValue($idx_value+1, $query_values[$idx_value]);
            }
            */
        //  Execute SQL statement
            try {
                $pdo_statement->execute( $query_values );
                $pdo_statement->setFetchMode(PDO::FETCH_CLASS, $model_name);
                $data = $pdo_statement->fetchAll();
            }
            catch(Exception $e) {
                throw $e;
                return false;
            }
        }
        else {
        //  We have been passed a raw query string.
        //  Execute the query straight up.
            try {
                $pdo_statement = $this->_link->query($query, PDO::FETCH_CLASS, $model_name);
                $pdo_statement->execute();
                $pdo_statement->setFetchMode(PDO::FETCH_CLASS, $model_name);
                $data = $pdo_statement->fetchAll();
            }
            catch(Exception $e) {
                throw $e;
                return false;
            }
            //  TODO: Fetch data and stmt->closeCursor
            
        }
        
    //  Return data (WhipModels)
        //if (isset($data) && is_array($data) && count($data)) {
        if (isset($data) && is_array($data)) {
            return $data;
        }
        echo $query_string;
        return false;
    }   //  function get_all    
    
    

    /**
     * connect function.
     *
     * Manually connect to the database
     * This should not be necessary in normal circumstances
     * 
     * @access public
     * @return void
     */
    public function connect() {
        //  test
        $this->_connect();
    }
    
    
    
    
    
    
}   //  Db
