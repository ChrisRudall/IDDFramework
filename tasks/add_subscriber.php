<?php
namespace InDemandDigital\IDDFramework;
session_start();
Date_default_timezone_set('UTC');
require '../vendor/autoload.php';

if(!$_GET['email'] && !$_POST['email']){
    echo "No email address supplied";
}else{
    try{
        if($_GET){
            print_r(Mail::addSubscriber($_GET));
        }
        if($_POST){
            print_r(Mail::addSubscriber($_POST));
        }
        //send confirmation? - not if added via booking form etc - in fact, send confirmation via ajax
    }catch(\Exception $e){
        echo "Fail - Email not valid";
    }
}
print_r("------DONE------");
?>
