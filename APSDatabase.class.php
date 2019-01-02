<?php

/**
 * @author Alamo Pereira Saravali <alamo.saravali@gmail.com>
 * @source https://github.com/alamops/APSDatabase.class.php
 */

class APSDatabase
{

	// Public
	public $host = '';
	public $user = '';
	public $password = '';
	public $database = '';
	public $port = '';

	public $connect_error;

	// Private
	private $connection;
	private $where_string = "";
	private $order_string = "";
	private $limit = "";
	private $skip = "";
	private $last_id = "";
	// private $join_table = "";
	// private $join_on = "";
	private $joins = array();
	private $from = "";
	private $last_query = "";

	function __construct($host = null, $user = null, $password = null, $database = null, $port = 3306)
	{
		// Verify
		$this->host = $host;
		$this->user = $user;
		$this->password = $password;
		$this->database = $database;
		$this->port = $port;

		// Make Connection
		$this->connection = new mysqli($this->host, $this->user, $this->password, $this->database, $this->port);

		if ($this->connection->connect_error)
			$this->connect_error = $this->connection->connect_error;
		//     die('Connect Error (' . $this->connection->connect_errno . ') ' . $this->connection->connect_error);

		$this->connection->set_charset('utf8');
	}

	// To open connection
	public function open()
	{
		$this->connection = new mysqli($this->host, $this->user, $this->password, $this->database);

		if ($this->connection->connect_error)
			die('Connect Error (' . $this->connection->connect_errno . ') ' . $this->connection->connect_error);

		$this->connection->set_charset('utf8');
	}

	// Generate a query with array data
	private function product_query_data($type, $array)
	{
		switch ($type) {
			case 'insert':
			$data_col = "(";
			$data_values = "(";
			$count = 0;
			foreach ($array as $col => $value) {
				if($count == 0)
				{
					$data_col .= $col;

					if(!is_bool($value) && !is_int($value) && !is_float($value) && !is_double($value) && !is_numeric($value) && !is_null($value))
						$data_values .= "'$value'";
					else if(is_null($value))
						$data_values .= "''";
					else
						$data_values .= "$value";

					$count++;
				}
				else
				{
					$data_col .= ", " . $col;
					$data_values .= ", ";

					if(!is_bool($value) && !is_int($value) && !is_float($value) && !is_double($value) && !is_numeric($value) && !is_null($value) && !is_array($value))
						$data_values .= "'$value'";
					else if(is_null($value))
						$data_values .= "''";
					else if(is_array($value))
						$data_values .= "''";
					else
						$data_values .= "$value";
				}
			}

			return $data_col . ") VALUES " . $data_values . ")";
			break;

			case 'update':
			$data_string = "";

			foreach ($array as $col => $value) {
				if(!$data_string)
					$data_string = "SET $col=";
				else
					$data_string .= ", $col=";

				if(!is_bool($value) && !is_int($value) && !is_float($value) && !is_double($value) && !is_numeric($value) && !is_null($value))
					$data_string .= "'$value'";
				else if(is_null($value))
					$data_string .= "''";
				else
					$data_string .= "$value";
			}

			return $data_string;
			break;
		}
	}

	// Save WHERE
	public function where($col, $value)
	{
		if($this->where_string && substr($this->where_string, -1) != '(') {
			if (strstr($col, ' ')) {
				$this->where_string .= " AND $col ";
			}
			else {
				$this->where_string .= " AND $col = ";
			}
		}
		else if ($this->where_string && substr($this->where_string, -1) == '(') {
			if (strstr($col, ' ')) {
				$this->where_string .= "$col ";
			}
			else {
				$this->where_string .= "$col = ";
			}
		}
		else {
			if (strstr($col, ' ')) {
				$this->where_string .= "WHERE $col ";
			}
			else {
				$this->where_string .= "WHERE $col = ";
			}
		}

		if(!is_bool($value)
			&& !is_int($value)
			&& !is_float($value)
			&& !is_double($value)
			&& !is_numeric($value)
			&& !is_null($value)
		) {
			$this->where_string .= "'$value'";
		}
		else if(is_null($value)) {
			$this->where_string .= "''";
		}
		else {
			$this->where_string .= "$value";
		}
	}

