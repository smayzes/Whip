<?php

/**
 * Query class.
 *
 * Query preparation and abstraction class
 * to be used in conjunction with the Db class
 *
 * Whip : A non-restrictive PHP framework
 * Copyright (c) 2010 Menno van Ens (codefocus.ca) and Shawn Mayzes (mayzes.org)
 * Released under the GNU General Public License, Version 3 
 *
 * @extends WhipPlugin
 */
class Query extends UncachedWhipPlugin {
//  Cross-instance
    protected static $_require = array('Db');       //  array: names of plugins required to run this plugin
    
//  Constants
    const LF = "\n";
    
    const REGEX_COLUMN = '/[A-Za-z0-9_\.]/';
    
    const WHERE_FIELD = 0;
    const WHERE_VALUE = 1;
    const WHERE_OPERATOR = 2;
    
    const PDO_PLACEHOLDER = '?';
    
    const ORDER_ASC = 'ASC';
    const ORDER_DESC = 'DESC';
    
    
//  Model properties
    private $_model_name = '';
    private $_table_name = '';
    
//  Raw query
    private $_is_raw_query = false;
    private $_raw_query = '';

//  Query properties
    private $_where_conditions = array();
    private $_where_values = array();
    private $_limit = 0;
    private $_offset = 0;
    private $_order_fields = array();
    private $_order_directions = array();//self::ORDER_ASC;
    
    /**
     * model function.
     *
     * Set the model we are querying
     * 
     * @access public
     * @param mixed $model
     * @return void
     */
    public function model($model) {
    //  Determine if $model contains a model name or instance
        if (is_string($model)) {
        //  Model name or raw query
            if (class_exists($model) && is_subclass_of($model, 'WhipModel')) {
            //  Model name
                $this->_model_name = $model;
                $this->_table_name = $model::$_table;
            }
            else {
            //  Not a valid model name
                throw new WhipModelException(E_MODEL_INVALID);
                return false;
            }
        }
        elseif ($model instanceof WhipModel) {
        //  Model object
            $this->_model_name = get_class($model);
            $this->_table_name = $model::$_table;
        }
        else {
        //  Unusable value passed to us
            throw new WhipModelException(E_MODEL_INVALID);
            return false;
        }
        return $this;
    }
    
    
    /**
     * get_instance function.
     *
     * Configure and initialize this plugin
     * 
     * @access public
     * @static
     * @param mixed array $config. (default: null)
     * @return WhipPlugin Current instance of this plugin
     */
    /*
    public static function get_instance($config=null) {
    //  When no config specified, always return new instance
        if(null != $config && count(self::$_instances)) {
        //  We have instances.
        //  Loop through instances to see if one of these
        //  should be returned instead of creating a new one.
            foreach(self::$_instances as $instance) {
                if ($instance instanceof WhipPlugin) {
                    if ($instance->get_config() == $config) {
                    //  Config matches entirely.
                    //  Return existing instance.
                        return $instance;
                    }
                }
            
            }   //  each instance
        }   //  if instances
    //  No qualifying instance found;
    //  Create and return a new instance
        $instance = new self($config);
        self::$_instances[] = $instance;
        return $instance;
    }   //  get_instance
    */
            
    
    /**
     * raw function.
     *
     * Set a raw SQL query to execute.
     * !! This query is NOT sanitized !!
     * 
     * @access public
     * @param mixed $sql
     * @return void
     */
    public function raw($sql) {
        $this->_is_raw_query = true;
        $this->raw_query = $sql;
        return $this;
    }   //  raw
    
    
    /**
     * Add a standard WHERE clause
     */
    public function where($column_name, $value, $operator='=') {
    //  Check if this condition already exists
        foreach($this->_where_conditions as $condition) {
            if (
                $condition[self::WHERE_FIELD] == (string)$column_name
             && $condition[self::WHERE_OPERATOR] == $operator) {
            //  WHERE clause exists.
            //  Modify existing one.
                $condition[self::WHERE_VALUE] == $value;
                return $this;
            }
        }
    //  WHERE clause does not exist yet.
    //  Add a new one.
        $this->_where_conditions[] = array(
            self::WHERE_FIELD => (string)$column_name,
            self::WHERE_VALUE => $value,
            self::WHERE_OPERATOR => $operator,
        );
        return $this;
    }
    
