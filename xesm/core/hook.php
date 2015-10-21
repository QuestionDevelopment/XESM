<?php
/**
* Hook Class
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
* Hook Class
*
* This class allows for the running of non-core code
* within XesmCMS via database definied 'hooks'
*/
class hook
{
    /** Stores class objects used for dependency injection @var array */
    protected $c = array();
    /** Stores all hook for the system @var array */
    private $hook;

    /**
    * Hook Class Constructor
    *
    * @param array $container All class dependencies are imported and verified here
    */
    public function __construct($c)
    {
        $this->c = $c;
    }
    
    public function site(){
        $this->c->log->core("Hook System Started");
        $hooks = $this->c->db->select("xesm_hook", "init = 'site' OR init = 'all' AND state = 1 ORDER BY sort");
        $this->load($hooks);
        $this->c->log->core("Hook System Initialized : ".print_r($hooks, true));
    }
    
   /**
    * Implements a specific hook
    *
    * call_user_func_array(array($class, $method), array($param1, $param2)); //Slow
    * $method->setAccessible(true); //should not be needed, allows system to call private/protected methods
    * might be useful to implements some / does class inc exists / method = http://php.net/is_callable
    *
    * @param array The hook that should be run
    */
    public function page($page_name) {
        $this->c->log->core("Hook Page System = ".$page_name);
        $bind = array(":page" => $page_name);
        $hooks = $this->c->db->select("xesm_hook", "init LIKE :page AND state = 1 ORDER BY sort", $bind);
        $this->load($hooks);
        $this->c->log->core("Hook Page System Initialized : ".print_r($hooks, true));
    }
    
    /**
    * Implements a specific hook
    *
    * call_user_func_array(array($class, $method), array($param1, $param2)); //Slow
    * $method->setAccessible(true); //should not be needed, allows system to call private/protected methods
    * might be useful to implements some / does class inc exists / method = http://php.net/is_callable
    *
    * @param array The hook that should be run
    */
    public function run($hook_name) {
        $this->c->log->core("Hook : ".$hook_name." Started");
        //Are there any hooks under this hookName?
        if(isset($this->hook[$hook_name]) and is_array($this->hook[$hook_name]) && count($this->hook[$hook_name]) > 0){
            //Loop through all hooks that are active for this page
            foreach ($this->hook[$hook_name] as $hook){
                //Include File
                if (isset($hook["file_path"]) AND $hook["file_path"] != ""){
                    $hook_file_path = $this->c->config->dir_public.$this->c->security->inc($hook["file_path"]);
                    if (is_file($hook_file_path)){
                        include_once($hook_file_path);
                    }
                    $this->c->log->core("Hook : File ".$hook_file_path);
                }
                //Include Class
                if (isset($hook["class"]) AND $hook["class"] != "" AND isset($hook["method"]) AND $hook["method"] != ""){
                    $hookObject = new $hook["class"]($this->c);
                    $hookObject->$hook["method"]();
                    $this->c->log->core("Hook : Class ".$hook["class"]." + Method : ".$hook["method"]);
                    //http://stackoverflow.com/questions/273169/how-do-i-dynamically-invoke-a-class-method-in-php
                }
            }
        }
    }
    
    public function load($hooks){
        if (isset($hooks) AND is_array($hooks) AND count($hooks)){
            foreach ($hooks as $hook){
                $hook["hook_point"] = $this->synonym($hook["hook_point"]);
                $this->hook[$hook["hook_point"]][] = array(
                    "file_path" => $hook["file_path"],
                    "class" => $hook["class"],
                    "method" => $hook["method"]
                );
            }
        }
    }
    
    public function add($hook_point, $hook_class = "", $hook_method = "", $hook_file_path = "") {
        $hook_point = $this->synonym($hook_point);
        $this->hook[$hook_point][] = array(
            "file_path" => $hook_file_path,
            "class" => $hook_class,
            "method" => $hook_method
        );
    }
    
    public function synonym($hook_point){
        if ($hook_point == "start"){ $hook_point = "init"; }
        else if ($hook_point == "pre_template"){ $hook_point = "pre_model"; }
        else if ($hook_point == "post_model"){ $hook_point = "pre_controller"; }
        else if ($hook_point == "post_controller"){ $hook_point = "pre_head"; }
        else if ($hook_point == "post_head"){ $hook_point = "pre_view"; }
        else if ($hook_point == "post_view"){ $hook_point = "pre_foot"; }
        else if ($hook_point == "post_template"){ $hook_point == "post_foot"; }
        return $hook_point;
    }
}
