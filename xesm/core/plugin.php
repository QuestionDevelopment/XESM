<?php
/**
 * Plugin Control Class
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
 * Access Control class
 *
 * This class handles all interactions with plugins
 * within the XesmCMS system
 */
class plugin
{
    /** Stores class objects used for dependency injection @var array */
    protected $c = array();

    /**
     * Access Control Constructor
     *
     * @param array $c All class dependencies are imported and verified here
     */
    public function __construct($c)
    {
        $this->c = $c;
    }
    
    public function site(){
        $this->c->log->core("Plugin System Started");
        $plugins = $this->c->db->select("xesm_plugin", "active=1 AND (init = 'site' OR init = 'all') ORDER BY sort");
        $this->load($plugins);
        $this->c->log->core("Plugin System Initialized : ".print_r($plugins, true));
    
}
    /**
    * Implements a specific hook
    *
    * call_user_func_array(array($class, $method), array($param1, $param2)); //Slow
    * $method->setAccessible(true); //should not be needed, allows system to call private/protected methods
    * might be useful to implements some / does class file exists / method = http://php.net/is_callable
    *
    * @param array The hook that should be run
    */
    public function page($page_name)
    {
        $this->c->log->core("Plugin System Page Started");
        $bind = array(":page" => $page_name);
        $plugins = $this->c->db->select("xesm_plugin", "active=1 AND init LIKE :page ORDER BY sort",$bind);
        $this->load($plugins);
        $this->c->log->core("Plugin System Page Initialized : ".print_r($plugins, true));
    }
    
    public function load($plugins)
    {
        if (isset($plugins) AND is_array($plugins) AND count($plugins)){
            foreach ($plugins as $plugin){
                $plugin_cleaned = $this->c->str->restrict_alphanumeric($plugin["code"]);
                //Include File
                $plugin_file = $this->c->config->dir_public.$this->c->config->dir_plugin.$plugin_cleaned."/init.php";
                if (is_file($plugin_file)){
                    include_once($plugin_file);
                }
                $plugin_class = '\\plugin\\'.$plugin_cleaned.'\\init';
                if (class_exists($plugin_class)){
                    //Include Class
                    $plugin_object = new $plugin_class($this->c);
                    $this->c->log->core("Plugin Loaded : Class ".$plugin_class." + Method : init()");
                } else {
                    $this->c->log->core("Plugin Failed : Class ".$plugin_class." + Method : does not exist");
                }
            }
        }
    }
}
