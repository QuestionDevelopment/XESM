<?php

namespace system\item;

class xesm
{
    /**
     * MVC Class Constructor
     *
     * @param array $account_data Account Data
     */
    public function __construct($data = array())
    {
        $this->assign($data);
    }

    public function assign($data)
    {
        if (is_array($data) AND count($data)){
            foreach ($data as $key => $value){
                if (isset($this->config[$key]["type"])){
                    if ($this->config[$key]["type"] == "string"){
                        $this->$key = preg_replace("/[^a-zA-Z0-9\'@!?#=:$\/\-_@. ]/", "", $value);
                    } else if ($this->config[$key]["type"] == "char") {
                        $this->$key = preg_replace("/[^a-zA-Z\s]/", "", $value);
                    } else if ($this->config[$key]["type"] == "alphanumeric") {
                        $this->$key = preg_replace("/[^a-zA-Z0-9\s]/", "", $value);
                    } else if  ($this->config[$key]["type"] == "int"){
                        $this->$key = (int)$value;
                    } else if  ($this->config[$key]["type"] == "boolean"){
                        if ($value === 1){ $this->$key = 1; }
                        else { $this->$key = 0; }
                    } else if  ($this->config[$key]["type"] == "date"){
                        if (($timestamp = strtotime($value)) !== false) {
                            $this->$key = date("Y-m-d", $timestamp);
                        }
                    } else if  ($this->config[$key]["type"] == "datetime"){
                        if (($timestamp = strtotime($value)) !== false) {
                            $this->$key = date("Y-m-d H:i:s", $timestamp);
                        }
                    } else if ($this->config[$key]["type"] == "link" || $this->config[$key]["type"] == "join"){
                        if (is_array($value)){
                            $item_data = array();
                            foreach ($value as $assign_key => $assign_data){
                                if (isset($assign_key) AND !empty($assign_key) AND is_array($assign_data) AND count($assign_data)){
                                    $item_data[] = $assign_data;
                                } else if (is_numeric($assign_data)){
                                    if ($this->config[$key]["type"] == "link"){
                                        $item_data[] = array("id" => $assign_data);
                                    } else {
                                        $item_data[] = array($key."_id" => $assign_data);                                        
                                    }
                                }
                            }
                            $this->$key = $item_data;
                        }
                    }
                }
            }
        }
    }
    
    public function list_id($field)
    {
        $response = array();
        if(isset($this->$field) AND is_array($this->$field) AND count($this->$field)){
            if ($this->config[$field]["type"] == "join"){ $field_name = $field."_id"; }
            else { $field_name = "id"; }
            
            foreach ($this->$field as $field_data){
                if (isset($field_data[$field_name]) AND !empty($field_data[$field_name])){
                    $response[] = $field_data[$field_name];
                }
            }            
        }
        return $response;
    }
    
    public function output()
    {
        $output = get_object_vars($this);
        foreach ($output as $key => $value){
            if ($key != "config"){
                echo '<div>';
                echo '<span style="font-weight:bold;">'.$key.'</span> : ';
                if (is_array($value)){
                    $array_counter = count($value);
                    if ($array_counter > 0){
                        echo "[".$array_counter."]<div style='clear:both;'></div>";
                        foreach ($value as $key2 => $value2){
                            echo "<div style='margin-left:20px;'>";
                            echo '<span style="font-weight:bold;">'.$key2.'</span> : ';
                            if (count($value2)){
                                print_r($value2);
                            } else {
                                echo "empty";
                            }                            
                            echo "</div>";
                        }
                    } else {
                        echo "empty";
                    }
                }
                else { echo $value; }
                echo '</div>';
            }
        }
    }
}
