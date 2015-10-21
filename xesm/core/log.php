<?php
/**
 * Event Logging Class
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
 * Event Logging Class
 *
 * This class, which is psr-3 compatible writes all data to a log file.
 */
class log
{
    /** Is logging enabled @var bool */
    public $mode;
    /** The email address to notify if emergency log is inserted @var string */
    private $emergency_contact;
    /** The filename of the log file created @var string */
    private $file_name;

    /**
     * Limber Class Constructor
     */
    public function __construct($mode = "default", $file_name = 'debug.log', $emergency_contact = '')
    {
        $this->mode = $mode;
        $this->file_name = $file_name;
        $this->emergency_contact = $emergency_contact;
        if (isset($_SERVER["SCRIPT_NAME"])){
            $this->log("core", "Log system started : PHP_SELF = ".$_SERVER["PHP_SELF"]." FILE = ".__FILE__." SCRIPT_NAME = ".$_SERVER["SCRIPT_NAME"]);
        } else {
            $this->log("core", "Log system started : FILE = ".__FILE__);
        }
    }

    /**
     * Logs with an arbitrary level.
     *
     * @param mixed $level
     * @param string $message
     * @param array $context
     * @return null
     */
    public function log($level, $message, array $context = array())
    {
        $divider = "----------------------------------------------------------------------------------------";
        if ($this->mode != 'disabled'){
            if (isset($message) && $message != ""){
                if (is_array($message)){ $message = print_r($message,true); }
                $fh = @fopen($this->file_name, 'a');
                if ($fh) {
                    fwrite($fh, $divider."\n[".$level."] ".date("Y-m-d H:i:s")."\n".$divider."\n".$message."\n");
                    fclose($fh);
                }
                if ($level == "emergency"){
                    if ($this->emergency_contact != '' && isset($this->emergency_contact)){
                        $this->email($this->emergency_contact, "log@donotreply.com", "EMERGENCY LOG ENTRY", $message);

                    }
                }
            }
        }
        if ($this->mode == "verbose"){
            if (isset($message) && $message != ""){
                if (is_array($message)){
                    echo "<pre>";
                    print_r($message);
                    echo "</pre>";
                } else {
                    echo "<br/>".$divider."<br/>[".$level."]  ".date("Y-m-d H:i:s");
                    echo "<br/>".$divider;
                    echo "<br/>".nl2br($message)."<br/>";
                }
            }
        }
    }

    /**
     * Email message using PHP built in capability
     *
     * @param string $message_to
     * @param string $message_from
     * @param string $message_subject
     * @param string $message_body
     */
    public function email($message_to, $message_from, $message_subject, $message_body)
    {
        $headers = 'From: '.$message_from. "\r\n" .
            'Reply-To: '. $message_to . "\r\n" .
            'X-Mailer: PHP/' . phpversion();
        mail($message_to, $message_subject, $message_body, $headers);
    }

    /**
     * System is unusable.
     *
     * @param string $message
     * @param array $context
     * @return null
     */
    public function emergency($message, array $context = array())
    {
        $this->log("emergency", $message, $context);
    }

    /**
     * Action must be taken immediately.
     *
     * Example: Entire website down, database unavailable, etc. This should
     * trigger the SMS alerts and wake you up.
     *
     * @param string $message
     * @param array $context
     * @return null
     */
    public function alert($message, array $context = array())
    {
        $this->log("alert", $message, $context);
    }

    /**
     * Critical conditions.
     *
     * Example: Application component unavailable, unexpected exception.
     *
     * @param string $message
     * @param array $context
     * @return null
     */
    public function critical($message, array $context = array())
    {
        $this->log("critical", $message, $context);
    }

    /**
     * Runtime errors that do not require immediate action but should typically
     * be logged and monitored.
     *
     * @param string $message
     * @param array $context
     * @return null
     */
    public function error($message, array $context = array())
    {
        $this->log("error", $message, $context);
    }

