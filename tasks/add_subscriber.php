<?php
namespace InDemandDigital\IDDFramework;
session_start();
Date_default_timezone_set('UTC');

// print_r($_SERVER['DOCUMENT_ROOT']."/vendor/autoload.php");
require $_SERVER['DOCUMENT_ROOT']."/vendor/autoload.php";

if(!$_GET['email'] && !$_POST['email']){
    echo "0Please enter your email address";
}else{
    try{
        if($_GET){
            $person = Mail::addSubscriber($_GET);
        }
        if($_POST){
            $person = Mail::addSubscriber($_POST);
        }
        echo "1".$person->email . " has been added succesfully, please check your email!";
        // echo "Success! $person->email has been added";
        //send confirmation? - not if added via booking form etc - in fact, send confirmation via ajax
    }catch(\Exception $e){
        echo "0Please check you entered a valid email address!";
    }
}
?>
