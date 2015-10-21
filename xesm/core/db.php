<?php
/**
 * General Database Class
 *
 * @version 1.0
 * @package xesm
 * @subpackage core
 * @category class
 * @author Justin Campo <admin@xesmCMS.com>
 * @license MIT http://opensource.org/licenses/MIT
 */
namespace xesm\core;

/**
 * General Database Class
 *
 * This database class runs off PHP's PDO System.
 * It performs all database related functions.
 */
class db
{
    /** Stores all database error information @var string */
    protected $error;
    /** Stores the sql query @var string */
    protected $sql;
    /** Stores any variables that are going to be inserted into the sql @var array */
    protected $bind;
    /** Stores the site logging object @var /xesm/core/log */
    protected $log;
    /** Prefix added to all database tables @var string */
    protected $tbl_prefix = "";
    /** Stores the database objects @var array */
    protected $database = array();
	
    /**
     * Establishes the connection to the database
     *
     * @param string $dsn Database type
     * @param string $user Database username
     * @param string $password Database user password
     * @param object $log_object The system that handle db logging
     * @param string $location Where the database object will be stored
     */
    public function __construct($dsn, $user="", $password="", $log_object, $location = "default")
    {
        $this->log = $log_object;
        $this->init($dsn, $user, $password, $location);
        $this->log->db("Database Initialized");
    }

    /**
     * Establishes the connection to the database
     *
     * @param string $dsn Database type
     * @param string $user Database username
     * @param string $password Database user password
     * @param /xesm/core/log The Db logger object
     * @param string $location Where the database object will be stored
     */
    public function init($dsn, $user="", $password="", $location = "default")
    {

        $options = array(\PDO::ATTR_PERSISTENT => true, \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION );
        try {
            $this->database[$location] = new \PDO($dsn, $user, $password, $options);
        } catch (\PDOException $e) {
            $this->error = $e->getMessage();
            $this->log->db($this->error);
        }
    }

    /**
     * Inserts Record into database
     *
     * @param $table The name of the database table
     * @param $data array The data to be inserted into the table
     * @param string $location Where the database object will be stored
     * @return array|bool The lastInsertID
     */
    public function insert($table, $data, $created = true, $location = "default")
    {
        if ($created){ $data["created"] = date("Y-m-d H:i:s"); }
        $fields = $this->extract_fields($data);
        $sql = "INSERT INTO " . $this->tbl_prefix . $table . " (" . implode($fields, ", ") . ") VALUES (:" . implode($fields, ", :") . ");";
        $bind = array();
        foreach($fields as $field)
            $bind[":$field"] = $data[$field];
        return $this->run($sql, $bind, $location);
    }

    /**
     * Select or retrieve data from database
     *
     * @param string $table The table where the data resides
     * @param string $where The conditions to retrieve the data
     * @param array $bind The dynamic variables used in $where param
     * @param string $fields Specific fields to retrieve from database
     * @param string $location Where the database object will be stored
     * @return array|bool The database results
     */
    public function select($table, $where="", $bind=array(), $fields="*", $location = "default")
    {
        $sql = "SELECT " . $fields . " FROM " . $this->tbl_prefix . $table;
        if(isset($where) && $where != ""){ $sql .= " WHERE " . $where; }
        $sql .= ";";
        return $this->run($sql, $bind, $location);
    }

    /**
     * Update/Modify data in the database
     *
     * @param string $table The table where the data resides
     * @param string $where The conditions to retrieve the data
     * @param array $bind The dynamic variables used in $where param
     * @param string $location Where the database object will be stored
     * @return array|bool The id of the record
     */
    public function update($table, $data, $where, $bind=array(), $location = "default")
    {
        $fields = $this->extract_fields($data);
        $field_size = count($fields);

        $sql = "UPDATE " . $this->tbl_prefix . $table . " SET ";
        for($f = 0; $f < $field_size; ++$f) {
            if($f > 0){ $sql .= ", "; }
            $sql .= $fields[$f] . " = :update_" . $fields[$f];
        }
        $sql .= " WHERE " . $where . ";";

        foreach($fields as $field)
            $bind[":update_$field"] = $data[$field];

        return $this->run($sql, $bind, $location);
    }

