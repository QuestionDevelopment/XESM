<?php
/**
 * Form Creation Class
 *
 * @version 0.9
 * @package limber
 * @subpackage object
 * @category class
 *
 * @author Josh Cunningham <josh@joshcanhelp.com>
 * @author Justin Campo <admin@limberCMS.com>
 * @license MIT http://opensource.org/licenses/MIT
 */
namespace xesm\plugin;
/*
 * Form Creation Class
 * 
 * This class dynamically creates forms.  Below are the allowed values
 * 
 */
class form {
    /** Stores all form data @var array */
    private $data = array();
    /** String appended to various parts of the form @var string */
    private $prefix = "wine_";
    /** Stores all form attribute data */
    private $form = array(
        "action" => array(),
        "auto_class" => array("default" => true),
        "auto_id" => array("default" => true),
        "auto_tab_index" => array("default" => true),
        "cache" => array(),
        "captcha" => array(),
        "css_file" => array(),
        "debug" => array("default" => false),
        "editor" => array("default" => "/template/shelter/ckeditor/ckeditor.js"),
        "enctype" => array("default" => "application/x-www-form-urlencoded"),
        "honeypot" => array(),
        "id" => array(),
        "items" => array(), //system
        "js" => array(), //system
        "markup" => array("default" => "html"),
        "method" => array("default" => "post"),
        "reset" => array(),
        "submit" => array(),
        "title" => array(),
        "validate" => array("default" => true),
    );


