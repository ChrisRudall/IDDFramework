<?php
namespace InDemandDigital\IDDFramework;
session_start();
Date_default_timezone_set('UTC');
require '../vendor/autoload.php';

if(!$_GET['email']){
    echo "No email address supplied";
}else{
    try{
        Mail::addSubscriber($_GET);
        //send confirmation? - not if added via booking form etc - in fact, send confirmation via ajax
    }catch(\Exception $e){
        echo "Fail - Email not valid";
    }
}

?>