    /**
     * Add a WHERE ... LIKE clause
     */
    public function where_like($column_name, $value) {
        return $this->where($column_name, $value, 'LIKE');
    }
    /**
     * Add a WHERE ... NOT LIKE clause
     */
    public function where_not_like($column_name, $value) {
        return $this->where($column_name, $value, 'NOT LIKE');
    }
    /**
     * Add a WHERE ... > clause
     */
    public function where_gt($column_name, $value) {
        return $this->where($column_name, $value, '>');
    }
    /**
     * Add a WHERE ... < clause
     */
    public function where_lt($column_name, $value) {
        return $this->where($column_name, $value, '<');
    }
    /**
     * Add a WHERE ... >= clause
     */
    public function where_gte($column_name, $value) {
        return $this->where($column_name, $value, '>=');
    }
    /**
     * Add a WHERE ... <= clause
     */
    public function where_lte($column_name, $value) {
        return $this->where($column_name, $value, '<=');
    }
    
    /**
     * Add a WHERE ... IN clause
     */
    public function where_in($column_name, $values) {
        return $this->where($column_name, $values, 'IN');
    }
    /**
     * Add a WHERE ... NOT IN clause
     */
    public function where_not_in($column_name, $values) {
        return $this->where($column_name, $values, 'NOT IN');
    }
    
    /**
     * limit function.
     * 
     * @access public
     * @param mixed $limit
     */
    public function limit($limit) {
        $this->_limit = (int)$limit;
        return $this;
    }   //  limit
    
    /**
     * offset function.
     * 
     * @access public
     * @param mixed $offset
     */
    public function offset($offset) {
        $this->_offset = (int)$offset;
        return $this;
    }   //  offset
    
    /**
     * order function.
     * 
     * @access public
     * @param mixed $field
     * @param mixed $direction. (default: self::ORDER_ASC)
     */
    public function order($field, $direction=self::ORDER_ASC) {
        if ($direction==self::ORDER_ASC) {
        //  ASC
            $this->_order_fields[] = $this->_safe_name($field);
            $this->_order_directions[] = self::ORDER_ASC;
        }
        elseif ($direction==self::ORDER_DESC) {
        //  DESC
            $this->_order_fields[] = $this->_safe_name($field);
            $this->_order_directions[] = self::ORDER_DESC;
        }
        else {
        //  UNKNOWN
            throw new WhipDataException(E_DATA_INVALID_order_directions);
        }
        return $this;
    }   //  order
    
    /**
     * order_by function.
     * 
     * Alias for order
     */
    public function order_by($field, $direction=self::ORDER_ASC) {
        return $this->order($field, $direction);
    }   //  order_by
    
    
    
