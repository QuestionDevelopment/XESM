<?php
/**
 * Constructor class for Xesm
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
 * Class Alias allows for the shortened version of Xesm to be called at anytime
 */
class_alias('\xesm\core\init', 'init');
/**
 * Constructor class for XesmCMS
 *
 * This class is the primary class used to start
 * loading a XesmCMS webpage.  It loads in all the
 * resources required for the displaying of web pages
 * and passes all class dependencies to the page class.
 * This class also starts the autoloader
 */
class init
{
    /** Stores root folder @var string */
    public $dir_root = "./";
    /** Stores class objects used for dependency injection (alias for $container) @var array */
    public $c = array();

    /**
     * Xesm Class Constructor
     *
     * Initializes the xesm system
     *
     * @param Boolean $error_reporting Determines if php error reporting is on before config file can be loaded
     * @param String $dir_root Dynamically change the root directory used by Init
     */
    public function __construct($error_reporting = false, $dir_root = "./")
    {
        //Sets initial php error reporting before config gets loaded
        if ($error_reporting == true){ error_reporting(E_ALL); ini_set('display_errors', 1); }
        else { error_reporting(0); }

        //provides the ability to load anywhere in dir structure
        $this->dir_root = $dir_root;
        
        //Initialize container and core classes
        $this->container();
        $this->config();
        $this->log();
        $this->session();
        $this->bundle();
        $this->db();
        $this->hook();
        $this->plugin();
        $this->status();

        //Enable autoload system : Uses Log class
        spl_autoload_register('\xesm\core\init::autoload');

        //Load all container data from xesm_container table : Uses db class
        $this->c->xesm_container();

        //Enable Error Reporting : Uses config and log class
        $this->error_reporting($this->c->config->error_reporting, $this->c->log);

        $this->c->log->core("Init Construction complete");
    }

    /**
     * Loads Page class
     *
     * @param Boolean $render Render page at the end of loading
     */
    public function page($render = true)
    {
        $this->c->log->core("Init page method started");
        include($this->dir_root.$this->c->config->dir_core."page.php");
        $this->c->assign("page",function ($c) { return new \xesm\core\page($c); });
        $this->c->page->init($render);
    }

    /**
     * Loads all required files to create container system
     */
    public function container()
    {
        include($this->dir_root."xesm/core/container.php");
        $this->c = new \xesm\core\container;
    }
    
    /**
     * Loads all required files to create config system
     */
    public function config(){
        include($this->dir_root."system/config/common.php");
        $server = str_replace("www.", "",strtolower($_SERVER['SERVER_NAME']));
        if (is_file($this->dir_root."system/config/".$server.".php")){
            include($this->dir_root."system/config/".$server.".php");
        } else {
            include($this->dir_root."system/config/default.php");
        }
        $this->c->assign("config", function ($c) {
            return new \xesm\config\config();
        });
    }
    
    /**
     * Loads all required files to create config system
     */
    public function log(){
        include($this->dir_root.$this->c->config->dir_core."log.php");
        $this->c->assign("log", function ($c) {
            return new \xesm\core\log($c->config->logging, $c->config->dir_public.$c->config->dir_log.date('Y_m_d_H_i_s_').rand(1, 99999).".log", $c->config->email_debug);
        });
    }
    
    /**
     * Loads all required files to create session system
     */
    public function session(){
        include($this->dir_root.$this->c->config->dir_core."session.php");
        $this->c->assign("session",function ($c) { return new \xesm\core\session(); });
        $this->c->session->create($this->c->log);
    }
    
    /**
     * Loads database class
     */
    public function db(){
        include($this->dir_root.$this->c->config->dir_core."db.php");
        $this->c->assign("db",function ($c) { return new \xesm\core\db($c->config->db_type.':host='.$c->config->db_host.';dbname='.$c->config->db_name, $c->config->db_user, $c->config->db_password, $c->log); });
        $this->c->db->run('SET NAMES utf8');
    }
    
    /**
     * Loads all bundle classes into container
     */
    public function bundle(){
        include($this->dir_root.$this->c->config->dir_bundle."num.php");
        $this->c->assign("num",function ($c) { return new \xesm\bundle\num(); });
        include($this->dir_root.$this->c->config->dir_bundle."security.php");
        $this->c->assign("security",function ($c) { return new \xesm\bundle\security($this->c->config->security_salt); });
        include($this->dir_root.$this->c->config->dir_bundle."str.php");
        $this->c->assign("str",function ($c) { return new \xesm\bundle\str(); });
        include($this->dir_root.$this->c->config->dir_bundle."util.php");
        $this->c->assign("util",function ($c) { return new \xesm\bundle\util(); });
        $this->c->log->core("Bundles Intialized");
    }

    /**
     * Loads hook system
     */
    public function hook(){
        if ($this->c->config->load_hook != false){
            include($this->dir_root.$this->c->config->dir_core."hook.php");
            $this->c->assign("hook",function ($c) { return new \xesm\core\hook($c); });
            $this->c->hook->run('init');
        }
    }

    /**
     * Loads plugin system
     */
    public function plugin(){
        if ($this->c->config->load_plugin != false){
            include($this->dir_root.$this->c->config->dir_core."plugin.php");
            $this->c->assign("plugin",function ($c) { return new \xesm\core\plugin($c); });
            $this->c->plugin->site();
        }
    }

    /**
     * Loads status system
     */
    public function status(){
        include($this->dir_root.$this->c->config->dir_core."status.php");
        $this->c->assign("status",function ($c) { return new \xesm\core\status($c); });
    }
    
    /**
     * Sets php error reporting status
     *
     * @param bool $setting True displays php errors/False hides them
     */
    public function error_reporting($setting = false, $logger)
    {
        if ($setting){
            error_reporting(E_ERROR | E_WARNING | E_PARSE);
            assert_options(ASSERT_ACTIVE,   true);
            assert_options(ASSERT_BAIL,     true);
            assert_options(ASSERT_WARNING,  true);
            register_shutdown_function(array($logger, "shutdown_function"));
            //@ini_set("error_log","");
            //set_error_handler
            //set_exception_handler
        } else {
            error_reporting(0);
            assert_options(ASSERT_ACTIVE,   false);
            assert_options(ASSERT_BAIL,     false);
            assert_options(ASSERT_WARNING,  false);
        }
    }
    
     /**
     * Autoload feature for xesm
     *
     * All classes to be auto loaded without the need for
     * including/requiring their file.
     *
     * @param string $class_request The class name to be used, must include namespace unless alias is created
     */
    public function autoload($class_request)
    {
        $this->c->log->core("Start autoload : ".$class_request);
        $class_data = explode("\\",$class_request);
        if (isset($class_data[0]) AND $class_data[0] == "plugin"){ $class_data[0] = "xesm/plugin"; }
        $class_file = $this->dir_root.$class_data[0]."/".$class_data[1]."/".$class_data[2].".php";


        if (is_file($class_file)){
            include_once($class_file);
            $this->c->log->core("Autoload Success : ".$class_file);
        } else {
            $this->c->log->core("Autoload Fail : ".$class_file);
            //die("Fatal Autoload : ".$class_name);
        }
    }
}
