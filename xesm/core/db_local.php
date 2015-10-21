<?php
/**
 * Local Database Class
 *
 * @version 1.0
 * @package ssww
 * @subpackage core
 * @category class
 * @author Justin Campo <jcampo@ssww.com>
 */
namespace xesm\core;
/**
 * Local Database Class
 *
 * Alls the processing of large database requests by storing results
 * locally and retrieving them one result at a time
 */
class db_local extends \xesm\core\db
{
    /** Stores the query performed locally  @var object */
    private $result = array();
    
    /**
     * This method is takes the results from the query and stores it locally
     * so it can be iterated upon later
     *
     * @param string $sql The SQL query
     * @param array $bind The dynamic database being used in the SQL query
     * @return array|bool The results of the query
     */
    public function local_run($sql, $bind=array(), $db_location = "default", $result_location = "default")
    {
        $this->sql = trim($sql);
        $this->bind = $bind;
        $this->error = "";

        try {
            $pdostmt = $this->database[$db_location]->prepare($this->sql);
            if($pdostmt->execute($this->bind) !== false) {
                $this->debug_output();
                if(preg_match("/^(" . implode("|", array("select", "describe", "pragma")) . ") /i", $this->sql))
                    $this->result[$result_location] = $pdostmt;
            }
        } catch (\PDOException $e) {
            $this->error = $e->getMessage();
            $this->debug_output();
            return false;
        }
    }

    /**
     * The primary method of the database class
     *
     * This method is called by all database manipulation methods.  It actually
     * interfaces with the database and performs all queries.
     */
    public function local_result($location = "default")
    {
        if (isset($this->result[$location]) AND is_object($this->result[$location])){
            $row = $this->result[$location]->fetch(\PDO::FETCH_ASSOC);
            if (isset($row) AND is_array($row)){
                return $row;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    public function local_reset($location = "default")
    {
        if (isset($this->result[$location]) AND is_object($this->result[$location])){
            $this->result[$location]->fetch(PDO::FETCH_ASSOC, PDO::FETCH_ORI_ABS, 0);
        }
    }

    public function local_clear($location = "default")
    {
        if (isset($this->result[$location]) AND is_object($this->result[$location])){
            $this->result[$location] = null;
        }
    }
}