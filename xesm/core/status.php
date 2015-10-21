<?php
/**
 * Status Class
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
 * Status class
 *
 * This class stores errors and warnings
 * and allows for easy communication between objects
 * via container object
 */
class status
{
    /** Stores class objects used for dependency injection @var array */
    public $c = array();
    /** Stores all error messages @var array */
    public $error = array();
    /** Stores warnings @var array */
    public $warning = array();
    /** Stores standard output @var array */
    public $message = array();

    /**
     * Access Control Constructor
     *
     * @param array $c All class dependencies are imported and verified here
     */
    public function __construct($c)
    {
        $this->c = $c;
        if (isset($_REQUEST["error"]) AND !empty($_REQUEST["error"])){
            $error = $this->c->security->clean($_REQUEST["error"]);
            $this->error($error);
        }
        if (isset($_REQUEST["message"]) AND !empty($_REQUEST["message"])){
            $message = $this->c->security->clean($_REQUEST["message"]);
            $this->message($message);
        }
    }

    public function error($error){
        $this->error[] = $error;
    }

    public function warning($warning){
        $this->warning[] = $warning;
    }

    public function message($message){
        $this->message[] = $message;
    }

    public function valid($level = 'error'){
        if ($level = "all"){ $test = array("error","warning","message"); }
        else { $test = array($level); }

        $valid = true;
        foreach ($test as $type){
            if (count($this->$type)) {
                $valid = false;
            }
        }
        return $valid;
    }

    public function output($type = 'all'){
        if ($type == "all"){ $output = array("error","warning","message"); }
        else { $output = array($type); }

        foreach ($output as $type){
            if (count($this->$type)) {
                echo '<div class="xesm_' . $type . '"><div class="xesm_' . $type . '_icon"></div><ul>';
                foreach ($this->$type as $error) {
                    echo "<li>" . $error . "</li>";
                }
                echo '</ul></div>';
            }
        }
    }

    public function get($type = 'all') {
        if ($type == 'all') {
            return [ 'error' => $this->error, 'warning' => $this->warning, 'message' => $this->message ];
        } else {
            return [ $type => $this->$type ];
        }
    }
}
