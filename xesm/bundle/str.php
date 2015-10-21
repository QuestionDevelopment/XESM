<?php
/**
 * String manipulation class
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
 * String manipulation class
 *
 * This class handles any methods that are used
 * to manipulate strings
 */
class str
{
    /**
     * Determines if a variable has a value
     *
     * @param string $var The string being tested
     * @return boolean Does the string have a value
     */
    public function val(&$var)
    {
        if (isset($var)){
            if (!is_null($var) && $var != "" && $var != false){
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    /**
     * Truncate a string
     *
     * This method returns a string of a short length if it
     * is longer than the max length provided
     *
     * @param string $str The original string being manipulated
     * @param int $max The maximium length the above string should be
     * @param string $rep The string appended to the string if it is truncated
     * @return boolean Does the string have a value
     */
    public function truncate($str, $max, $rep = '...')
    {
        if(strlen($str) > $max) {
            $leave = $max - strlen($rep) + 1;
            return substr_replace($str, $rep, $leave);
        } else {
            return $str;
        }
    }

    /**
     * Random number string generator
     *
     * @param int $length The length of the string required
     * @return string A random string
     * @author Unknown
     */
    public function random($length = 8)
    {
        $characters = "0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ";
        $string = "";
        for ($p = 0; $p < $length; $p++) {
            $string .= $characters[mt_rand(0, 61)];
        }
        return $string;
    }

    /**
     * Removes all non numbers from a string
     *
     * @param string $data
     * @return string The safe string
     */
    public function restrict_numeric($data)
    {
        $clean = preg_replace("/[^0-9]/", '', $data);
        return $clean;
    }

    /**
     * Removes all non characters from a string
     *
     * @param string $data
     * @return string The safe string
     */
    public function restrict_characters($data)
    {
        $clean = preg_replace("/[^a-zA-Z\s]/", "", $data);
        return $clean;
    }

    /**
     * Removes all non numbers and non characters from a string
     *
     * @param string $data
     * @return string The safe string
     */
    public function restrict_alphanumeric($data)
    {
        $clean = preg_replace("/[^a-zA-Z0-9\s]/", "", $data);
        return $clean;
    }
}
