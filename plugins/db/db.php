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
//	Cross-instance
	protected static $_require = array();			//	array: names of plugins required to run this plugin
	
//	Per instance
	protected $_link = null;						//	Database connection
	
	
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
	}	//	destruct
	
	
	/**
	 * _connect function.
	 * 
	 * @access private
	 * @return void
	 */
	private function _connect() {
	//	Check if we are connected already
		if ($this->_link) return true;
	//	Check if all required config values are present
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
		$options = array();
		if ('mysql' === $this->_config['driver']) {
			$options[PDO::MYSQL_ATTR_INIT_COMMAND] = "SET NAMES utf8";
		}
		
	//	Connect
		$dsn = $this->_config['driver'].
			':host='.$this->_config['host'].
			';port='.$this->_config['port'].
			';dbname='.$this->_config['dbname'];
		$this->_link = new PDO(
			$dsn,
			$this->_config['username'],
			$this->_config['password'],
			$options);
		$this->_link->setAttribute(
			PDO::ATTR_ERRMODE,
			PDO::ERRMODE_EXCEPTION
		);
		
	//	If schema is set, select it
		if (isset($this->_config['schema'])) {
			$this->_link->exec(
				'SET search_path TO '.$this->_config['schema']
			);
		}
		return true;
	}	//	_connect
	
	
