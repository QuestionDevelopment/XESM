<?php
/**
 * Session class
 *
 * @version 1.0
 * @package xesm
 * @subpackage core
 * @category class
 * @author Justin Campo <admin@xesm.com>
 * @license MIT http://opensource.org/licenses/MIT
 */
namespace xesm\core;

/**
 * Session class
 *
 * This class handles any methods that are used
 * to manipulate and initialize PHP's sessions
 *
 */
class session
{
    /** Stores class objects used for dependency injection @var array */
    protected $log = array();

    /**
     * Retrieves data from session system
     *
     * This is one of php's magic functions.  It is called whenever an
     * undeclared variable is called
     *
     * Sample Usage : $c->Session->sample["test"];
     *
     * @param array $query An array representing the location of the data
     * @return object The value within the session
     */    
    public function __get($query)
    {
        $query = array($query);        
        return $this->select($query);
    }
    
    /**
     * Assigns data from session system
     *
     * Sample Use : $c->Session->sample_multi = "test"; (the _ represents another dimenion)
     * So the above would update $_SESSION["xesm"]["sample"]["multi"] = "test";
     *
     * @param string $name The variable name being assigned
     * @param array $data The information to insert into session
     */
    public function __set($name, $value)
    {
        $this->update($value);    
    }
    
    /**
     * Creates session
     *
     * Basic wrapper to determine what create method we are using
     */
    public function create($log)
    {
        $this->log = $log;
        $this->log->core("Session Pre-Start");
        if (session_id() === "") { session_start(); }
        $this->log->core("Session Started");
        if (!isset($_SESSION["xesm"])){ $_SESSION["xesm"] = array(); }
    }

    /**
     * Adds data to the session variable
     *
     * Normal functionality takes an array and assign it to the session.  To use :
     * Normal : $Session->update(array("session" => array("id" => "123")));
     * Shorthand encoding : session->id=test (slight performance decrease)
     *
     * @author Justin Campo
     * @author HamZa
     */
    public function update($data = array())
    {
        if (!is_array($data)){
            $pos = strpos($data, "->");
            if ($pos !== false) {
                //Determined using shorthand encoding
                $update_loc = preg_split("/(->|=)/", $data);
                $update_loc["xesm"] = $update_loc;
                $update_value = array_pop($update_loc);
                if (is_array($update_loc) AND count($update_loc) > 0 AND isset($update_value) AND $update_value != ""){
                    $max = count($update_loc)-1;
                    $update_array = array($update_loc[$max] => $update_value);
                    for($i=$max-1;$i>0;$update_array = array($update_loc[$i--] => $update_array));
                }
                $_SESSION = array_merge_recursive($_SESSION,$update_array);
                $this->log->core("Session Update (shorthand version) : ".print_r($data,true));
            }
        } else {
            $_SESSION["xesm"] = $this->array_merge_recursive_distinct($_SESSION["xesm"],$data);
            $this->log->core("Session Update (standard version) : ".print_r($data,true));
        }
    }

    /**
     * array_merge_recursive does indeed merge arrays, but it converts values with duplicate
     * keys to arrays rather than overwriting the value in the first array with the duplicate
     * value in the second array, as array_merge does. I.e., with array_merge_recursive,
     * this happens (documented behavior):
     *
     * array_merge_recursive(array('key' => 'org value'), array('key' => 'new value'));
     *     => array('key' => array('org value', 'new value'));
     *
     * array_merge_recursive_distinct does not change the datatypes of the values in the arrays.
     * Matching keys' values in the second array overwrite those in the first array, as is the
     * case with array_merge, i.e.:
     *
     * array_merge_recursive_distinct(array('key' => 'org value'), array('key' => 'new value'));
     *     => array('key' => array('new value'));
     *
     * Parameters are passed by reference, though only for performance reasons. They're not
     * altered by this function.
     *
     * @param array $array1
     * @param array $array2
     * @return array
     * @author Daniel <daniel (at) danielsmedegaardbuus (dot) dk>
     * @author Gabriel Sobrinho <gabriel (dot) sobrinho (at) gmail (dot) com>
     */
    public function array_merge_recursive_distinct (array &$array1,array &$array2 )
    {
        $merged = $array1;
        foreach ( $array2 as $key => &$value ) {
            if ( is_array ( $value ) && isset ( $merged [$key] ) && is_array ( $merged [$key] ) ) {
                $merged [$key] = $this->array_merge_recursive_distinct ( $merged [$key], $value );
            } else {
                $merged [$key] = $value;
            }
        }
        return $merged;
    }

    /**
     * Retrieves data from session
     *
     * Normal : $Session->select(array("session", "id"));
     * Shorthand encoding : session_id=test (slight performance decrease)
     */
    public function select($query = array())
    {
        if (!is_array($query)){
            $query = explode("_", $query);
        }
        if (is_array($query) AND count($query) > 0){
            $temp = $_SESSION["xesm"];
            foreach ($query as $level){
                if (isset($temp) AND is_array($temp) AND isset($temp[$level])){
                    $temp = $temp[$level];
                } else {
                    $temp = false;
                    break;
                }
            }
            $this->log->core("Session Select (standard version) : ".print_r($temp,true));
            return $temp;
        }
    }

    /**
     * Deletes value out of session
     *
     * Normal : $Session->select(array("session","id"));
     * Shorthand encoding : session->id=test (slight performance decrease)
     */
    public function delete($query = array())
    {
        if (isset($_SESSION["xesm"][$query[0]])) {
            $ref = &$_SESSION["xesm"];
            foreach ($query as $query_layer) {
                if (is_array($ref) && array_key_exists($query_layer, $ref)) {
                    $to_unset = &$ref;
                    $ref = &$ref[$query_layer];
                } else {
                    //throw new Exception('Path doe not exist');
                }
            }
            unset($to_unset[end($query)]);
        } else {
            return false;
        }
    }

    /**
     * Triggers an immediate session erasure
     */
    public function clear()
    {
        unset($_SESSION["xesm"]);
    }

    public function clear_cookies()
    {
        if (isset($_SERVER['HTTP_COOKIE'])) {
            $session_cookies = explode(';', $_SERVER['HTTP_COOKIE']);
            foreach($session_cookies as $session_cookie) {
                $parts = explode('=', $session_cookie);
                $name = trim($parts[0]);
                if ($name != "PHPSESSID" AND $name != "BALID") {
                    setcookie($name, '', time() - 1000);
                    setcookie($name, '', time() - 1000, '/', ".ssww.com");
                }
            }
        }
    }

    /**
     * Returns the current sessions id
     */
    public function id()
    {
        return session_id();
    }

}