    /**
     * Exceptional occurrences that are not errors.
     *
     * Example: Use of deprecated APIs, poor use of an API, undesirable things
     * that are not necessarily wrong.
     *
     * @param string $message
     * @param array $context
     * @return null
     */
    public function warning($message, array $context = array())
    {
        $this->log("warning", $message, $context);
    }

    /**
     * Normal but significant events.
     *
     * @param string $message
     * @param array $context
     * @return null
     */
    public function notice($message, array $context = array())
    {
        $this->log("notice", $message, $context);
    }

    /**
     * Generate data
     *
     * Example: User logs in, SQL logs.
     *
     * @param string $message
     * @param array $context
     * @return null
     */
    public function info($message, array $context = array())
    {
        $this->log("info", $message, $context);
    }

    /**
     * An action performed by the system's database class
     *
     * @param string $message
     * @param array $context
     * @return null
     */
    public function db($message, array $context = array())
    {
        $this->log("db", $message, $context);
    }

    /**
     * Information in regards to core system
     *
     * @param string $message
     * @param array $context
     * @return null
     */
    public function core($message, array $context = array())
    {
        $this->log("core", $message, $context);
    }

    /**
     * Information in regards to plugin
     *
     * @param string $message
     * @param array $context
     * @return null
     */
    public function plugin($message, array $context = array())
    {
        $this->log("plugin", $message, $context);
    }
    /**
     * An action performed by the system's autoload function
     *
     * @param string $message
     * @param array $context
     * @return null
     */
    public function autoload($message, array $context = array())
    {
        $this->log("autoload", $message, $context);
    }

    /**
     * Text that is meant to be outputted onto the screen for admin users
     * ie : has tag output
     *
     * @param string $message
     * @param array $context
     */
    public function debug($message = '', array $context = array())
    {
        $output_title = "debug";
        if (isset($message) AND $message != "" AND $message != false AND !is_null($message)){
            $this->log($output_title, $message, $context);
        } else if ($this->mode === "disabled") {
            echo "Logging is disabled";
        } else if (!is_file($this->file_name)) {
            echo "Log File Not Found [".$this->file_name."]";
        }
    }

    /**
     * Display the log console
     *
     * @param mixed $level
     * @param string $message
     * @param array $context
     * @return null
     */
    public function output($display = "all")
    {
        if ($this->mode == "disabled") {
            echo "Logging is disabled";
        } else if (!is_file($this->file_name)) {
            echo "Log File Not Found [".$this->file_name."]";
        } else if ($display == "all") {
            echo '<span class="bold">'.$this->file_name.'</span><br/>';
            echo nl2br(file_get_contents($this->file_name));
        } else {
            $output_performed = false;
            $fh = @fopen($this->file_name, 'r');
            if ($fh) {
                $needle = "[".$display."]";
                $trigger_output = 0;
                while (($line = fgets($fh)) !== false) {
                    if ($trigger_output == 0){
                        $pos = strpos($line, $needle);
                        if ($pos !== false) {
                            echo str_replace($needle,"",$line)."<br/>";
                            $output_performed = true;
                            $trigger_output = 1;
                        }
                    } else {
                        if (preg_match("/^\[.*?\]/",$line) === 1){
                            $pos = strpos($line, $needle);
                            if ($pos === false) {
                                $trigger_output = 0;
                            } else {
                                echo str_replace($needle,"",$line)."<br/>";
                                $output_performed = true;
                            }
                        } else {
                            echo $line."<br/>";
                            $output_performed = true;
                        }
                    }
                }
                fclose($fh);
            }
            if ($output_performed == false){ echo "No Debug Information Provided"; }
        }
    }
    
    /**
     * Php's shutdown function to help with debugging
     */
    public function shutdown_function()
    {
        $error = error_get_last();
        if (is_array($error) AND count($error) > 0){
            echo "Shutdown Error: ".print_r($error,true);
            $this->log("debug", "Shutdown Error: ".print_r($error,true));
        }
    }

}
