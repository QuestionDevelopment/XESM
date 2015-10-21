<?php
include("../core/init.php");
include("../core/page.php");

$xesm = new init(true, "../../");
$page = new \xesm\core\page($xesm->c);

$test = array();
$test[] = "/";
$test[] = "/index.php";
$test[] = "/account/";
$test[] = "/account/index.php";
$test[] = "/account/edit/";
$test[] = "/account/edit/index.php";
$test[] = "/account/edit.php";
$test[] = "/account/edit/7/";

foreach ($test as $test_page){
    $page->data($test_page);
    echo "------------------------------------------------------------<br/>";
    echo $test_page."<br/>";
    echo "------------------------------------------------------------<br/>";
    echo "url = ".$page->data["url"]."<br/>";
    echo "page = ".$page->data["page"]."<br/>";
    echo "parameters = ".$page->data["parameters"]."<br/>";   
}
exit();