    /**
     * build_select function.
     *
     * Builds the SQL query for SELECT
     * 
     * @access public
     * @return void
     */
    public function build_select($model_name=null) {
    //  Check if we have a model name
        if ($model_name!=null) {
            $this->model($model_name);
        }
        if ($this->_table_name=='') {
            throw new WhipModelException(E_MODEL_INVALID);
            return false;
        }
    //  SELECT
        $sql =
            'SELECT *'.self::LF.
            'FROM '.$this->_safe_name($this->_table_name).self::LF;
    //  WHERE
        if (count($this->_where_conditions)) {
            $sql .= $this->_build_where().self::LF;
        }
    //  ORDER
        $num_order = count($this->_order_fields);
        if ($num_order) {
            
            $sql_order = array();
            for($i_field=0; $i_field<$num_order; ++$i_field) {
                $sql_order[] = $this->_order_fields[$i_field].' '.$this->_order_directions[$i_field];
            }
            $sql .= 'ORDER BY '.implode(', ',$sql_order).self::LF;
        }
        
        
    //  LIMIT / OFFSET
        if ($this->_limit) {
            $sql .= 'LIMIT '.((int)$this->_limit).self::LF;
        }
        if ($this->_offset) {
            $sql .= 'OFFSET '.((int)$this->_offset).self::LF;
        }
        return $sql;
    }   //  build_select
    
    
    /**
     * build_count function.
     *
     * Builds the SQL query for COUNT
     * 
     * @access public
     * @return void
     */
    public function build_count($model_name=null) {
    //  Check if we have a model name
        if ($model_name!=null) {
            $this->model($model_name);
        }
        if ($this->_table_name=='') {
            throw new WhipModelException(E_MODEL_INVALID);
            return false;
        }
    //  SELECT
        $sql =
            'SELECT COUNT(*)'.self::LF.
            'FROM '.$this->_safe_name($this->_table_name).self::LF;
    //  WHERE
        if (count($this->_where_conditions)) {
            $sql .= $this->_build_where().self::LF;
        }
    //  LIMIT / OFFSET
        if ($this->_limit) {
            $sql .= 'LIMIT '.((int)$this->_limit).self::LF;
        }
        if ($this->_offset) {
            $sql .= 'OFFSET '.((int)$this->_offset).self::LF;
        }
        return $sql;
    }   //  build_count
    
    
    
    
    /**
     * build_insert function.
     *
     * Builds the SQL query for INSERT
     * 
     * @access public
     * @return void
     */
    public function build_insert($model_name=null) {
    //  Check if we have a model name
        if ($model_name!=null) {
            $this->model($model_name);
        }
        if ($this->_table_name=='') {
            throw new WhipModelException(E_MODEL_INVALID);
            return false;
        }
    //  SELECT
        $sql =
            'SELECT COUNT(*)'.self::LF.
            'FROM '.$this->_safe_name($this->_table_name).self::LF;
    //  WHERE
        if (count($this->_where_conditions)) {
            $sql .= $this->_build_where().self::LF;
        }
    //  LIMIT / OFFSET
        if ($this->_limit) {
            $sql .= 'LIMIT '.((int)$this->_limit).self::LF;
        }
        if ($this->_offset) {
            $sql .= 'OFFSET '.((int)$this->_offset).self::LF;
        }
        return $sql;
    }   //  build_insert
    
    
    /**
     * build_update function.
     *
     * Builds the SQL query for UPDATE
     * 
     * @access public
     * @return void
     */
    public function build_update($model_name=null) {
    //  Check if we have a model name
        if ($model_name!=null) {
            $this->model($model_name);
        }
        if ($this->_table_name=='') {
            throw new WhipModelException(E_MODEL_INVALID);
            return false;
        }
    //  SELECT
        $sql =
            'SELECT COUNT(*)'.self::LF.
            'FROM '.$this->_safe_name($this->_table_name).self::LF;
    //  WHERE
        if (count($this->_where_conditions)) {
            $sql .= $this->_build_where().self::LF;
        }
    //  LIMIT / OFFSET
        if ($this->_limit) {
            $sql .= 'LIMIT '.((int)$this->_limit).self::LF;
        }
        if ($this->_offset) {
            $sql .= 'OFFSET '.((int)$this->_offset).self::LF;
        }
        return $sql;
    }   //  build_update
    
    
    
    
    
    /**
     * get_values function.
     *
     * returns values for PDO's bindParams
     * 
     * @access public
     */
    public function get_values() {
        return $this->_where_values;
    }   //  get_values
    
    
    /**
     * _build_where function.
     * 
     * @access protected
     * @return void
     */
    protected function _build_where() {
    //  Check if we have a WHERE clause
        if (!count($this->_where_conditions)) {
            return '';
        }
    //  Convert conditions to strings
        $where_conditions = array();    //  The WHERE clause parts as strings
        $where_values = array();        //  Used as bindParams by PDO
        foreach($this->_where_conditions as $condition) {
            if (is_array($condition[self::WHERE_VALUE])) {
            //  Value is an array of values
            //  Used for WHERE ... IN
                $num_values = count($condition[self::WHERE_VALUE]);
                $where_conditions[] =
                    $this->_safe_name($condition[self::WHERE_FIELD]).
                    $condition[self::WHERE_OPERATOR].
                    '('.implode(',', array_fill(0, $num_values, self::PDO_PLACEHOLDER)).')';
                foreach($condition[self::WHERE_VALUE] as $value) {
                    $where_values[] = $value;
                }
            }
            else {
            //  Value is a plain basic value
                $where_conditions[] =
                    $this->_safe_name($condition[self::WHERE_FIELD]).
                    $condition[self::WHERE_OPERATOR].
                    self::PDO_PLACEHOLDER;
                $where_values[] = $condition[self::WHERE_VALUE];
            }
        }   //  each WHERE condition
        
    //  Remember the WHERE values for when PDO asks about them
        $this->_where_values = $where_values;
        
    //  Glue the WHERE clause together
        $sql = 'WHERE '.implode(' AND ', $where_conditions);
        return $sql;
        
    }   //  _build_where
    
    
    

    /**
     * _safe_name function.
     * 
     * Quote a table name or column name
     *
     * @access protected
     * @param mixed $column_or_table_name
     * @return void
     */
    protected function _safe_name($column_or_table_name) {
    //  Check name
        if (!preg_match(self::REGEX_COLUMN, $column_or_table_name)) {
            throw new WhipDataException(E_DATA_INVALID_COLUMN_OR_TABLE_NAME);
            return false;
        }
    //  Enquote
        return '"'.$column_or_table_name.'"';
    }   //  _safe_name    
    
}   //  class Query

