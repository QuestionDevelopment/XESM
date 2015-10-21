<?php
/**
 * Creates the container to store all system classes
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
 * Creates the container to store all system classes
 *
 * Manages all instances of objects in one location so they
 * can be easily used through out system
 */

class container {
    /** Stores declared functions @var array */
    protected $function=array();
    /** Stores initialized class objects @var array */
    protected $container=array();
    /** Stores any classes that need to be reinitialized when called @var array */
    protected $factory=array();
    /** Stores declared functions dynamically declared (xesm_container table) @var array */
    protected $xesm_container=array();
    
    /**
     * Adds the class to the container system
     *
     * This is one of php's magic functions.  It is called whenever an
     * undeclared variable is set
     *
     * @param string $name The name of the variable called
     * @param function $function The function that will generate the object
     */
    public function __set($name, $function)
    {
        $this->assign($name, $function);
    }

    /**
     * Retrieves the class from the container system
     *
     * This is one of php's magic functions.  It is called whenever an
     * undeclared variable is called
     *
     * @param string $name The name of the object being called
     * @return object The class object
     */    
    public function __get($name)
    {
        return $this->load($name);
    }
    
    /**
     * Method that assigns function to container system
     *
     * @param string $name The name of the variable called
     * @param function $function The function that will generate the object
     */
    public function assign($name, $function)
    {
        $this->function[$name]=$function;
    }   

    /**
     * Retrieves the class from the container system
     *
     * This is one of php's magic functions.  It is called whenever an
     * undeclared variable is called
     *
     * @param string $name The name of the object being called
     * @return object The class object
     */
    public function load($name)
    {
        if (in_array($name, $this->factory)){
            //returns new instance every time (declared as factory)
            return $this->function[$name]($this);
        } else if (isset($this->container[$name]) AND is_object($this->container[$name])){
            //returns existing instance (if it exists)
            return $this->container[$name];
        } else if (isset($this->function[$name])){
            //creates new instance (it has been created yet)
            $this->container[$name] = $this->function[$name]($this);
            return $this->container[$name];
        } else if (isset($this->xesm_container[$name]) AND !empty($this->xesm_container[$name])){
             //creates new instance (it has been created yet) for any class called in from database (xesm_container)
            $this->container[$name] = new $this->xesm_container[$name]($this);
            return $this->container[$name];
        }  else {
            //dynamically tries to load the class call (if not declared) from model folder
            $file_model = "undeclared";
            if (!is_object($this->container["config"])) {
                $this->container[$name] = $this->function[$name]($this);
            }
            $file_model = $this->container["config"]->dir_public.$this->container["config"]->dir_model.$name.".php";
            $file_xesm = $this->container["config"]->dir_public.$this->container["config"]->dir_model."xesm.php";

            if (is_file($file_xesm)) {
                include_once($file_xesm);
            }
            if (is_file($file_model)){
                include_once($file_model);
                $class_name = '\\site\\model\\'.$name;
                if (class_exists($class_name)){
                    $this->container[$name] = new $class_name($this);
                    return $this->container[$name];
                }
            }
            //throw new \InvalidArgumentException(sprintf('Identifier "%s" is not defined.', $name));
        }
    }
    
    /**
     * Assigns one of the container objects to be a factory
     *
     * This means that a new instance of the object will be
     * created every time the object is called
     *
     * @param string $name The name of the object to become a factory
     */    
    public function factory($name)
    {
        $this->factory[] = $name;
    }

    /**
     * Assigns all classes to the container that reside in the
     * xesm_container database table
     */
    public function xesm_container()
    {
        if (!isset($this->container["db"]) || !is_object($this->container["db"])){
            $this->container["db"] = $this->function["db"]($this);
        }
        $results = $this->container["db"]->select("xesm_container","state = 1 ORDER BY sort");
        if (isset($results) AND is_array($results) AND count($results) > 0){
            foreach ($results as $xesm_container){
                $this->xesm_container[$xesm_container["title"]] = (string)$xesm_container["class"];
            }
        }
    }
}