	// Save OR in WHERE
	public function where_or($col, $value)
	{
		if($this->where_string && substr($this->where_string, -1) != '(') {
			if (strstr($col, ' ')) {
				$this->where_string .= " OR $col ";
			}
			else {
				$this->where_string .= " OR $col = ";
			}
		}
		else if ($this->where_string && substr($this->where_string, -1) == '(') {
			if (strstr($col, ' ')) {
				$this->where_string .= "$col ";
			}
			else {
				$this->where_string .= "$col = ";
			}
		}
		else {
			if (strstr($col, ' ')) {
				$this->where_string .= "WHERE $col ";
			}
			else {
				$this->where_string .= "WHERE $col = ";
			}
		}

		if(!is_bool($value)
			&& !is_int($value)
			&& !is_float($value)
			&& !is_double($value)
			&& !is_numeric($value)
			&& !is_null($value)
		) {
			$this->where_string .= "'$value'";
		}
		else if(is_null($value)) {
			$this->where_string .= "''";
		}
		else {
			$this->where_string .= "$value";
		}
	}

	/**
	 * WHERE IS NULL
	 */
	public function where_is_null($col) {
		if($this->where_string && substr($this->where_string, -1) != '(') {
			$this->where_string .= " AND $col IS NULL";
		}
		else if ($this->where_string && substr($this->where_string, -1) == '(') {
			$this->where_string .= "$col IS NULL";
		}
		else {
			$this->where_string .= "WHERE $col IS NULL";
		}
	}

	/**
	 * WHERE IS NULL
	 */
	public function where_is_null_or($col) {
		if($this->where_string && substr($this->where_string, -1) != '(') {
			$this->where_string .= " OR $col IS NULL";
		}
		else if ($this->where_string && substr($this->where_string, -1) == '(') {
			$this->where_string .= "$col IS NULL";
		}
		else {
			$this->where_string .= "WHERE $col IS NULL";
		}
	}

	/**
	 * WHERE IS NOT NULL
	 */
	public function where_is_not_null($col) {
		if($this->where_string && substr($this->where_string, -1) != '(') {
			$this->where_string .= " AND $col IS NOT NULL";
		}
		else if ($this->where_string && substr($this->where_string, -1) == '(') {
			$this->where_string .= "$col IS NOT NULL";
		}
		else {
			$this->where_string .= "WHERE $col IS NOT NULL";
		}
	}

	/**
	 * WHERE IS NOT NULL
	 */
	public function where_is_not_null_or($col) {
		if($this->where_string && substr($this->where_string, -1) != '(') {
			$this->where_string .= " OR $col IS NOT NULL";
		}
		else if ($this->where_string && substr($this->where_string, -1) == '(') {
			$this->where_string .= "$col IS NOT NULL";
		}
		else {
			$this->where_string .= "WHERE $col IS NOT NULL";
		}
	}

	/**
	 * GROUP START
	 * @return [type] [description]
	 */
	public function group_start ()
	{
		if ($this->where_string) {
			$this->where_string .= ' AND (';
		}
		else {
			$this->where_string .= 'WHERE (';
		}
	}

	/**
	 * GROUP START WHIT OR
	 * @return [type] [description]
	 */
	public function group_start_or ()
	{
		if ($this->where_string) {
			$this->where_string .= ' OR (';
		}
		else {
			$this->where_string .= 'WHERE (';
		}
	}

	/**
	 * GROUP END
	 * @return [type] [description]
	 */
	public function group_end ()
	{
		$this->where_string .= ')';
	}

	// Save ORDER BY
	public function order_by($col, $order = 'ASC')
	{
		$this->order_string = "ORDER BY $col $order";
	}