/*
	Rudimentary transaction implementation
	@TODO: Lots of work to be done here
*/
	
	public function begin() {
	//	Make sure we are connected
		if (!$this->_connect()) {
		//@TODO: Throw Exception!
			return false;
		}
		return $this->_link->beginTransaction();
	}	//	function begin
	
	
	public function commit() {
	//	Make sure we are connected
		if (!$this->_connect()) {
		//@TODO: Throw Exception!
			return false;
		}
		return $this->_link->commit();
	}	//	function commit
	
	public function rollback() {
	//	Make sure we are connected
		if (!$this->_connect()) {
		//@TODO: Throw Exception!
			return false;
		}
		return $this->_link->rollBack();
	}	//	function rollback
	
	
	/**
	 * get_one function.
	 * Returns exactly one model instance if the query yields one result.
	 * 
	 * @access public
	 * @param mixed $model_name
	 * @param mixed $query
	 * @param mixed array $params. (default: null)
	 * @return void
	 */
	public function get_one($model_name, $query, array $params=null) {
	//	Make sure the model exists
		$results = $this->get_all($model_name, $query, $params);
		if (is_array($results)) {
			$num_results = count($results);
			if ($num_results==1) {
			//	One result exactly. Return it.
				return array_pop($results);
			}
			elseif ($num_results>1) {
			//	More than one result!
			//	Query error.
				throw new WhipException(E_DATA_MORE_THAN_ONE_RESULT);
				return false;
			}
		}
		return $results;
	}	//	function get_one
	
	
	/**
	 * get_count function.
	 *
	 * Returns the number of rows that match a query.
	 * 
	 * @access public
	 * @param mixed $model_name
	 * @param mixed $query
	 * @return int
	 */
	public function get_count($model_name, Query $query=null) {
	//	Make sure the model exists
		try {
			Whip::model($model_name);
		}
		catch(Exception $e) {
			throw $e;
			return false;
		}
	//	Make sure we are connected
		if (!$this->_connect()) {
		//@TODO: Throw Exception!
			return false;
		}
	//	If the query is null, count ALL rows
		if (null === $query) {
			$query_string = 'SELECT COUNT(*) FROM '.$model_name::$_table;
			$query_values = null;
		}
		else {
		//	Prepare and execute the query.
			$query_string = $query->build_count($model_name);
			$query_values = $query->get_values();
		
		}
		
	//	Prepare SQL statement
		$pdo_statement = $this->_link->prepare($query_string);
	//	Execute SQL statement
		try {
			$pdo_statement->execute( $query_values );
			$pdo_statement->setFetchMode(PDO::FETCH_NUM);
			$data = $pdo_statement->fetchColumn(0);
		}
		catch(Exception $e) {
			throw $e;
			return false;
		}
		return $data;
	}	//	function get_count
	
	
	/**
	 * get_field function.
	 *
	 * Returns one field.
	 * 
	 * @access public
	 * @param string $query
	 */
	public function get_field($query, array $params=null) {
	//	Make sure we are connected
		if (!$this->_connect()) {
		//@TODO: Throw Exception!
			return false;
		}
	//	...
		if (!is_string($query)) {
		//	We have not been passed a string.
			//@TODO: Throw exception!
			return false;
		}
	//	We have been passed a raw query string.
	//	Execute the query straight up.
		try {
			if (null!==$params) {
				$pdo_statement = $this->_link->prepare($query);
				$pdo_statement->execute($params);
			}
			else {
				$pdo_statement = $this->_link->query($query, PDO::FETCH_NUM);
			}
			$pdo_statement->setFetchMode(PDO::FETCH_NUM);
			$data = $pdo_statement->fetch();
		}
		catch(Exception $e) {
			if (Whip::is_dev()) {
			//	In a development environment,
			//	show the query that caused the exception.
				throw new WhipPluginException(
					$e->getMessage()."\r\nQuery:\r\n".$query."\r\n"
				);
			}
			else {
				throw $e;
			}
			return false;
		}
	//	Return data
		if (isset($data)) {
			if (count($data)==1) {
				return $data[0];
			}
			return $data;
		}
		return false;
	}	//	function get_field
	
	
	/**
	 * get_fields function.
	 * Alias for get_raw
	 * 
	 * @access public
	 * @param mixed $query
	 * @param mixed array $params. (default: null)
	 * @return void
	 */
	public function get_fields($query, array $params=null) {
		return $this->get_raw($query, $params);
	}	//	function get_fields
	
	
	/**
	 * get_raw function.
	 *
	 * Returns raw query result.
	 * 
	 * @access public
	 * @param string $query
	 */
	public function get_raw($query, array $params=null) {
	//	Make sure we are connected
		if (!$this->_connect()) {
		//@TODO: Throw Exception!
			return false;
		}
	//	...
		if (!is_string($query)) {
		//	We have not been passed a string.
			//@TODO: Throw exception!
			return false;
		}
	//	We have been passed a raw query string.
	//	Execute the query straight up.
		try {
			if (null!==$params) {
				$pdo_statement = $this->_link->prepare($query);
				$pdo_statement->execute($params);
			}
			else {
				$pdo_statement = $this->_link->query($query, PDO::FETCH_NUM);
			}
			$pdo_statement->setFetchMode(PDO::FETCH_NUM);
			$data = $pdo_statement->fetchAll();
		}
		catch(Exception $e) {
			if (Whip::is_dev()) {
			//	In a development environment,
			//	show the query that caused the exception.
				throw new WhipPluginException(
					$e->getMessage()."\r\nQuery:\r\n".$query."\r\n"
				);
			}
			else {
				throw $e;
			}
			return false;
		}
	//	Return data
		if (isset($data)) {
			return $data;
		}
		return false;
	}	//	function get_raw
	
	
	/**
	 * execute function.
	 *
	 * Executes a query.
	 * 
	 * @access public
	 * @param string $query
	 */
	public function execute($query, array $params=null) {
	//	Make sure we are connected
		if (!$this->_connect()) {
		//@TODO: Throw Exception!
			return false;
		}
	//	...
		if (!is_string($query)) {
		//	We have not been passed a string.
			//@TODO: Throw exception!
			return false;
		}
	//	We have been passed a raw query string.
	//	Execute the query straight up.
		try {
			if (null!==$params) {
				$pdo_statement = $this->_link->prepare($query);
				$pdo_statement->execute($params);
			}
			else {
				$pdo_statement = $this->_link->exec($query);
			}
			
		}
		catch(Exception $e) {
			throw $e;
			return false;
		}
	}	//	function execute



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
	public function get_all($model_name, $query=null, array $params=null) {
	//	Make sure the model exists
		try {
			Whip::model($model_name);
		}
		catch(Exception $e) {
			throw $e;
			return false;
		}
	//	Make sure we are connected
		if (!$this->_connect()) {
		//@TODO: Throw Exception!
			throw new WhipPluginException('Could not connect to the database');
			return false;
		}
	//	Have we been passed:
	//	- a Query object
	//	- raw sql
	//	- or a primary key value?
		if ($query instanceof WhipPlugin && $query instanceof Query) {
		//	We have been passed a query class.
		//	Prepare and execute the query.
			$query_string = $query->build_select($model_name);
			//echo $query_string;
			$query_values = $query->get_values();
		//	Prepare SQL statement
			$pdo_statement = $this->_link->prepare($query_string);
		//	Execute SQL statement
			try {
				$pdo_statement->execute( $query_values );
				$pdo_statement->setFetchMode(PDO::FETCH_CLASS, $model_name);
				$data = $pdo_statement->fetchAll();
				foreach($data as $model) {
				//	Mark model as clean
					$model->mark_all_clean();
				}
			}
			catch(Exception $e) {
				throw $e;
				return false;
			}
		}
		elseif (is_numeric($query)) {
		//	We have been passed a primary key value.
		//	Retrieve a model instance by its primary key.
			$query_string =
				'SELECT *'.
				' FROM '.$model_name::$_table.
				' WHERE '.$model_name::$_pk.'='.((int)$query).
				' LIMIT 1';
			try {
				$pdo_statement = $this->_link->query($query_string, PDO::FETCH_CLASS, $model_name);
				if (false === $pdo_statement) {
					return false;
				}
				$pdo_statement->setFetchMode(PDO::FETCH_CLASS, $model_name);
				$data = $pdo_statement->fetchAll();
				foreach($data as $model) {
				//	Mark model as clean
					$model->mark_all_clean();
				}
			}
			catch(Exception $e) {
				throw $e;
				return false;
			}
		}
		elseif(null===$query) {
		//	We have not been passed any type of query.
		//	Retrieve ALL model instances from database.
			$query_string = 'SELECT * FROM '.$model_name::$_table;
			try {
				$pdo_statement = $this->_link->query($query_string, PDO::FETCH_CLASS, $model_name);
				if (false === $pdo_statement) {
					return false;
				}
				$pdo_statement->setFetchMode(PDO::FETCH_CLASS, $model_name);
				$data = $pdo_statement->fetchAll();
				foreach($data as $model) {
				//	Mark model as clean
					$model->mark_all_clean();
				}
			}
			catch(Exception $e) {
				throw $e;
				return false;
			}
		}
		else {
		//	We have been passed a raw query string.
		//	Execute the query straight up.
			try {

				$pdo_statement = $this->_link->prepare($query);
				if (false === $pdo_statement) {
					$error_code = $this->_link->errorCode();
					if ('00000' === $error_code) {
						throw new WhipPluginException('PDO cannot create model: '.$model_name);
					}
					else {
						$errorInfo = $this->_link->errorInfo();
						throw new WhipPluginException('PDO Error: '.$errorInfo[2]);
					}
					return false;
				}
				$pdo_statement->execute($params);
				$pdo_statement->setFetchMode(PDO::FETCH_CLASS, $model_name);
				$data = $pdo_statement->fetchAll();
			}
			catch(Exception $e) {
				if (Whip::is_dev()) {
				//	In a development environment,
				//	show the query that caused the exception.
					throw new WhipPluginException(
						$e->getMessage()."\r\nQuery:\r\n".$query."\r\n"
					);
				}
				else {
					throw $e;
				}
			}
			//	TODO: Fetch data and stmt->closeCursor
		}
	//	Return data (WhipModels)
		if (isset($data) && is_array($data)) {
			return $data;
		}
		return false;
	}	//	function get_all
	
	

	/**
	 * connect function.
	 *
	 * Manually connect to the database
	 * This should not be necessary in normal circumstances
	 * 
	 * @access public
	 */
	public function connect() {
		$this->_connect();
	}	//	function connect
	
	
	
	
	/**
	 * save function.
	 * 
	 * @access public
	 * @param mixed WhipModel $model
	 */
	public function save(WhipModel &$model) {
	//	Make sure we are connected
		if (!$this->_connect()) {
		//@TODO: Throw Exception!
			return false;
		}
	//	Initialize query
		$query = Whip::Query();
	//	Prepare and execute the query.
		$is_insert = false;
		$pk = $model::get_pk();
		if (is_numeric($model->$pk) && $model->$pk > 0) {
		//	Update
			$query_string = $query->build_update($model);
			if (false===$query_string) {
			//	No fields to update?
				return false;
			}
		}
		else {
		//	Insert
			$query_string = $query->build_insert($model);
			if (false===$query_string) {
			//	No fields to update?
				return false;
			}
			if ($this->_config['driver'] == 'pgsql') {
			//	Postgres:	RETURNING
				$query_string .= ' RETURNING '.$model::$_pk;
			}
			$is_insert = true;
		}
	//	Prepare SQL statement
		$query_values = $query->get_values();
		$pdo_statement = $this->_link->prepare($query_string);

	//	Execute SQL statement
		try {
			$pdo_statement->execute( $query_values );
			if ($is_insert) {
			//	Get new primary key value
				if ($this->_config['driver'] == 'pgsql') {
				//	Postgres:	RETURNING
					$model->{$model::$_pk} = $pdo_statement->fetchColumn();
				}
				else {
					$model->{$model::$_pk} = $this->_link->lastInsertId();
				}
			}
		}
		catch(Exception $e) {
			throw $e;
			return false;
		}
	//	Success!
	//	Mark model as clean
		$model->mark_all_clean();
		return true;
	}	//	function save
	
	
	/**
	 * escape function.
	 * 
	 * @access public
	 * @param mixed $value
	 * @return void
	 */
	public function escape($value) {
		if (!$this->_connect()) {
		//@TODO: Throw Exception!
			return false;
		}
		return $this->_link->quote($value);
	}	//	function escape
	
	
	/**
	 * quote function.
	 * Alias for escape()
	 * 
	 * @access public
	 * @param mixed $value
	 * @return void
	 */
	public function quote($value) {
		return $this->escape($value);
	}
	
	
}	//	Db
