<?php
namespace InDemandDigital\IDDFramework;
session_start();
Date_default_timezone_set('UTC');

require $_SERVER['DOCUMENT_ROOT']."/vendor/autoload.php";

if(!$_GET['email'] && !$_POST['email']){
    die("need an email address");
}
?>