	/**
	 * SET LIMIT
	 * @param  integer $count [description]
	 * @return [type]         [description]
	 */
	public function limit ($count = 0) {
		if ($count) {
			$this->limit = "LIMIT $count";
		}
	}

	/**
	 * SET SKIP
	 * @param  integer $count [description]
	 * @return [type]         [description]
	 */
	public function skip ($count = 0) {
		if ($count) {
			$this->skip = "OFFSET $count";
		}
	}

	/**
	 * FROM
	 * @param  [type] $table [description]
	 * @return [type]        [description]
	 */
	public function from ($table) {
		$this->from = $table;
	}

	/**
	 * JOIN
	 * @param  [type] $table [description]
	 * @param  [type] $on    [description]
	 * @return [type]        [description]
	 */
	public function join ($table, $on) {
		// $this->join_table = $table;
		// $this->join_on = $on;
		$this->joins[$table] = $on;
	}

	// Insert data in table
	public function insert ($table, $array, $save_last_id = true)
	{
		if(!$table || !$array)
			return false;

		//Open
		$this->open();

		$data = $this->product_query_data('insert', $array);

		$insert = "INSERT INTO $table " . $data;

		$this->last_query = $insert;

		$result = $this->connection->query($insert);

		$this->clean_query();

		if($result)
		{
			if($save_last_id) {
				$this->last_id = $this->connection->insert_id;
			}

			return true;
		}
		else
			return false;
	}

	// Select data in table
	public function select($what = "*", $table = null)
	{
		if(!$table && !$this->from) {
			return false;
		}
		else if (!$table && $this->from) {
			$table = $this->from;
		}

		//Open
		$this->open();

		if (!$this->joins) {
			$select_string = "SELECT $what FROM $table $this->where_string $this->order_string $this->limit $this->skip";
		}
		else {
			$select_string = "SELECT $what FROM $table";

			foreach ($this->joins as $table => $on) {
				$select_string .=  " JOIN $table ON $on";
			}

			$select_string .= " $this->where_string $this->order_string $this->limit $this->skip";
		}

		$this->last_query = $select_string;

		$this->clean_query();

		return $this->connection->query($select_string);
	}

	// Update data in table
	public function update($table, $array)
	{
		if(!$table || !$array)
			return false;

		//Open
		$this->open();

		$data = $this->product_query_data('update', $array);

		$update = "UPDATE $table " . $data . " $this->where_string";

		$this->last_query = $update;

		$result = $this->connection->query($update);

		$this->clean_query();

		if($result)
			return true;
		else
			return false;
	}

	// Drop / Delete data from table
	public function delete($table)
	{
		if(!$table)
			return false;

		//Open
		$this->open();

		$delete = "DELETE FROM $table $this->where_string";

		$this->last_query = $delete;

		$result = $this->connection->query($delete);

		$this->clean_query();

		if($result) {
			return true;
		}
		else {
			return false;
		}
	}

	// Custom Query
	public function query($query)
	{
		$this->clean_query();

		return $this->connection->query($query);
	}

	// Return the Last Inserted ID
	public function get_last_insert_id()
	{
		return $this->last_id;
	}

	/**
	 * GET LAST QUERY
	 * @return [type] [description]
	 */
	public function get_last_query () {
		return $this->get_last_query_string();
	}

	/**
	 * GET LAST QUERY STRING
	 * @return [type] [description]
	 */
	public function get_last_query_string()
	{
		return $this->last_query;
	}

	/**
	 * CLEAN QUERY
	 * @return [type] [description]
	 */
	public function clean_query () {
		$this->where_string = '';
		$this->order_string = '';
		$this->limit = '';
		$this->skip = '';
		// $this->join_table = '';
		// $this->join_on = '';
		$this->joins = array();
		$this->from = '';
	}

	// Close the connection
	public function close ()
	{
		$this->connection->close();
	}

}
