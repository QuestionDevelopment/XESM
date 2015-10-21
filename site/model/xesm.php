<?php
namespace site\model;

/**
 * Search system
 */
class xesm
{
    public $c;
    public $name = "";
    public $title = "";
    public $item;
    public $query;
    public $temp = array(); //stores data that cant get assigned to item

    public function __construct($c)
    {
        $this->c = $c;
        $class = get_called_class();
        $this->name = substr($class, strrpos($class, '\\') + 1);
        if (empty($this->title)){ $this->title = ucwords(str_replace("_"," ",$this->name)); }
        $item = "\\system\\item\\".$this->name;
        //include($this->dir_root.$this->c->config->dir_core.$this->c->config->dir_item."xesm.php");
        $this->item = new $item();
        $query = "\\system\\query\\".$this->name;
        //include($this->dir_root.$this->c->config->dir_core.$this->c->config->dir_query."xesm.php");
        $this->query = new $query($this->c->db);
    }

    public function init($data = array())
    {
        if (count($data)) {
            $this->item->assign($data);
        }
    }

    public function assign($data)
    {
        $this->item->assign($data);
    }

    
    public function id()
    {
        return $this->item->id;
    }
    
    public function mode()
    {
        $mode = "new";
        if (isset($this->item->id) AND $this->item->id != 0){
            $mode = "edit"; 
        }
        return $mode;
    }
    
    public function list_id($field)
    {
        return $this->item->list_id($field);
    }
    
    public function query($query_method, $data = array())
    {
        $response = array();
        if (count($data)) {
            $response = $this->query->$query_method($data);
        } else {
            $response = $this->query->$query_method();
        }
        return $response;
    }
    
    public function option($config)
    {
        if (!isset($config["value"])){ $config["value"] = "id"; }
        if (!isset($config["label"]) AND !isset($config["name"])){ $config["name"] = "title"; }
        $option = array();
        if (isset($config["table"])){
            $data = $this->query($config["table"]);        
            if (isset($data) AND is_array($data) AND count($data)){  
                if (isset($config["intro"])){
                    $option[] = array("value" => "", "name" => $config["intro"]); //only for selects             
                }
                foreach ($data as $data_item){
                    $temp_option = array("value" => $data_item[$config["value"]]);
                    if (isset($config["label"])){ $temp_option["label"] = $data_item[$config["label"]]; }
                    if (isset($config["name"])){ $temp_option["name"] = $data_item[$config["name"]]; }
                    
                    if (isset($config["group"]["field"]) AND isset($data_item[$config["group"]["field"]]) AND isset($config["group"]["value"][$data_item[$config["group"]["field"]]])){
                        $temp_option["group"] = $config["group"]["value"][$data_item[$config["group"]["field"]]];
                    }                    
                    $option[] = $temp_option;
                }
            }
        }
        return $option;
    }

    public function load($id = 0, $mode = 'full')
    {
        if ($id == 0){ $id = $this->item->id; }
        if (isset($id) AND $id != false) {
            if ($mode == "full"){
                $item_data = $this->query->xesm_read($this->item, $id);                
            } else {
                $item_data = $this->query->xesm_read_fast($this->item, $id);
            }
            $this->assign($item_data);
        }
    }

    public function validate()
    {
        foreach ($this->item->config as $field => $field_config) {
            if (isset($field_config["validate"]) AND is_array($field_config["validate"]) AND count($field_config["validate"])){
                foreach ($field_config["validate"] as $validation){
                    switch($validation){
                        case 'required' :
                            if (!isset( $this->item->$field ) || empty( $this->item->$field )) {
                                $this->c->status->error($field . ' is a required field and must have a value');
                            }
                            break;
                    }
                }
            }
        }
    }
    
    public function create()
    {
        $this->update();
    }
    
    public function update()
    {
        $response = false;
        $this->validate();
        if ($this->c->status->valid()){
            $response = $this->query->update($this->item);
            if ($response["valid"]){
                if ($this->item->id == 0){
                    $this->item->id = $response["last_insert_id"];
                    $this->c->status->message("New ".$this->title." Successfully Created.");
                } else {
                    $this->c->status->message($this->title." Successfully Edited.");                    
                }
            } else {
                if ($this->item->id == 0){
                    $this->c->status->message("System could not create new ".$this->title.". Please try again.");
                } else {
                    $this->c->status->message("System could not perform ".$this->title." edit. Please try again.");                    
                }
            }            
        }
        return $response;
    }
    

    public function delete($confirmation)
    {
        if (isset($confirmation["confirm"]) AND $confirmation["confirm"] == "true"){
            $response = $this->query->delete($this->item->id);
            if ($response["valid"]){
                $this->c->status->message("Your account was successfully deleted.");
                $this->c->account->logout();
            } else {
                $this->c->status->error("There was an error deleting your account.  Please try again.");
            }
        } else {
            $this->c->status->error("You must check off that you agree to the deletion terms.");
        }
    }
    
    public function output()
    {
        $this->item->output();
    }
}
