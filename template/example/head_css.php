<link href='//fonts.googleapis.com/css?family=Open+Sans:300,400,600,700' rel='stylesheet' type='text/css'>
<link rel="stylesheet" href="<?php echo $c->config->css; ?>master.min.css<?php echo $this->cache_bust("css"); ?>">
<link rel="stylesheet" href="<?php echo $c->config->css; ?>jquery-ui-structure.min.css<?php echo $this->cache_bust("css"); ?>">
<link rel="stylesheet" href="<?php echo $c->config->css; ?>jquery-ui-theme.min.css<?php echo $this->cache_bust("css"); ?>">
<link rel="stylesheet" href="<?php echo $c->config->css; ?>style.min.css<?php echo $this->cache_bust("css"); ?>">
<link rel="stylesheet" href="<?php echo $c->config->css; ?>cdn.min.css<?php echo $this->cache_bust("css"); ?>">
<link rel="stylesheet" href="<?php echo $c->config->css; ?>form.min.css<?php echo $this->cache_bust("css"); ?>">
<link rel="stylesheet" href="<?php echo $c->config->css; ?>site.min.css<?php echo $this->cache_bust("css"); ?>">
<?php
if (isset($this->data["css_long"]) AND is_array($this->data["css_long"]) AND count($this->data["css_external"]) > 0){
    foreach ($this->data["css_long"] as $css_file){
        echo '<link rel="stylesheet" href="'.$css_file.'">';
    }
}
if (isset($this->data["css"]) AND is_array($this->data["css"]) AND count($this->data["css"]) > 0){
    foreach ($this->data["css"] as $css_file){
        echo '<link rel="stylesheet" href="'.$c->config->css.$css_file . $this->cache_bust("css").'">';
    }
}