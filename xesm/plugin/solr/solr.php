<?php
/**
 * Solr Class
 *
 * @version 0.9
 * @package xesm
 * @subpackage plugin
 * @category class
 *
 * @author Justin Campo <admin@limberCMS.com>
 * @license MIT http://opensource.org/licenses/MIT
 */
namespace plugin\solr;
/*
 * Solr Creation Class
 * 
 * This class interacts with solr
 * 
 */
class solr {
    public $server; //solr url
    public $handler; //solrs request handler
    public $core; //which index you are talking to
    public $query; //query string
    public $query_original; //query original string
    public $sku = false; //is this query a sku (boolean)
    public $category; // filter by category
    public $facets = array(); //implement facets
    public $mode = "broad"; //narrow
    public $sort;
    public $page = 1; //which page are we viewing
    public $page_results = 24; //number of results per page
    public $fields;
    public $result_format = "json";

    /**
     * Solr Class Constructor
     *
     * @param array $config All configuration settings are imported and verified here
     */
    public function __construct($config)
    {
        if (isset($config->solr_url)){ $this->server($config->solr_url); }
    }
    
    public function config($config)
    {
        if (isset($config["server"])){ $this->server($config["server"]); }
        if (isset($config["core"])){ $this->core($config["core"]); }
        if (isset($config["handler"])){ $this->handler($config["handler"]); }
        if (isset($config["query"])){ $this->query($config["query"]); }
    }
    
    public function server($server)
    {
        $this->server = $server;
    }
    
    public function core($core)
    {
        $this->core = $core;
    }
    
    public function handler($handler)
    {
        $this->handler = $handler;
    }
    
    public function query($query)
    {
        $this->query_original = $query;
        $query = trim($query);
        $query = strtolower($query);
        $this->query = $query;
    }
    
    public function url()
    {
        $url = "";
        if (!empty($this->server) AND !empty($this->core) AND !empty($this->handler)) {
            $url = $this->server.$this->core."/".$this->handler."?";
            $data = array();            
            $data["q"] = $this->query;
            $data["wt"] = $this->result_format;            
            $url .= http_build_query($data);
        }
        return $url;
    }
    
    public function result()
    {
        $result = array();
        $url = $this->url();
        if (!empty($url)){            
            $result = file_get_contents($url);
            echo $result;
            exit();
            if ($this->result_format == "php"){
                $result = unserialize($result);
            } elseif ($this->result_format == "json"){
                $result = json_decode($result);
            }
        }
        return $result;
    }
}
