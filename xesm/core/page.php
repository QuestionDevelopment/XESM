<?php
/**
 * Controls the loading of all web pages in xesmCMS
 *
 * @todo
 * - REST state
 * - Debug (perhaps remove mode method + take out session?)
 * - Event Logging
 * - Cache Plugin
 * - Autoload has to work from shell
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
 * Controls the loading of all web pages in xesmCMS
 *
 * This class handles the major workload for all pages loaded
 * in xesmCMS.  Besides the obvious templating aspects it handles
 * the class autoload, sessions, $_REQUEST data, debugging system
 * and managing loading of plugins/css and js.
 */
class page
{
    /** Stores class objects used for dependency injection (reference of above) @var array */
    protected $c = array();
    /** Stores page information @var array */
    public $data = array();
    
    /**
     * Loads Page Dependencies
     *
     * Stores all classes required to run Page in $container
     *
     * @param array $c The array that stores all class dependencies
     */
    public function __construct($c)
    {
        $this->c = $c;
    }

    /**
     * Declares all page data
     *
     * @param $render boolean After initialization do we render page
     */
    public function init($render = true){
        //determine current page data
        $this->data($_SERVER['REQUEST_URI']);
        //first hook into page system
        if ($this->c->config->load_hook == true){ $this->c->hook->run('page'); }
        //load template data
        if (!isset($this->data["bypass"]["template"])){ $this->template(); }
        //load page's simple mvc
        if (!isset($this->data["bypass"]["mvc"])){ $this->mvc(); }
        //load in hook system
        if (!isset($this->data["bypass"]["hook"])){ $this->c->hook->page($this->data["url"]); }
        //load in plugin system
        if (!isset($this->data["bypass"]["plugin"])){ $this->c->plugin->page($this->data["url"]); }
        //should we render the page template data
        if ($render == true){ $this->render(); }
    }

    /**
     * Determines the uri of the current page request
     *
     * This is the path (minus the parameters) that the user sees (with index.php appended to all)
     */
    public function data($url)
    {
        $url = parse_url($url);
        if (isset($url["path"]) AND !empty($url["path"])){
            $this->data["url"] = $url["path"];
        }
        $this->data["url"] = ltrim($this->data["url"], '/');
        if ($this->data["url"] == ""){
            //homepage
            $this->data["url"] = "index.php";
        } else {
            $url_data = explode("/",$this->data["url"]);
            if (isset($url_data) AND is_array($url_data)){
                $url_data_count = count($url_data)-1;
            }
            if (empty($url_data[$url_data_count])){
                //last array element is empty = string ends with /
                if ($url_data_count == 2){
                    // account/register/ = account/register.php
                    if ($url_data[1] == "create") { $url_data[1] = "edit"; };
                    $this->data["url"] = $url_data[0]."/".$url_data[1].".php";      
                } else if ($url_data_count == 3){
                    // account/edit/7/ = account/edit.php?id=7
                    if (is_numeric($url_data[2])){
                        $_REQUEST["id"] = $url_data[2];
                        $this->data["url"] = $url_data[0]."/".$url_data[1].".php";   
                    } else {
                        $this->data["url"] .= "index.php";
                    }
                } else {
                    $this->data["url"] .= "index.php";
                }                
            }            
        }
        if (isset($url["query"]) AND !empty($url["query"])){
            $this->data["parameters"] = $url["query"];
        } else {
            $this->data["parameters"] = "";
        }
        $this->data["page"] = basename($this->data["url"]);
        $this->c->log->core("Page : data : Variables Declared [ ".print_r($this->data,true)." ]");
    }
    
    /**
     * Determines head and foot to load page
     */
    public function template(){        
        //determine template name
        if (isset($this->c->config->template)){
            $this->data["template"]["name"] = $this->c->config->template;
        } else {
            $this->data["template"]["name"] = "default";
        }
        
        //Determine head and foot
        if ($this->c->config->verify_head_foot == true AND is_file($this->c->config->dir_public.$this->c->config->dir_template.$this->data["template"]["name"]."/head.php") == false){
            //Unrecoverable error - we can not not proceed without a head file
            $this->c->log->core("Page : template : Template Head Not Valid [ ".$this->data["template"]["head"]." ]");
            exit("Template Error : #111423");
        } else if ($this->c->config->verify_head_foot == true AND is_file($this->c->config->dir_public.$this->c->config->dir_template.$this->data["template"]["name"]."/foot.php") == false){
            //Unrecoverable error - we can not not proceed without a foot file
            $this->c->log->core("Page : template : Template Foot Not Valid [ ".$this->data["template"]["foot"]." ]");
            exit("Template Error : #222454");
        } else {
            $this->data["template"]["head"] = $this->c->config->dir_public.$this->c->config->dir_template.$this->data["template"]["name"]."/head.php";
            $this->data["template"]["foot"] = $this->c->config->dir_public.$this->c->config->dir_template.$this->data["template"]["name"]."/foot.php";
        }
    }