    /** All input validation methods available @var array */
    private $validations = array("email","phone","zip","alpha","numeric","alpha_numeric","alpha_numberic_space","date","dateTime","time","url");
    /** Form attributes available @var array */
    private $form_attributes = array
    ("action","method","enctype","markup","title","id","honeypot","validate","cache","submit","reset","captcha","cssfile","autoclass","autotabindex","js
","items","debug","editor");
    /** All form item attributes available + system assigned @var array */
    private $item_attributes = array
    ("type","name","label","id","value","placeholder","maxlength","minlength","tabindex","autofocus","required","option","class","disabled","readonly","
onkeyup","onkeydown","onchange","onfocus","onmouseover","onmouseout","onclick","validate","equals","render_method","help");
    /** All form item attributes available + system assigned @var array */
    private $item_options = array
    ("id","name","value","tabindex","selected","checked","class","autofocus","required","disabled","readonly","onchange","onfocus","onmouseover","onmous
eout","onclick");
    /** All available form item types @var array */
    private $item_types = array
    ("text","file","hidden","password","checkbox","radio","select","button","textarea","h1","h2","h3","h4","h5","h6","div","editor");
    /** Rendering types for all item types (keys sync to $this->item_types) @var array */
    private $item_types_render_method = array
    ("input","file","hidden","input","option","option","select","button","open_close","text","text","text","text","text","text","text","editor");

    /*
     * Constructor to set basic form attributes
     * 
     * @param var $user_settings User defined settings, can be a string or array
     */
    public function __construct($user_settings = array())
    {
        //setup data struction
        $this->data["form"] = array();
        $this->data["form"]["items"] = 0;
        $this->data["form"]["editor"] = $this->editor;
        $this->data["item"] = array();
        $this->data["warning"] = array();
        $this->data["error"] = array();

        //save settings
        $this->config($user_settings);
    }

    public function config($user_settings){
        //user is allowed to send a string if they just want to set the action
        if (!is_array($user_settings)){
            $temp = array();
            $temp["action"] = $user_settings;
            $user_settings = $temp;
            unset($temp);
        }

        //insert data
        if (is_array($user_settings) AND count($user_settings) > 0) {
            foreach ($user_settings as $user_setting => $user_setting_value) {
                $this->add_attribute($user_setting, $user_setting_value);
            }
        }
    }

    /*
     * Load form attributes post initialization
     *
     * @param var $user_settings User defined settings, can be a string or array
     */
    public function add_attributes($user_settings = array())
    {
        if (is_array($user_settings) AND count($user_settings) > 0) {
            foreach ($user_settings as $user_setting => $user_setting_value) {
                $this->add_attribute($user_setting, $user_setting_value);
            }
        }
    }

    /*
     * Set attributes for the form and special fields
     * 
     * @param string $key The name of the form attribute to edit
     * @param string $value The value of the form attribute to edit
     */
    public function add_attribute($key, $val)
    {
        if ($this->val($key) && $this->val($val)){ $this->data["form"][$key] = $val; }
    }

    /*
     * Adds an item to the form
     * 
     * @param var $item_settings User definied items settings, can be a string or array
     */
    public function add_item($item_settings = array())
    {
        //user is allowed to send a string if they just want to declare the item name
        if (!is_array($item_settings) && $this->val($item_settings)){
            $temp = array();
            $temp["label"] = $item_settings;
            $temp["name"] = preg_replace("/[^a-zA-Z0-9\s]/", "", $item_settings);
            $temp["type"] = "text";
            $item_settings = $temp;
        }

        //increment form item count
        $this->data["form"]["items"]++;

        //insert items
        if (is_array($item_settings) AND count($item_settings) > 0){
            foreach ($item_settings as $item_setting => $item_setting_value){
                $this->set_item_attribute($item_setting, $item_setting_value);
            }
        }
    }

    /*
     * Add multiple items to form
     * 
     * @param array items The items to add to the form
     */
    public function add_items($items = array())
    {
        if (is_array($items) AND count($items) > 0){
            foreach ($items as $item){
                $this->add_item($item);
            }
        }
    }

    /*
     * Set attributes for the form and special fields
     * 
     * @param string $key The name of the form attribute to edit
     * @param string $value The value of the form attribute to edit
     */
    function set_item_attribute($key, $val, $item = ''){
        //determine what item we are editing (empty $item means top of stack one)
        if ($item == ''){
            $item_key = $this->data["form"]["items"]-1;
        } else {
            $item_data_loop = 0;
            foreach ($this->data["item"] as $item_data){
                if ($item_data["name"] == $item || $item_data["label"] == $item){
                    $item_key = $item_data_loop;
                    break;
                }
                $item_data_loop++;
            }
        }
        if (is_int($item_key) && $this->val($key) && $this->val($val)){
            $this->data["item"][$item_key][$key] = $val;
        }
    }

    /*
     * Validates form data
     * 
     * Validation should be done before rendering form data.  The render method assumes
     * that the $this->data is properly formatted and only contains valid data so it is 
     * important to sanitize everything perfectly here.
     */
    private function validate()
    {
        //Make sure required form data has at least an empty value to trigger validation
        $required_from_attributes = array("action", "method", "enctype", "markup");

        //cache system requires form id
        if (isset($this->data["form"]["cache"])){ $required_from_attributes[] = "id"; }

        foreach ($required_from_attributes as $required_from_attribute){
            if (!isset($this->data["form"][$required_from_attribute])){
                $this->data["form"][$required_from_attribute] = "";
                $this->data["warning"][] = "Required form attribute [". $required_from_attribute ."] left blank.";
            }
        }

        //Validate form data
        foreach($this->data["form"] as $form_attribute => &$val){
            switch ($form_attribute) :
                case 'honeypot':
                case 'validate':
                case 'auto_tab_index':
                case 'auto_class':
                case 'js':
                    if ($val !== true && $val !== false){
                        if ($this->val($val)){
                            $val = true;
                            $this->data["warning"][] = "Form attribute [". $form_attribute ."] is a boolean value and should be true or false.  System assigned : true.";
                        } else {
                            $val = false;
                            $this->data["warning"][] = "Form attribute [". $form_attribute ."] is a boolean value and should be true or false.  System assigned : false.";
                        }
                    }
                    break;
                case 'id':
                    if (preg_match('/\s/',$val)){
                        $this->data["error"][] = "Form id has spacing in it";
                    }
                    break;
                case 'method':
                    if (strtolower($val) != "get" && strtolower($val) != "post"){
                        $this->data["warning"][] = "Form attribute [". $form_attribute ."] must be set to 'get' or 'post'.  System assigned default value : post.";
                        $val = "post";
                    }
                    break;
                case 'markup':
                    if (strtolower($val) != "xhtml" && strtolower($val) != "html"){
                        $this->data["warning"][] = "Form attribute [". $form_attribute ."] must be set to 'html' or 'xhtml'.  System assigned default value : html.";
                        $val = "html";
                    }
                    break;
                case 'enctype':
                    if (strtolower($val) != "multipart/form-data" && strtolower($val) != "application/x-www-form-urlencoded"){
                        $this->data["warning"][] = "Form attribute [". $form_attribute ."] must be set to 'multipart/form-data' or 'application/x-www-form-urlencoded'.  System assigned default value : application/x-www-form-urlencoded.";
                        $val = "application/x-www-form-urlencoded";
                    }
                    break;
                case 'cache':
                    if ($this->val($val)){
                        if (!is_writable($val)){
                            $val = false;
                            $this->data["warning"][] = "Cache folder is not writeable. Cache system has been disabled.";
                        }
                    }
                    break;
                default:
                    if (!in_array($form_attribute, $this->form_attributes)){
                        unset($this->data["form"][$form_attribute]);
                    }
                    break;
            endswitch;
        }

        //If form has cache enabled it must have an id
        if ($this->val($this->data["form"]["cache"])){
            if ($this->val($this->data["form"]["id"]) == false){
                $this->form["error"][] = "Form is utilizing cache.  Form id is required.";
            }
        }

        $item_name_list = array();
        $this->data["form"]["item_id_list"] = array();
        $item_tab_index_list = array();
        $autofocus_count = 0;
        $item_count = 1;
        $auto_tab_index_count = 1;
        //Loop through and check all item data
        foreach ($this->data["item"] as &$item){
            //validates type and assigns render method for this item
            $item_type_key = array_search($item["type"], $this->item_types);
            if ($item_type_key === false){
                $item["type"] = "text";
                $item["render_method"] = "input";
            } else {
                $item["render_method"] = $this->item_types_render_method[$item_type_key];
                if ($item["type"] == "editor"){ $this->data["form"]['js'] = true; }
            }

            //determine what attributes are required/banned for each item type
            if ($item["render_method"] == "input"){
                $required_item_attributes = array("type","name");
                $prohibited_item_attributes = array("option");
            } else if ($item["render_method"] == "hidden"){
                $required_item_attributes = array("type","name");
                $prohibited_item_attributes = array("option","tabindex","placeholder","minlength","maxlength","autofocus","disabled","readonly","onfocus","onkeyup","onkeydown","onchange","onclick","onmouseover","onmouseout");
            } else if ($item["render_method"] == "file"){
                $required_item_attributes = array("type","name");
                $prohibited_item_attributes = array("option","maxlength","minlength","placeholder","value","onkeydown","onkeyup","validate","equals");
            } else if ($item["render_method"] == "text"){
                $required_item_attributes = array("type","value");
                $prohibited_item_attributes = array("name","placeholder","maxlength","minlength","option","tabindex","label","autofocus","required","disabled","readonly","onfocus","onkeyup","onkeydown","onchange","validate","equals");
            } else if ($item["render_method"] == "open_close"){
                $required_item_attributes = array("type","name");
                $prohibited_item_attributes = array("placeholder","option");
            } else if ($item["render_method"] == "button"){
                $required_item_attributes = array("type","name");
                $prohibited_item_attributes = array("placeholder","maxlength","minlength","option","required","readonly","equals","validate","onkeydown","onkeyup","autofocus");
            } else if ($item["render_method"] == "option"){
                $required_item_attributes = array("type","option");
                $prohibited_item_attributes = array("name","placeholder","maxlength","minlength","value","tabindex","disabled","readonly","onfocus","onkeyup","onkeydown","onchange","onclick","onmouseover","onmouseout","equals","required","validate");
            } else if ($item["render_method"] == "select"){
                $required_item_attributes = array("type","name","option");
                $prohibited_item_attributes = array("placeholder","maxlength","minlength");
            } else if ($item["render_method"] == "editor"){
                $required_item_attributes = array("type","name","id");
                $prohibited_item_attributes = array("placeholder","onmouseover","onmouseout","onclick","maxlength","minlength","option","disabled","readonly","onfocus","onkeyup","onkeydown","onchange");
            }

            //Make sure required item data has at least an empty value to trigger validation
            foreach ($required_item_attributes as $required_item_attribute){
                if (!isset($item[$required_item_attribute])){
                    $this->data["form"][$required_item_attribute] = "";
                    $this->data["error"][] = "Required item attribute [". $required_item_attribute ."] on item [" . $item_count . "] left blank.";
                }
            }

            //Make sure no banned item attributes are present
            foreach ($prohibited_item_attributes as $prohibited_item_attribute){
                if (isset($item[$prohibited_item_attribute])){
                    unset($item[$prohibited_item_attribute]);
                    $this->data["warning"][] = "Prohibited item attribute [". $prohibited_item_attribute ."] with type [". $item["type"] . "] on item [" . $item_count . "] : Attribute removed.";
                }
            }

            //Add auto tabindex if tabindex is turned on and we are not displaying text based item and its not a option based item
            if (isset($this->data["form"]["auto_tab_index"]) && $this->data["form"]["auto_tab_index"] == true){
                $do_not_apply_auto_tab_index = array("text","option","hidden");
                if (!in_array($item["render_method"],$do_not_apply_auto_tab_index)){
                    $item["tabindex"] = $auto_tab_index_count;
                    $auto_tab_index_count++;
                }
            }

            foreach ($item as $item_attribute => &$val){
                switch ($item_attribute) :
                    case 'autofocus':
                    case 'required':
                    case 'disabled':
                    case 'readonly':
                        if ($val !== true && $val !== false){
                            if ($this->val($val)){
                                $val = true;
                                $this->data["warning"][] = "Item attribute [". $item_attribute ."] on item [" . $item_count . "] is a boolean value and should be true or false.  System assigned : true.";
                            } else {
                                $val = false;
                                $this->data["warning"][] = "Item attribute [". $item_attribute ."] on item [" . $item_count . "] is a boolean value and should be true or false.  System assigned : false.";
                            }
                        }
                        if ($item_attribute == "autofocus" && $val == true){
                            $autofocus_count++;
                        }
                        if ($item_attribute == "required" && $val == true){
                            $this->data["form"]['js'] = true;
                        }
                        break;
                    case 'maxlength':
                    case 'minlength':
                    case 'tabindex':
                        if (!is_int($val)){
                            //might accidently send value as string
                            if((int)$val > 0){
                                $val = (int)$val;
                                $this->data["warning"][] = "Item attribute [". $item_attribute ."] on item [" . $item_count . "] must be an integer.  System assigned : ". $val . ".";
                            } else {
                                unset($this->data["item"][$item_count-1][$item_attribute]);
                            }
                        } else if ($val <= 0){
                            $this->data["warning"][] = "Item attribute [". $item_attribute ."] on item [" . $item_count . "] must be an integer greater than 0.  System removed.";
                            unset($this->data["item"][$item_count-1][$item_attribute]);
                        }
                        if ($item_attribute == "tabindex" && in_array($val,$item_tab_index_list)){
                            $this->data["warning"][] = "Item attribute [tabindex] on item [" . $item_count . "] was duplicated on a previous item.";
                        } else {
                            $item_tab_index_list[] = $val;
                        }
                        if (($item_attribute == "maxlength" || $item_attribute == "minlength") && $val == true){
                            $this->data["form"]['js'] = true;
                        }
                        break;
                    case 'id':
                        if (preg_match('/\s/',$val)){
                            $this->data["error"][] = "Item [" . $item_count . "] id has spacing in it.";
                        } else if (in_array($val, $this->data["form"]["item_id_list"])){
                            $this->data["error"][] = "Item [" . $item_count . "] id has already been used.";
                        } else {
                            $this->data["form"]["item_id_list"][] = $val;
                        }
                        break;
                    case 'validate':
                        if (!in_array($val, $this->validations)){
                            unset($this->data["item"][$item_count-1][$item_attribute]);
                            $this->data["warning"][] = "Validation assigned to item [" . $item_count . "] was not found in validation list.  System removed.";
                        } else {
                            $this->data["form"]['js'] = true;
                        }
                        break;
                    case 'name':
                        if ($this->val($val, false) == false){
                            $this->data["error"][] = "Item [" . $item_count . "] does not have a name assigned.";
                        } else if (in_array($val, $item_name_list)){
                            $this->data["error"][] = "Item [" . $item_count . "] name has already been used.";
                        } else {
                            $item_name_list[] = $val;
                        }
                        break;
                    case 'class':
                        if (is_array($val)){
                            $temp = array();
                            foreach ($val as $class_name){
                                if ($this->val($class_name)){
                                    $temp[] = $class_name;
                                } else {
                                    $this->data["warning"][] = "Item [" . $item_count . "] was provided with class attribute that contained an empty value.";
                                }
                            }
                            $val = $temp;
                        } else {
                            $val = array();
                            $this->data["warning"][] = "Item [" . $item_count . "] was provided with class attribute but it was not an array.";
                        }
                        break;
                    case 'option':
                        if (is_array($val)){
                            $option_counter = 1;
                            $temp = array();
                            foreach ($val as $option_data){
                                //name is required
                                if ($this->val($option_data["name"], false) == false){
                                    $this->data["error"][] = "Item [" . $item_count . "] option [" . $option_counter . "] name is empty.";
                                }
                                //value
                                if (!isset($option_data["value"])){
                                    $option_data["value"] = "";
                                    $this->data["warning"][] = "Item [" . $item_count . "] option [" . $option_counter . "] value is empty.";
                                }
                                //Add auto tabindex
                                if (isset($this->data["form"]["auto_tab_index"]) && $this->data["form"]["auto_tab_index"] == true && $this->data["item"][$item_count-1]["type"] != "select"){
                                    $option_data["tabindex"] = $auto_tab_index_count;
                                    $auto_tab_index_count++;
                                }
                                foreach ($option_data as $option_data_key => $option_data_value){
                                    switch ($option_data_key) :
                                        case 'id':
                                            if (preg_match('/\s/',$option_data_value)){
                                                $this->data["error"][] = "Item [" . $item_count . "] option [" . $option_counter . "] id has spacing in it.";
                                            } else if (in_array($option_data_value, $this->data["form"]["item_id_list"])){
                                                $this->data["error"][] = "Item [" . $item_count . "] option [" . $option_counter . "] id has already been used.";
                                            } else {
                                                if ($this->data["item"][$item_count-1]["type"] == "select"){
                                                    unset($option_data[$option_data_key]);
                                                } else {
                                                    $this->data["form"]["item_id_list"][] = $option_data_value;
                                                }
                                            }
                                            break;
                                        case 'tabindex':
                                            if ($this->data["item"][$item_count-1]["type"] == "select"){
                                                //select
                                                unset($option_data[$option_data_key]);
                                            } else {
                                                //checkbox + radio
                                                if (!is_int($option_data_value)){
                                                    //might accidently send value as string
                                                    if((int)$option_data_value > 0){
                                                        $option_data["tabindex"] = (int)$option_data_value;
                                                        $this->data["warning"][] = "Item [". $item_count ."] option [" . $option_counter . "] tabindex must be an integer.  System assigned : ". (int)$option_data_value . ".";
                                                    } else {
                                                        unset($option_data["tabindex"]);
                                                    }
                                                } else if ($option_data_value <= 0){
                                                    $this->data["warning"][] = "Item [". $item_count ."] option [" . $option_counter . "] tabindex must be an integer greater than 0.  System removed.";
                                                    unset($option_data["tabindex"]);
                                                }
                                                if (in_array($option_data["tabindex"],$item_tab_index_list)){
                                                    $this->data["warning"][] = "Item attribute [tabindex] on item [" . $item_count . "] was duplicated on a previous item.";
                                                } else {
                                                    $item_tab_index_list[] = $option_data["tabindex"];
                                                }
                                            }
                                            break;
                                        case 'selected':
                                        case 'checked':
                                            if (isset($this->data["item"][$item_count-1]["type"]) && $this->data["item"][$item_count-1]["type"] == "select"){
                                                if ($option_data_value){
                                                    $option_data["selected"] = true;
                                                } else if (isset($option_data["selected"])) {
                                                    unset($option_data["selected"]);
                                                }
                                                if (isset($option_data["checked"])){ unset($option_data["checked"]); }
                                            } else {
                                                if ($option_data_value == true){
                                                    $option_data["checked"] = true;
                                                } else if (isset($option_data["checked"])) {
                                                    unset($option_data["checked"]);
                                                }
                                                if (isset($option_data["selected"])){ unset($option_data["selected"]); }
                                            }
                                            break;
                                        case 'class':
                                            if ($this->data["item"][$item_count-1]["type"] == "select"){
                                                //select
                                                unset($option_data[$option_data_key]);
                                            } else {
                                                //checkbox + radio 
                                                if (is_array($option_data_value)){
                                                    $class_temp = array();
                                                    foreach ($option_data_value as $class_name){
                                                        if ($this->val($class_name)){
                                                            $class_temp[] = $class_name;
                                                        } else {
                                                            $this->data["warning"][] = "Item [" . $item_count . "] option [" . $option_counter . "] was provided with class attribute that contained an empty value.";
                                                        }
                                                    }
                                                    $option_data["class"] = $class_temp;
                                                } else {
                                                    $option_data["class"] = array();
                                                    $this->data["warning"][] = "Item [" . $item_count . "] option [" . $option_counter . "] was provided with class attribute but it was not an array.";
                                                }
                                            }
                                            break;
                                        case 'autofocus':
                                        case 'required':
                                        case 'disabled':
                                        case 'readonly':
                                            if ($this->data["item"][$item_count-1]["type"] == "select"){
                                                //select
                                                unset($option_data[$option_data_key]);
                                            } else {
                                                //checkbox + radio
                                                if (isset($option_data[$option_data_value]) && $option_data[$option_data_value] !== true && $option_data[$option_data_value] !== false){
                                                    if ($this->val($option_data[$option_data_value])){
                                                        $option_data[$option_data_value] = true;
                                                        $this->data["warning"][] = "Item attribute [". $item_attribute ."] on item [" . $item_count . "] option [" . $option_counter . "] is a boolean value and should be true or false.  System assigned : true.";
                                                    } else {
                                                        $option_data[$option_data_value] = false;
                                                        $this->data["warning"][] = "Item attribute [". $item_attribute ."] on item [" . $item_count . "] option [" . $option_counter . "] is a boolean value and should be true or false.  System assigned : false.";
                                                    }
                                                }
                                                if ($option_data_key == "autofocus" && $option_data_value == true){
                                                    $autofocus_count++;
                                                }
                                                if ($option_data_key == "required" && $option_data_value == true){
                                                    $this->data["form"]['js'] = true;
                                                }
                                            }
                                            break;
                                        case 'label':
                                        case 'onkeydown':
                                        case 'onchange':
                                        case 'onfocus':
                                        case 'onmouseover':
                                        case 'onmouseout':
                                        case 'onclick':
                                            if ($this->data["item"][$item_count-1]["type"] == "select"){
                                                unset($option_data[$option_data_key]);
                                            }
                                            break;
                                        default:
                                            if (!in_array($option_data_key, $this->itemOptions)){
                                                unset($option_data[$option_data_key]);
                                            }
                                            break;
                                    endswitch;
                                }
                                $temp[] = $option_data;
                            }
                            $val = $temp;
                        } else {
                            $val = array();
                            $this->data["warning"][] = "Item [" . $item_count . "] was provided with option attribute but it was not an array.";
                        }
                        break;
                    default:
                        if (!in_array($item_attribute, $this->item_attributes)){
                            unset($this->data["item"][$item_count-1][$item_attribute]);
                        }
                        break;
                endswitch;
            }
            $item_count++;
        }

        /**
        //Changed this so if it is not a id of a form item it will make sure that is the actual string
        //Loop through and check all item data
        foreach ($itemEqualsList as $itemEqual){
        if (!in_array($itemEqual, $this->data["form"]["item_id_list"])){
        $this->data["error"][] = "Item equals set to [" . $itemEqual . "] but this is not a valid id.";
        }
        }
         **/

        //Only one autofocus allowed per form
        if ($autofocus_count > 1){
            $this->data["warning"][] = "There have been [" . $autofocus_count . "] autofocus elements declared, maximium 1 allowed.";
        }
    }
    /*
     * Method that gets run if validation is disabled
     * 
     * There are a few things (determing render_method, auto_tab_index) that need to get done which
     * will get skipped if validation is turned off
     */
    function nonvalidate()
    {
        if (isset($this->data["form"]['captcha']) AND $this->data["form"]['captcha']){
            $this->data["form"]['js'] = true;
        }
        $auto_tab_index_count = 1;
        foreach ($this->data["item"] as &$item){
            //assigns render_method for item
            $item_type_key = array_search($item["type"], $this->item_types);
            if ($item_type_key === false){
                $item["type"] = "text";
                $item["render_method"] = "input";
            } else {
                $item["render_method"] = $this->item_types_render_method[$item_type_key];
            }
            //Add auto tabindex
            if (isset($this->data["form"]["auto_tab_index"]) && $this->data["form"]["auto_tab_index"] == true){
                $do_not_apply_auto_tab_index = array("text","option","hidden");
                if (!in_array($item["render_method"],$do_not_apply_auto_tab_index)){
                    $item["tabindex"] = $auto_tab_index_count;
                    $auto_tab_index_count++;
                }
            }
            //determine if form needs to use javascript
            if ((isset($item["required"]) && $item["required"] == true)
                || (isset($item["validate"]) && $item["validate"] == true)
                || (isset($item["equals"]) && $item["equals"] == true)
                || ($item["type"] == "editor")
                || (isset($item["maxlength"]) && $item["maxlength"] == true)
                || (isset($item["minlength"]) && $item["minlength"] == true)){
                $this->data["form"]['js'] = true;
            }

            //store itemidS for js validation
            if (isset($item["id"])){ $this->data["form"]["item_id_list"][] = $item["id"]; }
        }
    }

    /*
     * Creates the form html
     * 
     * @param booleon $html Output the form to the screen or return it
     */
    function render($output = true)
    {
        if (isset($this->data["form"]["cache"])){
            $cacheKey = md5(json_encode($this->data));
            $cache_file = $this->data["form"]["cache"].$cacheKey;
            if (stream_resolve_include_path($cache_file)){
                echo file_get_contents($cache_file);
                exit();
            }
        }
        if (isset($this->data["form"]["debug"]) AND $this->data["form"]["debug"]){
            $html = "";
            $this->validate();
            if (count($this->data["error"]) > 0){
                $html .= "<br/><span style='font-weight:bold;font-size:24px;'>Your form has the following errors:</span><br/><ul>";
                foreach ($this->data["error"] as $error){
                    $html .= "<li>".$error."</li>";
                }
                $html .= "</ul>";
            }
            if (count($this->data["warning"]) > 0){
                $html .= "<br/><span style='font-weight:bold;font-size:24px;'>Your form has the following warnings:</span><br/><ul>";
                foreach ($this->data["warning"] as $warning){
                    $html .= "<li>".$warning."</li>";
                }
                $html .= "</ul>";
            }
            $html .= "<br/><span style='font-weight:bold;font-size:24px;'>The raw form data:</span><br/>";
            $html .= "<pre>".print_r($this->data["form"],true)."</pre>";
            $html .= "<br/><span style='font-weight:bold;font-size:24px;'>The raw form item data:</span><br/>";
            $html .= "<pre>".print_r($this->data["item"],true)."</pre>";
            echo $html;
            exit();
        } else if (isset($this->data["form"]["validate"]) AND $this->data["form"]["validate"] == true){
            $this->validate();
            if (count($this->data["error"]) > 0){
                $html = 'Your form could not be rendered due to the following errors:<br/><ul>';
                foreach ($this->data["error"] as $error){
                    $html .= "<li>".$error."</li>";
                }
                $html .= "</ul>";
                if ($output){ echo $html; }
                else { return $html; }
                exit();
            }
        } else {
            $this->nonvalidate();
        }

        $html = '';
        //css_file
        if ($this->val($this->data["form"]['css_file'])){ $html .= '<link rel="stylesheet" href="'.$this->data["form"]['css_file'].'">'; }
        //jsfile
        if ($this->val($this->data["form"]['jsfile'])){ $html .= '<script src="'.$this->data["form"]['css_file'].'"></script>'; }
        //Title
        if ($this->val($this->data["form"]['title'])){ $html .= '<div class="'.$this->prefix.'title">'.$this->data["form"]['title'].'</div>'; }
        //Container Div
        if ($this->data["form"]['auto_class'] == true){ $html .= '<div id="'.$this->prefix.'container" class="'.$this->prefix.'container">'; }
        //Form tag
        $html .= '<form method="' . $this->data["form"]["method"] . '"';
        if (isset($this->data["form"]['enctype'])){ $html .= ' enctype="' . $this->data["form"]['enctype'] . '"'; }
        $html .= ' action="' . $this->data["form"]['action'] . '"';
        if ($this->val($this->data["form"]['id'])){ $html .= ' id="' . $this->data["form"]['id'] . '"'; }
        if (isset($this->data["form"]['js']) AND $this->data["form"]['js'] == true){ $html .= ' onsubmit="return('.$this->prefix.'validate());"'; }
        $html .= '>';
        //Add Honeypot
        if (isset($this->data["form"]['honeypot']) AND $this->data["form"]['honeypot'] == true){
            $field = array();
            $field["type"] = "text";
            $field["name"] = $this->prefix."honeypot";
            $field["label"] = "Leave blank to send form";
            $field["id"] = $this->prefix."honeypot";
            $field["class"] = array($this->prefix."hidden");
            $field["render_method"] = "input";
            $this->data["item"][] = $field;
            $this->data["form"]["items"]++;
        }
        foreach ($this->data["item"] as $item){
            $value = "";
            $type = "";
            //
            // Start rendering items
            //
            //input,option,open_close,text,editor
            if ($item["render_method"] == "text"){
                //
                // Render text items
                //
                if (isset($item["type"])){
                    $type = $item["type"];
                    unset($item["type"]);
                }
                if (isset($item["value"])){
                    $value = $item["value"];
                    unset($item["value"]);
                }
                $html .= '<'.$type;
                $html .= $this->render_item_attributes($item);
                $html .= '>';
                $html .= $value;
                $html .= '</'.$type.'>';
            } else {
                //Auto assign class
                if (isset($this->data["form"]['auto_class']) AND $this->data["form"]['auto_class'] == true){
                    if (isset($item['class']) AND is_array($item['class'])){
                        $item['class'][] = $this->prefix."input";
                    } else if (isset($item['class'])){
                        $item['class'] = array($item['class'],$this->prefix."input");
                    } else {
                        $item['class'] = array($this->prefix."input");
                    }
                }
                //Wrapper Item Div
                $html .= '<div';
                if ($this->val($item["id"])){ $html .= ' id="'.$item["id"].'_wrapper"'; }
                if (isset($item["class"])){ $html .= $this->render_class($item['class'],"_wrapper"); }
                //Item Label (with end bracket prefix)
                $html .= '>';
                if ($this->val($item["label"])){
                    $html .='<label for="'.$item["id"].'"';
                    if (isset($this->data["form"]['auto_class']) AND $this->data["form"]['auto_class'] == true){ $html .= ' class="'.$this->prefix.'label"'; }
                    $html .='>'.$item["label"].'</label>';
                }

                switch ($item["render_method"]):
                    case 'input':
                    case 'file':
                    case 'hidden':
                        //
                        // RENDER STANDARD INPUTS (.ie text)
                        //
                        $html .= '<input' . $this->render_item_attributes($item);
                        if ($this->data["form"]["markup"] == "xhtml"){ $html .= '/>'; }
                        else { $html .= '>'; }
                        break;
                    case 'open_close':
                    case 'button':
                        //
                        // RENDER INPUTS THAT HAVE OPEN AND CLOSING TAGS (ie. textarea)
                        //
                        if (isset($item["value"])){
                            $value = $item["value"];
                            unset($item["value"]);
                        }
                        $html .= '<'.$item["type"] . $this->render_item_attributes($item) . '>';
                        if ($this->val($value)){ $html .= $value; }
                        $html .= '</'.$item["type"].'>';
                        break;
                    case 'option':
                        //
                        // RENDER OPTION BASED INPUTS (radio + checkbox)
                        //
                        if (isset($item["option"]) AND is_array($item["option"])) {
                            foreach ($item["option"] as $option) {
                                $option["type"] = $item["type"];
                                $html .= $option["label"] . "&nbsp;";
                                $html .= '<input' . $this->render_item_attributes($option);
                                if ($this->data["form"]["markup"] == "xhtml") {
                                    $html .= '/>&nbsp;';
                                } else {
                                    $html .= '>&nbsp;';
                                }
                            }
                        }
                        break;
                    case 'select':
                        //
                        // RENDER SELECT ELEMENT
                        //
                        $html .= '<select';
                        $html .= $this->render_item_attributes($item);
                        $html .= '>';
                        if (isset($item["option"]) AND is_array($item["option"])) {
                            foreach ($item["option"] as $option) {
                                if (isset($option["name"])) {
                                    $name = $option["name"];
                                    unset($option["name"]);
                                }
                                $html .= '<option' . $this->render_item_attributes($option) . '>';
                                $html .= $name;
                                $html .= '</option>';
                            }
                        }
                        $html .= "</select>";
                        break;
                    default:
                        //
                        // DYNAMICALLY CALL FORM ITEM TYPES RENDERED BY METHODS (ie. render_editor)
                        //
                        $dynamic_class = "render_".ucfirst($item["type"]);
                        if (method_exists($this, $dynamic_class)){
                            $html .= $this->$dynamic_class($item);
                        }
                        break;
                endswitch;
            } // end if text item
        } // end foreach item
        $html .= $this->captcha();
        if (isset($this->data["form"]['reset'])){
            $html .= "<div class='".$this->prefix."reset'><input class='".$this->prefix."reset_input' type='reset' name='".$this->data["form"]['reset']."' value='".$this->data["form"]['reset']."'></div>";
        }
        if (isset($this->data["form"]['submit'])){
            $html .= "<div class='".$this->prefix."submit'><input class='".$this->prefix."submit_input' type='submit' name='".$this->data["form"]['submit']."' value='".$this->data["form"]['submit']."'></div>";
        }
        $html .= '</form></div>';
        $html .= $this->javascript();

        if (isset($this->data["form"]["cache"])){
            file_put_contents($cache_file, $html);
        }

        if ($output){ echo $html; }
        else { return $html; }
    }

    /*
     * Creates a string of an input attributes
     * 
     * @param array $arr The classes for the element
     */
    private function render_item_attributes($item_data) {
        $skip_attributes = array("label","option","validate","equals","render_method","minlength");
        $boolean_attributes = array("autofocus","checked","selected","required","disabled","readonly");

        $item_attributes = '';
        foreach ($item_data as $key => $item_attribute){
            if (isset($item_attribute)){
                if (in_array($key, $boolean_attributes)){
                    //must be marked true
                    if ($item_attribute == true) {
                        //this the format that like ' disabled' or ' disabled="disabled"'
                        if ($this->data["form"]["markup"] == "xhtml") {
                            $item_attributes .= ' ' . $key . '="' . $key . '"';
                        } else {
                            $item_attributes .= ' ' . $key;
                        }
                    }
                } else if ($key == "class"){
                    //output the items classes
                    $item_attributes .= $this->render_class($item_attribute);
                } else if (!in_array($key,$skip_attributes)){
                    //this is the standard form like ' name="userValue"'
                    $item_attributes .= ' '.$key.'="'.$item_attribute.'"';
                }
            }
        }
        return $item_attributes;
    }

    /*
     * Creates a string of all classes
     *
     * @param array $arr The classes for the element
     */
    private function render_editor($item) {
        $editor = '<div class="clear '.$this->prefix.'clear"></div>';
        $editor .= '<textarea id="'.$item["id"].'"';
        if (isset($item["class"])){ $editor .= $this->render_class($item['class']); }
        if (isset($item["tabindex"])){ $editor .= ' tabindex="'.$item["tabindex"].'"'; }
        if (isset($item["autofocus"])){
            if ($this->data["form"]["markup"] == "xhtml"){
                $editor .= ' autofocus="autofocus"';
            } else {
                $editor .= ' autofocus';
            }
        }
        $editor .= ' name="'.$item["name"].'">';
        if (isset($item["value"]) AND $item["value"] != ""){
            $item["value"]=stripslashes($item["value"]);
            $item["value"]=str_replace("&","&amp;",$item["value"]);
            $item["value"]=str_replace("<","&lt;",$item["value"]);
            $item["value"]=str_replace(">","&gt;",$item["value"]);
            $editor .= $item["value"];
        }
        $editor .= '</textarea>';
        return $editor;
    }

    /*
     * Creates a string of all classes
     *
     * @param array $arr The classes for the element
     */
    private function render_class($arr = array(), $suffix = '') {
        if (is_array($arr)){
            $html = ' class="';
            foreach ($arr as $class){
                $html .= $class . $suffix . ' ';
            }
            return substr($html, 0, -1).'"';
        } else {
            return '';
        }
    }

    /*
     * Renders all the javascript required for the formradioOption1idWrapper
     */
    private function javascript(){
        if (isset($this->data["form"]['js']) && $this->data["form"]['js']){
            $js = "<script>";
            $js .= "function ".$this->prefix."ApplyError(itemid){var tempElem=".$this->prefix."Elem(itemid);tempElem.className += ' ".$this->prefix."Error';tempElem.focus();}";
            $js .= "function ".$this->prefix."RemoveError(itemid){var tempElem=".$this->prefix."Elem(itemid);if(tempElem.className){tempElem.className.replace( /(?:^|\s)".$this->prefix."Error(?!\S)/ , '' );}}";
            $js .= "function ".$this->prefix."Elem(id){var elem;if(document.getElementById){elem=document.getElementById(id);}";
            $js .= "else if(document.all){elem=document.all[id];}else if(document.layers){elem=document.layers[id];}return elem;}";
            $js .= "var emailRegex = /(.+)@(.+){2,}\.(.+){2,}/;";
            $js .= "var phoneRegex = /(\W|^)[(]{0,1}\d{3}[)]{0,1}[\s-]{0,1}\d{3}[\s-]{0,1}\d{4}(\W|$)/;";
            $js .= "var zipRegex = /^\d{5}$|^\d{5}-\d{4}$/;";
            $js .= "var alphaRegex = /^[a-zA-Z]+$/;";
            $js .= "var numericRegex = /^[0-9]+$/;";
            $js .= "var alpha_numericRegex = /^[a-zA-Z0-9]+$/;";
            $js .= "var alpha_numericSpaceRegex = /^[a-zA-Z0-9 ]+$/;";
            $js .= "var dateRegex = /^\d{2}\/\d{2}\/\d{4}$/;";
            $js .= "var dateTimeRegex = /^[0,1]?\d\/(([0-2]?\d)|([3][01]))\/((199\d)|([2-9]\d{3}))\s[0-2]?[0-9]:[0-5][0-9] (AM|am|aM|Am|PM|pm|pM|Pm)?$/;";
            $js .= "var timeRegex = /^ *(1[0-2]|[1-9]):[0-5][0-9] *(a|p|A|P)(m|M) *$/;";
            $js .= "var urlRegex = /(http|ftp|https):\/\/[\w\-_]+(\.[\w\-_]+)+([\w\-\.,@?^=%&amp;:/~\+#]*[\w\-\@?^=%&amp;/~\+#])?/;";
            $js .= "function ".$this->prefix."Validate(){";
            $editor_js = array();
            //Loop through items
            $render_methods_to_skip = array("text");
            foreach($this->data["item"] as $item){
                if (!in_array($item["render_method"], $render_methods_to_skip)){
                    //store id if editor so it can be rendered
                    if ($item["type"] == "editor"){
                        $editor_js[] = $item["id"];
                    }
                    if ($item["type"] == "radio" || $item["type"] == "checkbox"){
                        foreach ($item["option"] as $option){
                            $js .= $this->javascript_validation($item, $option, "option");
                        }
                    } else {
                        $js .= $this->javascript_validation($item);
                    }
                }
            }
            $js .= "return true;}";;
            //$js .= 'window.addEventListener("load", function (){ '.$this->prefix.'Validate(); });';
            $js .= "</script>";
            //Editor JS
            if (count($editor_js) > 0){
                $js .= '<script src="'.$this->data["form"]["editor"].'"></script>';
                $js .= "<script>";
                foreach ($editor_js as $editor_id){
                    $js .= 'window.addEventListener("load", function (){ CKEDITOR.replace("'.$editor_id.'"); });';
                }
                $js .= "</script>";
            }
            return $js;
        }
    }

    /*
     * Renders all the javascript required for the form
     */
    private function javascript_validation($item, $option = array(), $version = "item"){
        //for option based items we want to use the id + label from the primary item
        if (isset($item["id"]) AND $item["id"] != ""){
            $error_id = $item["id"]."_wrapper";
        }
        if (isset($item["label"]) AND $item["label"] != ""){
            $item_label = $item["label"];
        }
        if ($version == "option"){
            $item = $option;
            if (isset($item["label"]) AND $item["label"] != ""){
                $item_label = $item["label"];
            }
        }
        $js = "";
        if (isset($item["id"]) && isset($item["id"]) != ""){
            //determine best name to use
            if (isset($item_label) and $item_label != ""){
                $name = $item_label;
            } else if (isset($item["name"]) and $item["name"] != ""){
                $name = $item["name"];
            } else if (isset($item["id"]) and $item["id"] != ""){
                $name = $item["id"];
            } else if (isset($item["type"]) and $item["type"] != ""){
                $name = $item["type"];
            }
            //maxlength
            if (isset($item["maxlength"])){
                $js .= "if(".$this->prefix."Elem('".$item["id"]."').value.length > ".$item["maxlength"]."){alert('".$name." exceeds the the maximium length of ".$item["maxlength"]."');";
                if (isset($error_id)) { $js .= $this->prefix."ApplyError('".$error_id."'); return false; } else { ".$this->prefix."RemoveError('".$error_id."'); }"; }
                else { $js .= "}"; }
            }
            //minlength
            if (isset($item["minlength"])){
                $js .= "if(".$this->prefix."Elem('".$item["id"]."').value.length < ".$item["minlength"]."){alert('".$name." does not reach the minimium length of ".$item["minlength"]."');";
                if (isset($error_id)) { $js .= $this->prefix."ApplyError('".$error_id."'); return false; } else { ".$this->prefix."RemoveError('".$error_id."'); }"; }
                else { $js .= "}"; }
            }
            //required
            if (isset($item["required"]) && $item["required"]){
                if (!isset($item["type"])){ //only options do not have a type declared (radio/checkbox)
                    $js .= "if(".$this->prefix."Elem('".$item["id"]."').checked == false){alert('".$name." is a required field and must have a value');";
                    if (isset($error_id)) { $js .= $this->prefix."ApplyError('".$error_id."'); return false; } else { ".$this->prefix."RemoveError('".$error_id."'); }"; }
                    else { $js .= "}"; }
                } else {
                    $js .= "if(".$this->prefix."Elem('".$item["id"]."').value.length == 0){alert('".$name." is a required field and must have a value');";
                    if (isset($error_id)) { $js .= $this->prefix."ApplyError('".$error_id."'); return false; } else { ".$this->prefix."RemoveError('".$error_id."'); }"; }
                    else { $js .= "}"; }
                }
            }
            //equals
            if (isset($item["equals"])){
                if (in_array($item["equals"], $this->data["form"]["item_id_list"])){
                    $equals = $this->prefix."Elem('".$item["equals"]."').value";
                    $equal_error = $name . " must match the value of form item : ".$item["equals"];
                } else {
                    $equals = "'".$item["equals"]."'";
                    $equal_error = $name . " is required to have the value of : ".$item["equals"];
                }
                $js .= "if(".$this->prefix."Elem('".$item["id"]."').value != ".$equals."){alert('".$equal_error."');";
                if (isset($error_id)) { $js .= $this->prefix."ApplyError('".$error_id."'); return false; } else { ".$this->prefix."RemoveError('".$error_id."'); }"; }
                else { $js .= "}"; }
            }
            //validation
            if (isset($item["validate"])){
                if ($item["validate"] == "email"){
                    $js .= "if(emailRegex.test(".$this->prefix."Elem('".$item["id"]."').value) == false){alert('".$name." must be a valid email address');";
                } else if ($item["validate"] == "phone"){
                    $js .= "if(phoneRegex.test(".$this->prefix."Elem('".$item["id"]."').value) == false){alert('".$name." must be a valid phone number [7, 10, 11 digits with or without hypthens]');";
                } else if ($item["validate"] == "zip"){
                    $js .= "if(zipRegex.test(".$this->prefix."Elem('".$item["id"]."').value) == false){alert('".$name." must be a valid zip code [5 or 5-4 digits]');";
                } else if ($item["validate"] == "alpha"){
                    $js .= "if(alphaRegex.test(".$this->prefix."Elem('".$item["id"]."').value) == false){alert('".$name." is only allowed to have alphabetic characters');";
                } else if ($item["validate"] == "numeric"){
                    $js .= "if(numericRegex.test(".$this->prefix."Elem('".$item["id"]."').value) == false){alert('".$name." is only allowed to have numeric characters');";
                } else if ($item["validate"] == "alpha_numeric"){
                    $js .= "if(alpha_numericRegex.test(".$this->prefix."Elem('".$item["id"]."').value) == false){alert('".$name." is only allowed to have alphanumberic characters');";
                } else if ($item["validate"] == "alpha_numberic_space"){
                    $js .= "if(alpha_numericSpaceRegex.test(".$this->prefix."Elem('".$item["id"]."').value) == false){alert('".$name." is only allowed to have alphanumberic characters and spaces');";
                } else if ($item["validate"] == "date"){
                    $js .= "if(dateRegex.test(".$this->prefix."Elem('".$item["id"]."').value) == false){alert('".$name." must be a valid date [XX/XX/XXXX]');";
                } else if ($item["validate"] == "dateTime"){
                    $js .= "if(dateTimeRegex.test(".$this->prefix."Elem('".$item["id"]."').value) == false){alert('".$name." must be a valid date [DD/MM/YY HH:MM AM]');";
                } else if ($item["validate"] == "time"){
                    $js .= "if(timeRegex.test(".$this->prefix."Elem('".$item["id"]."').value) == false){alert('".$name." must be in a valid time format [HH:MM AM]');";
                } else if ($item["validate"] == "url"){
                    $js .= "if(urlRegex.test(".$this->prefix."Elem('".$item["id"]."').value) == false){alert('".$name." must be in a valid url [http://www.example.com]');";
                }
                if (isset($error_id)) { $js .= $this->prefix."ApplyError('".$error_id."'); return false; } else { ".$this->prefix."RemoveError('".$error_id."'); }"; }
                else { $js .= "}"; }
            }
            //captcha
            if (isset($this->data["form"]['captcha']) AND $this->data["form"]['captcha']){
                $captchaReversed = $this->captcha_code(true);
                $js .= "var captchaInput = ".$this->prefix."Elem('".$this->prefix."Captcha').value;";
                $js .= "if (btoa(captchaInput.charAt(0)) != '".base64_encode($captchaReversed[0])."'){ alert('The first characters of your captcha is incorrect'); ".$this->prefix."ApplyError('".$this->prefix."CaptchaWrapper'); return false; } else { ".$this->prefix."RemoveError('".$this->prefix."Captcha'); }";
                $js .= "if (btoa(captchaInput.charAt(1)) != '".base64_encode($captchaReversed[1])."'){ alert('The second characters of your captcha is incorrect'); ".$this->prefix."ApplyError('".$this->prefix."CaptchaWrapper'); return false; } else { ".$this->prefix."RemoveError('".$this->prefix."Captcha'); }";
                $js .= "if (btoa(captchaInput.charAt(2)) != '".base64_encode($captchaReversed[2])."'){ alert('The third characters of your captcha is incorrect'); ".$this->prefix."ApplyError('".$this->prefix."CaptchaWrapper'); return false; } else { ".$this->prefix."RemoveError('".$this->prefix."Captcha'); }";
                $js .= "if (btoa(captchaInput.charAt(3)) != '".base64_encode($captchaReversed[3])."'){ alert('The fourth characters of your captcha is incorrect'); ".$this->prefix."ApplyError('".$this->prefix."CaptchaWrapper'); return false; } else { ".$this->prefix."RemoveError('".$this->prefix."Captcha'); }";
                $js .= "if (btoa(captchaInput.charAt(4)) != '".base64_encode($captchaReversed[4])."'){ alert('The fifth characters of your captcha is incorrect'); ".$this->prefix."ApplyError('".$this->prefix."CaptchaWrapper'); return false; } else { ".$this->prefix."RemoveError('".$this->prefix."Captcha'); }";
            }
        }
        return $js;
    }

    /*
     * Renders all the data for the captcha system
     */
    private function captcha(){
        $html = "";
        $css = "";
        if (isset($this->data["form"]['captcha']) AND $this->data["form"]['captcha']){
            $html .= "<div id='".$this->prefix."_captcha_wrapper'>";
            $html .= "<label for='".$this->prefix."captcha'>".$this->data["form"]['captcha']."</label>";
            $html .= "<div id='".$this->prefix."captcha_text'>";
            $html .= "Please enter the following text in reverse : ";
            $hide_class_array = array();
            $characters = "0123456789abcdefghijklmnopqrstuvwxyz";
            $code_array = str_split($this->captcha_code());
            foreach ($code_array as $code_character){
                //Insert fake spans to try and prevent code scraping
                $faux_spans = rand(1,5);
                $i = 0;
                while ($i < $faux_spans){
                    $html .= "<span class='";
                    $faux_class = "char";
                    for ($p = 0; $p < 6; $p++) {
                        $faux_class .= $characters[mt_rand(0, 35)];
                    }
                    $hide_class_array[] = $faux_class;
                    $html .= $faux_class;
                    $html .= "'>".$characters[mt_rand(0, 35)]."</span>";
                    $i++;
                }
                //Insert real character
                $html .= "<span class='char";
                for ($p = 0; $p < 6; $p++) {
                    $html .= $characters[mt_rand(0, 35)];
                }
                $html .= "'>".$code_character."</span>";
            }
            $html .= "</div>";
            $html .= "<input type='text' id='".$this->prefix."Captcha' name='".$this->prefix."Captcha'>";
            $html .= "</div>";
            $css = "<style>";
            foreach ($hide_class_array as $hide_class_item){
                $css .= ".".$hide_class_item."{display:none;}";
            }
            $css .= "</style>";
        }

        return $css.$html;
    }

    /*
     * Generates the captcha code for the client
     */
    public function captcha_code($reversed = false){
        $user_generated_string = $_SERVER['HTTP_USER_AGENT'].$_SERVER['SERVER_NAME'].$_SERVER['SERVER_ADDR'].$_SERVER['REMOTE_ADDR'];
        $hash = hash_hmac('crc32', $user_generated_string, 'xxeeTT');
        $code = substr($hash, 0, 5);
        if ($reversed){
            return strrev($code);
        } else {
            return $code;
        }
    }


    /**
     * Determines if a variable has a value
     *
     * Note : var is passed by reference to avoid errors with using undeclared index
     *
     * @param string $var The string being tested
     * @param string $strict Do we test for null/empty string/being false
     * @return boolean Does the string have a value
     */
    public function val(&$var, $strict = true)
    {
        if (isset($var)){
            if (!$strict || (!is_null($var) && $var != "" && $var != false)){
                return true;
            } else { return false; }
        } else { return false; }
    }

    /*
     * Assigns the location for the editor javascript file
     */
    public function editor($location)
    {
        if (isset($location) AND $location != ""){
            $this->editor = $location;
        }
    }

    /*
     * Returns form data in php array
     */
    public function data()
    {
        return $this->data;
    }

    /*
     * Returns form data in php array
     */
    public function warning($verbose = false)
    {
        if ($verbose){
            echo "<pre>";
            print_r($this->data["warning"]);
            echo "</pre>";
        } else {
            return $this->data["warning"];
        }
    }
}