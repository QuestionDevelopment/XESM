<?php
/**
 * Number manipulation class
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
 * Number manipulation class
 *
 * This class handles any methods that are used
 * to manipulate numbers
 *
 */
class num
{
    /**
     * Random number generator
     *
     * @param int $length The length of the number required
     * @return int A random number
     * @author Unknown
     */
    public function random($length = 8)
    {
        $characters = "0123456789abcdefghijklmnopqrstuvwxyz";
        $num = "";
        for ($p = 0; $p < $length; $p++) {
            $num .= $characters[mt_rand(0, strlen($characters))];
        }
        return $num;
    }
}
