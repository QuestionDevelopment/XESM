<?php
/**
 * Site Specific Config File
 *
 * @version 1.0
 * @package xesm
 * @subpackage config
 * @category class
 * @author Justin Campo <admin@xesm.com>
 * @license MIT http://opensource.org/licenses/MIT
 */
namespace xesm\config;

/**
 * Site Specific Config File
 *
 * This class stores all config data specific to a certain site.
 * It is commonly used to store differences between development accounts.
 * The site is determine in the Page Class->autoload
 * $className = strtolower($_SERVER['SERVER_NAME']);
 * $className = str_replace("www.", "", $className);
 */
class config extends \xesm\config\common
{
    //
    // Common
    //
    /** How logging is perfromed in system @var string (disabled,enabled,verbose) */
    public $logging = 'enabled';
    /** Cache busting version for css */
    public $cache_bust_css = 1;
    /** Cache busting version for js */
    public $cache_bust_js = 1;
    /** Will the website display errors for visitors @var boolean */
    public $error_reporting = true;

    //
    // Site
    //
    /** The internal unique id for this site @var int */
    public $site_id = 1;
    /** The url/domain name of this site @var string */
    public $site_domain = 'example.com';
    /** The human readable name of the site @var string */
    public $site_name = 'Example Site';
    /** Will the website cache any data @var boolean */
    public $cache = false;

    //
    // Template Variables
    //
    /** The template the site is running from @var string */
    public $template = 'example';
    /** The location of the javascript files @var string */
    public $css = '/template/example/css/';
    /** The location of the javascript files @var string */
    public $img = '/template/example/img/';
    /** The location of the javascript files @var string */
    public $js = '/template/example/js/';

    //
    // Dir
    //
    /** The location of the this site on the server @var string */
    public $dir_public = '/home/example/public_html/';

    //
    // Email
    //
    /** The email address of the owner of the website @var string */
    public $email_admin = 'admin@example.com';
    /** The customer service email address @var string */
    public $email_cs = 'admin@example.com';
    /** The email address that gets notified of any site issues @var string */
    public $email_debug = 'admin@example.com';

    //
    // Database
    //
    /** The name of this sites database @var string */
    public $db_name = 'db_name';
    /** The name of the user authorized to access database @var string */
    public $db_user = 'db_user';
    /** The password to of the above user to access database @var string */
    public $db_password = 'xxxxxxxxx';

    //
    // Optimization
    //
    /** Should xesm load the hook system @var boolean */
    public $load_hook = true;
    /** Should xesm load the plugin system @var boolean */
    public $load_plugin = true;
    /** Should xesm confirm that the head and foot are present @var boolean */
    public $verify_head_foot = true;
}
