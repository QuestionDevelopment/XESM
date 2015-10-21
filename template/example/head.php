<!doctype html>
<!--[if lt IE 7]><html class="no-js lt-ie9 lt-ie8 lt-ie7" lang="en"><![endif]-->
<!--[if IE 7]><html class="no-js lt-ie9 lt-ie8" lang="en"><![endif]-->
<!--[if IE 8]><html class="no-js lt-ie9" lang="en"><![endif]-->
<!--[if gt IE 8]><!--><html class="no-js" lang="en" xmlns="http://www.w3.org/1999/html"><!--<![endif]-->
<head>
    <meta charset="utf-8">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <meta name="HandheldFriendly" content="true">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php
    if (isset($this->data["title"])) { echo '<title>'.$this->data["title"].'</title>'; }
    if (isset($this->data["canonical"])) { echo '<link rel="canonical" href="'.$this->data["canonical"].'">'; }
    if (isset($this->data["meta"]) AND is_array($this->data["meta"]) AND count($this->data["meta"]) > 0){
        foreach ($this->data["meta"] AS $tag){
            if (is_array($tag) AND count($tag) > 0){
                echo "<meta property=\"" . $tag[0] . "\" content=\"" . $tag[1] . "\" />\n";
            }
        }
    }
    include("head_css.php");
    include("head_js.php");
    ?>
</head>
<body>
