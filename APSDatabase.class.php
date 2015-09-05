<?php

/**
 * @author Alamo Pereira Saravali <alamo.saravali@gmail.com>
 */

class APSDatabase
{

    // Public
    public $host = '';
    public $user = '';
    public $password = '';
    public $database = '';

    public $connect_error;
    
    // Private
    private $connection;
    private $where_string = "";
    private $order_string = "";
    private $last_id = "";

    function __construct($host = null, $user = null, $password = null, $database = null)
    {
        // Verify
        if($host)
            $this->host = $host;

        if($user)
            $this->user = $user;

        if($password)
            $this->password = $password;

        if($database)
            $this->database = $database;

        // Make Connection
        $this->connection = new mysqli($this->host, $this->user, $this->password, $this->database);

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

                            if(!is_bool($value) && !is_int($value) && !is_float($value) && !is_double($value) && !is_numeric($value) && !is_null($value))
                                $data_values .= "'$value'";
                            else if(is_null($value))
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
        if($this->where_string)
            $this->where_string .= " AND $col = ";
        else
            $this->where_string .= "WHERE $col = ";

        if(!is_bool($value) && !is_int($value) && !is_float($value) && !is_double($value) && !is_numeric($value) && !is_null($value))
            $this->where_string .= "'$value'";
        else if(is_null($value))
            $this->where_string .= "''";
        else
            $this->where_string .= "$value";
    }

    // Save OR in WHERE
    public function where_or($col, $value)
    {
        if($this->where_string)
            $this->where_string .= " OR $col = ";
        else
            $this->where_string .= "WHERE $col = ";

        if(!is_bool($value) && !is_int($value) && !is_float($value) && !is_double($value) && !is_numeric($value) && !is_null($value))
            $this->where_string .= "'$value'";
        else if(is_null($value))
            $this->where_string .= "''";
        else
            $this->where_string .= "$value";
    }

    // Save ORDER BY
    public function order_by($col, $order = 'ASC')
    {
        $this->order_string = "ORDER BY $col $order";
    }

    // Insert data in table
    public function insert($table, $array, $save_last_id = true)
    {
        if(!$table || !$array)
            return false;

        //Open
        $this->open();

        $data = $this->product_query_data('insert', $array);

        $insert = "INSERT INTO $table " . $data;

        $this->where_string = "";

        $result = $this->connection->query($insert);

        if($result)
        {
            if($save_last_id)
                $this->last_id = $this->connection->insert_id;
            
            return true;
        }
        else
            return false;
    }

    // Select data in table
    public function select($what = "*", $table)
    {
        if(!$table)
            return false;

        //Open
        $this->open();

        $select_string = "SELECT $what FROM $table $this->where_string $this->order_string";

        $this->where_string = "";
        $this->order_string = "";

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
        
        $this->where_string = "";

        $result = $this->connection->query($update);

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

        $result = $this->connection->query($delete);

        if($result)
            return true;
        else
            return false;
    }

    // Custom Query
    public function query($query)
    {
        $this->where_string = "";

        return $this->connection->query($query);
    }

    // Return the Last Inserted ID
    public function get_last_insert_id()
    {
        return $this->last_id;
    }

    // Close the connection
    public function close ()
    {
        $this->connection->close();
    }

}