    /**
     * Delete Record in the database
     *
     * @param string $table The table where the data resides
     * @param string $where The conditions to retrieve the data
     * @param array $bind The dynamic variables used in $where para
     * @param string $location Where the database object will be stored
     *
     * @return array Results of delete query
     */
    public function delete($table, $where, $bind=array(), $location = "default")
    {
        $sql = "DELETE FROM " . $this->tbl_prefix . $table . " WHERE " . $where . ";";
        return $this->run($sql, $bind, $location);
    }

    /**
     * Count the number of records in the database
     *
     * @param string $table The table where the data resides
     * @param string $where The conditions to retrieve the data
     * @param array $bind The dynamic variables used in $where param
     * @param string $location Where the database object will be stored
     * @return int the number of records found
     */
    public function count($table, $where="", $bind=array(), $location = "default")
    {
        $sql = "SELECT COUNT(" . $table . "ID) FROM " . $this->tbl_prefix . $table;
        if (isset($where) AND $where != ""){
            $sql .= " WHERE " . $where . ";";
        }
        $results = $this->run($sql, $bind, $location);
        return $results[0]["COUNT(" . $table . "ID)"];
    }

    /**
     * Extracts the fields from the data provided
     *
     * @param array $data The data going into the database
     * @return array The fields being utilized
     */
    public function extract_fields($data)
    {
        $return_array = array();
        if (is_array($data) AND count($data) > 0){
            foreach ($data as $key => $value){
                $return_array[] = $key;
            }
        }
        return $return_array;
    }

    /**
     * Handles debugging of database queries
     */
    public function debug()
    {
        if ($this->log->mode != "disabled"){
            $message = "-----------------------------------\n";
            $message .= "DB DEBUG\n";
            $message .= "-----------------------------------\n";
            $message .= "RESULT : ".$this->error."\n";
            $message .= "SQL : ".$this->sql."\n";
            $message .= "BIND : ". trim(print_r($this->bind, true))."\n";
            $translation = $this->sql;
			if (isset($this->bind) AND is_array($this->bind) AND count($this->bind)){
				foreach ($this->bind as $key => $value) { $translation = str_replace($key, $value, $translation); }
			}
            $message .= "TRANSLATION : ".$translation."\n";
            $backtrace = debug_backtrace();
            if(isset($backtrace)) {
                foreach($backtrace as $info) {
					if (isset($info["file"]) AND isset($info["line"])){
						$message .= "BACKTRACE : ". $info["file"] . " at line " . $info["line"]."\n";	
					} else if (isset($info["file"])){
						$message .= "BACKTRACE : ". $info["file"] . "\n";
					}
                }
            }
            $message .= "-----------------------------------\n";
            $this->log->db($message);
        }
    }

    /**
     * The primary method of the database class
     *
     * This method is called by all database manipulation methods.  It actually
     * interfaces with the database and performs all queries.
     *
     * @param string $sql The SQL query
     * @param array $bind The dynamic database being used in the SQL query
     * @param string $location Where the database object will be stored
     * @return array|bool The results of the query
     */
    public function run($sql, $bind=array(), $location = "default")
    {
        $this->sql = trim($sql);
        $this->bind = $bind;
        $this->error = "";

        try {
            $pdostmt = $this->database[$location]->prepare($this->sql);
            if($pdostmt->execute($this->bind) !== false) {
                $this->debug();
				if(preg_match("/^(select|describe|pragma) /i", $this->sql)) {
					return $pdostmt->fetchAll(\PDO::FETCH_ASSOC);
				} elseif(preg_match("/^(insert) /i", $this->sql)) {
					return array("last_insert_id" => $this->database[$location]->lastInsertId(), "row_count" => $pdostmt->rowCount(), "valid" => true);
				}  elseif(preg_match("/^(delete|update) /i", $this->sql)) {
					return array("row_count" => $pdostmt->rowCount(), "valid" => true);
				}
            }
        } catch (\PDOException $e) {
            $this->error = $e->getMessage();
            $this->debug();
            return false;
        }
    }
}