<?php

namespace system\query;

class xesm
{
    protected $system;
    protected $name;

    public function __construct($system)
    {
        $this->system = $system;
        $class = get_called_class();
        $this->name = substr($class, strrpos($class, '\\') + 1);
    }
    
    
    public function __call($method, $args) {
        $method = "xesm_".$method;        
        if (method_exists($this, $method)){
            return call_user_func_array(array($this, $method), $args);
        }
    }

    public function convert($item, $mode){
        $account_array = array();
        foreach ($item->config as $field => $field_data){
            if (isset($field_data["query"]) AND is_array($field_data["query"])){
                $item_modes = $field_data["query"];
            } else {
                $item_modes = array("create","read","read_all","update"); // default values
            }
            if (in_array($mode, $item_modes) AND isset($item->$field)){
                $account_array[$field] = $item->$field;
            }
        }
        return $account_array;
    }

    public function xesm_list($table, $order_by = "sort", $fields = "*")
    {
        return $this->system->run("SELECT ".$fields." FROM ".$table." ORDER BY ".$order_by);
    }

    public function xesm_select($query, $query_field = "id", $fields = "*", $table = "")
    {
        if (empty($table)){ $table = $this->name; }
        if (isset($query) AND !empty($query)) {
            $bind = array(":" . $query_field => strtolower($query));
            $result = $this->system->select($table, $query_field . " = :" . $query_field . " LIMIT 1", $bind, $fields);
        }        
        if (isset($result[0]) AND is_array($result[0]) AND count($result[0])){
            return $result[0];
        } else {
            return array();
        }
    }
    
    public function xesm_read($item, $query = 0, $query_field = "id", $fields = "*", $table = "", $mode = "full")
    {
        $response = array();
        if (empty($table)){ $table = $this->name; }
        if ($query == 0){ $query = $item->id; }
        if (isset($query) AND !empty($query)) {
            $bind = array(":" . $query_field => strtolower($query));
            $result = $this->system->select($table, $query_field . " = :" . $query_field . " LIMIT 1", $bind, $fields);
        }
        if ($mode == "full" AND isset($result[0]) AND is_array($result[0]) AND count($result[0])){           
            $response = $result[0];
            foreach ($item->config as $field => $field_data){
                if ($field_data["type"] == "link"){
                    //get link table data
                    $bind = array(":" . $query_field => strtolower($query));
                    $sql = "SELECT * FROM `".$table."_".$field."` WHERE `".$table."_id` = :id";
                    $result = $this->system->run($sql,$bind);
                    if (isset($result) AND is_array($result) AND count($result)){
                        if (!isset($field_data["field"])){ $field_data["field"] = array("title"); }
                        $field_data["field"][] = "id";
                        
                        $response[$field] = array();
                        foreach ($result as $result_entry){
                            $id = $result_entry["id"];
                            $response[$field][$id] = array();
                            foreach($field_data["field"] as $field_data_entry){
                                $response[$field][$id][$field_data_entry] = $result_entry[$field_data_entry];
                            }
                        }
                    }                    
                } else if ($field_data["type"] == "join"){
                    //get join table data
                    $bind = array(":" . $query_field => strtolower($query));
                    $sql = "SELECT * FROM `".$table."_".$field."` LEFT JOIN `".$field."` ON `".$table."_".$field."`.`".$field."_id` = `".$field."`.`id` WHERE `".$table."_".$field."`.`".$table."_id` = :id";
                    $result = $this->system->run($sql,$bind);
                    if (isset($result) AND is_array($result) AND count($result)){
                        if (!isset($field_data["field"])){ $field_data["field"] = array("title"); }
                        $field_data["field"][] = $field."_id";
                        
                        $response[$field] = array();
                        foreach ($result as $result_entry){
                            $id = $result_entry["id"];
                            $response[$field][$id] = array();
                            foreach($field_data["field"] as $field_data_entry){
                                $response[$field][$id][$field_data_entry] = $result_entry[$field_data_entry];
                            }
                        }
                    }
                }
            }
        }
        return $response;
    }
    
    public function xesm_create($item, $table = "", $mode = "full")
    {
        return $this->xesm_update($item, "id", $table, $mode);
    }
    
    public function xesm_update($item, $query_field = "id", $table = "", $mode = "full")
    {
        $response = array();
        if (empty($table)){ $table = $this->name; }
        
        if ($item->id == 0){
            $item_array = $this->convert($item, 'create');
            $response = $this->system->insert($table, $item_array);
        } else {
            $item_array = $this->convert($item, 'update');
            $response = $this->system->update($table, $item_array, $query_field." = :".$query_field, array(':'.$query_field => $item->$query_field));
        }
        if ($mode == "full"){
            foreach ($item->config as $field => $field_data){
                if (isset($item->$field) AND is_array($item->$field) AND count($item->$field)){
                    if ($field_data["type"] == "link"){
                        $this->xesm_update_link($item, $field);
                    } else if ($field_data["type"] == "join"){
                        $this->xesm_update_join($item, $field);
                    }
                }
            }            
        }
        return $response;
    }
    
    public function xesm_update_join($item, $join_table)
    {
        $this->xesm_delete($item->id, $this->name."_id", $this->name."_".$join_table);
        if (isset($item->$join_table) AND is_array($item->$join_table) AND count($item->$join_table)){
            foreach($item->$join_table as $join_value){
                $insert = array($this->name."_id" => $item->id, $join_table."_id" => $join_value[$join_table."_id"]);
                $this->system->insert($this->name."_".$join_table, $insert, false);            
            }
        }
    }
    
    public function xesm_update_link($item, $link_table)
    {
        $this->xesm_delete($item->id, $this->name."_id", $this->name."_".$link_table);
        if (isset($item->$link_table) AND is_array($item->$link_table) AND count($item->$link_table)){
            foreach($item->$link_table as $link_value){
                $insert = array();
                $insert[$this->name."_id"] = $item->id;
                foreach($item->config[$link_table]["field"] as $insert_field){
                    if (isset($link_value[$insert_field])){
                        $insert[$insert_field] = $link_value[$insert_field];
                    }
                }                
                $this->system->insert($this->name."_".$join_table, $insert);            
            }
        }
    }
    
    public function xesm_delete($query, $query_field = "id", $table = "")
    {
        if (empty($table)){ $table = $this->name; }
        $bind = array(":".$query_field => $query);
        return $this->system->delete($table, $query_field." = :".$query_field, $bind);
    }
}
