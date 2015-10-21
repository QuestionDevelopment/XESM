<?php
/**
 * Security class
 *
 * @version 1.0
 * @package xesm
 * @subpackage bundle
 * @category class
 * @author Justin Campo <admin@xesm.com>
 * @license MIT http://opensource.org/licenses/MIT
 */
namespace xesm\bundle;

/**
 * Security class
 *
 * This class handles any methods that are used
 * to handle site security
 */
class security
{
    /* Sitewide salt value (from common config file) */
    private $site_salt = "";

    /**
     * Sets up the security class and retrieves required information
     *
     * @param $site_salt string The value of the site salt
     */
    public function __construct($site_salt)
    {
        $this->site_salt = $site_salt;
    }

    /**
     * Provides a basic security cleaning of a string
     *
     * @param string $data
     * @return string The safe data
     */
    public function clean($data)
    {
        $data = preg_replace("/[^a-zA-Z0-9\'@!?#=:$\/\-_@. ]/", "", $data);
        return $data;
    }

    /**
     * Cleans and allows access to all $_REQUEST vars
     *
     * Upon loading of the page class, all $_REQUEST vars are
     * parsed and assigned to the $page->params var for easy access
     *
     * @return array All $_REQUEST data cleaned
     */
    public function clean_array($dataArray)
    {
        $cleaned = array();
        if (count($dataArray) > 0 AND is_array($dataArray)){
            foreach ($dataArray as $key => $value){
                if ($key != "PHPSESSID"){ //filters var out if passing $_REQUEST
                    $value = self::clean($value);
                    $key = self::clean($key);
                    $cleaned[$key] = $value;
                }
            }
        }
        return $cleaned;
    }

    /**
     * Provides security cleaning by utilizing php's filter features
     *
     * @param string $data
     * @return string The safe username
     */
    public function filter($data)
    {
        $data = filter_var($data, FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_LOW);
        $data = filter_var($data, FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_HIGH);
        return $data;
    }

    /**
     * Removes all non numbers and non characters from a string
     *
     * @param string $data
     * @return string The safe string
     */
    public function alphanumeric($data)
    {
        $clean = preg_replace("/[^a-zA-Z0-9\s]/", "", $data);
        return $clean;
    }

    /**
     * Provides security cleaning for all user names
     *
     * @param string $data
     * @return string The safe username
     */
    public function title($data)
    {
        $data = strtolower($data);
        $data = preg_replace("/[^a-zA-Z0-9_\-.@!?#$]/", "", $data);
        return $data;
    }

    /**
     * Provides security cleaning for all user emails
     *
     * @param string $data
     * @return string The safe email
     */
    public function email($data)
    {
        $data = strtolower($data);
        $data = preg_replace("/[^a-zA-Z0-9@!?#=:$\-_@. ]/", "", $data);
        return $data;
    }

    /**
     * Provides security cleaning for all passwords
     *
     * @param string $data
     * @return string The safe password
     */
    public function password($data)
    {
        $data = preg_replace("/[^a-zA-Z0-9@-_!?#$]/", "", $data);
        return $data;
    }

    /**
     * Provides security cleaning for all file names
     *
     * @param string $data
     * @return string The safe file name
     */
    public function file($data)
    {
        $data = str_replace(" ", "_", $data);
        $data = preg_replace("/[^a-zA-Z0-9_\-.]/", "", $data);
        return $data;
    }

    /**
     * Provides security for all dynamic include paths
     *
     * @param string $data
     * @return string The safe file name
     */
    public function inc($data)
    {
        //prevent trying to go back to lower directories
        $data = str_replace("..", ".", $data);
        //can only include .php files
        if (substr($data, -4) != ".php"){ $data .= ".php"; }
        return $data;
    }

    /**
     * Provides encrypting for any string
     *
     * Requests SITE_SALT to be declared in the global config.
     * This data can not be decrypted.
     *
     * @param string $data Tje data to be encrypted
     * @param string $salt A salt specific to each method call
     * @param string $site_salt A site wide secondary salt
     * @return string The encrypted data
     */
    public function encrypt($data, $salt, $site_salt = "")
    {
        if (empty($site_salt)){ $site_salt = $this->site_salt; }
        $data = self::password($data);
        $data = hash_hmac('sha256', $data.$salt, $site_salt);
        return $data;
    }

    /**
     * Generates a salt for hash functions
     *
     * @param int $length
     * @return string The generated salt
     */
    public function salt($length = 12)
    {
        $characters = "0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ";
        $string = "";
        for ($p = 0; $p < $length; $p++) {
            $string .= $characters[mt_rand(0, 61)];
        }
        return $string;
        //return mcrypt_create_iv($length, MCRYPT_RAND);
    }

    /**
     * Secure Equals
     *
     * Provides a method to compare a and b without the no differences
     * in run time between true and false
     *
     * @author Taylor Hornby [Password Hashing With PBKDF2 (http://crackstation.net/hashing-security.htm)]
     * @copyright 2013
     * @param string $a Variable to be compared 1
     * @param string $b Variable to be compared
     * @return boolean Are the variables the same
     */
    function equals($a, $b)
    {
        $diff = strlen($a) ^ strlen($b);
        for($i = 0; $i < strlen($a) && $i < strlen($b); $i++)
        {
            $diff |= ord($a[$i]) ^ ord($b[$i]);
        }
        return $diff === 0;
    }
}
