<?php
namespace InDemandDigital\IDDFramework;
use InDemandDigital\IDDFramework\Entities AS Ent;

class SocialManager{
    function getPost($id){
        return new Ent\SocialPost($id);
    }
    function getAccount($name){
        return new Ent\SocialAccount($name);
    }
}
?>