    /**
     * Determines model / view / controller to load page
     */
    public function mvc()
    {
        //Determine what model to use (if any / this is an optional template item)
        if (is_file($this->c->config->dir_public.$this->c->config->dir_model.$this->data["url"]) == true){
            $this->data["template"]["model"] = $this->c->config->dir_public.$this->c->config->dir_model.$this->data["url"];
        }

        //Determine what view to use
        $this->c->log->core("Page : template : Default View = ".$this->c->config->dir_public.$this->c->config->dir_view.$this->data["url"]." ]");
        if (is_file($this->c->config->dir_public.$this->c->config->dir_view.$this->data["url"]) == false){
            //If we do not have a have a valid view we will default to the 404 view
            if (is_file($this->c->config->dir_public.$this->c->config->dir_view."404.php") == false){
                //If we do not have a 404 create we will load the index
                if (is_file($this->c->config->dir_public.$this->c->config->dir_view."index.php") == false){
                    //If we do not have a valid index view then we have failed at life
                    $this->c->log->core("Page : template : Template View Not Valid + No 404 + No index.php [ ".$this->data["template"]["view"]." ]");
                    exit("Template Error : #333525");
                } else {
                    $this->data["status"] = 404;
                    $this->data["template"]["view"] = $this->c->config->dir_public.$this->c->config->dir_view."index.php";
                    $this->c->log->core("Page : template : Template View Not Valid + No 404 = index loaded [ ".$this->data["template"]["view"]." ]");
                }
            } else {
                $this->data["status"] = 404;
                $this->data["template"]["view"] = $this->c->config->dir_public.$this->c->config->dir_view."404.php";
                $this->c->log->core("Page : template : Template View Not Valid = 404 loaded [ ".$this->data["template"]["view"]." ]");
            }
        } else {
            $this->data["status"] = 200;
            $this->data["template"]["view"] = $this->c->config->dir_public.$this->c->config->dir_view.$this->data["url"];
        }

        //Determine what controller to use (if any / this is an optional template item)
        if (is_file($this->c->config->dir_public.$this->c->config->dir_controller.$this->data["url"]) == true){
            $this->data["template"]["controller"] = $this->c->config->dir_public.$this->c->config->dir_controller.$this->data["url"];
        } else if (is_file($this->c->config->dir_public.$this->c->config->dir_controller."404.php") == true){
            $this->data["template"]["controller"] = $this->c->config->dir_public.$this->c->config->dir_controller."404.php";
        } else if (is_file($this->c->config->dir_public.$this->c->config->dir_controller."index.php") == true){
            $this->data["template"]["controller"] = $this->c->config->dir_public.$this->c->config->dir_controller."index.php";
        }

        $this->c->log->core("Page : template : Template Variables Declared [ ".print_r($this->data["template"],true)." ]");
    }

    /**
     * Generates the html for page load by
     * including template variables
     */

    public function render()
    {
        $c = $this->c;
        if ($c->config->load_hook == true) {
            //render using hook system
            $c->hook->run('pre_model');
            if (isset($this->data["template"]["model"])) {
                include_once($this->data["template"]["model"]);
            }
            $c->hook->run('pre_controller');  //aka postmodel
            if (isset($this->data["template"]["controller"])) {
                include_once($this->data["template"]["controller"]);
            }
            $c->hook->run('pre_head'); //aka postcontroller
            include_once($this->data["template"]["head"]);
            $c->hook->run('pre_view'); //aka posthead
            include_once($this->data["template"]["view"]);
            $c->hook->run('pre_foot'); //aka postview
            include_once($this->data["template"]["foot"]);
            $c->hook->run('post_foot');
        } else {
            //render without hook system
            if (isset($this->data["template"]["model"])) {
                include_once($this->data["template"]["model"]);
            }
            if (isset($this->data["template"]["controller"])) {
                include_once($this->data["template"]["controller"]);
            }
            include_once($this->data["template"]["head"]);
            include_once($this->data["template"]["view"]);
            include_once($this->data["template"]["foot"]);
        }
    }

    public function cache_bust($type = 'random', $url_variable = "?"){
        if ($type == 'css' || $type == 'js'){
            $config_variable = "cache_bust_".$type;
            if ($this->c->config->$config_variable == "random"){
                $bust_string = $this->c->str->random();
            } else {
                $bust_string = $this->c->config->$config_variable;
            }
        } else {
            $bust_string = $this->c->str->random();
        }
        return $url_variable.'cache_bust='.$bust_string;
    }
}
