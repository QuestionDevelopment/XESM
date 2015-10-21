<script src="<?php echo $c->config->js; ?>master.min.js<?php echo $this->cache_bust("js"); ?>"></script>
<script src="<?php echo $c->config->js; ?>site.min.js<?php echo $this->cache_bust("js"); ?>"></script>
<?php
if (isset($this->data["js"]) AND is_array($this->data["js"]) AND count($this->data["js"]) > 0){
    foreach ($this->data["js"] as $jsFile){
        echo '<script src="'.$c->config->js.$jsFile.$this->cache_bust("js").'"></script>';
    }
}
if (isset($this->data["script"]) AND is_array($this->data["script"]) AND count($this->data["script"]) > 0){
    echo '<script>';
    foreach ($this->data["script"] as $jsScript){
        if (is_array($jsScript) AND isset($jsScript["action"]) AND isset($jsScript["id"])){
            if ($jsScript["action"] == "click"){
                echo '$("#'.$jsScript["id"].'").click(function() {'.$jsScript["value"].'});';
            } else if ($jsScript["action"] == "autocomplete"){
                echo '$("#'.$jsScript["id"].'").autocomplete({';
                    echo 'minLength:2,';
                    echo 'source: "'.$jsScript["source"].'"';
                echo '});';
            }
        } else {
             echo $jsScript;
        }
    }
    echo '</script>';
}
