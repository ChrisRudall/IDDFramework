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
    function getPostsForAccount($account){
        if($account->include_any == 1){
            $sql = "SELECT * FROM `data` WHERE `publish_date`<NOW() AND `expires`>NOW() AND `publish`='1' AND(`account`='$account->account_name' OR `account`='any')";
        }
        else{
            $sql = "SELECT * FROM `data` WHERE `publish_date`<NOW() AND `expires`>NOW() AND `publish`='1' AND `account`='$account->account_name'";
        }
        return Database::query($sql);
    }
}
?>
