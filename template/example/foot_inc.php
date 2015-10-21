<?php
if (isset($this->data["inc"]) AND is_array($this->data["inc"]) AND count($this->data["inc"]) > 0){
    foreach ($this->data["inc"] as $inc_file){
        include($c->config->dir_public.$inc_file);
    }
}