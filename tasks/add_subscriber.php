<?php
namespace InDemandDigital\IDDFramework;
session_start();
Date_default_timezone_set('UTC');

// print_r($_SERVER['DOCUMENT_ROOT']."/vendor/autoload.php");
require $_SERVER['DOCUMENT_ROOT']."/vendor/autoload.php";

if(!$_GET['email'] && !$_POST['email']){
    echo "Please enter your email address";
}else{
    try{
        if($_GET){
            $person = Mail::addSubscriber($_GET);
        }
        if($_POST){
            $person = Mail::addSubscriber($_POST);
        }
        var_dump($person);
        // echo "Success! $person->email has been added";
        //send confirmation? - not if added via booking form etc - in fact, send confirmation via ajax
    }catch(\Exception $e){
        echo "Please check you entered a valid email address!";
    }
}
?>
