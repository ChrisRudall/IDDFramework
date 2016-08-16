<?php
namespace InDemandDigital\IDDFramework\Entities;
use InDemandDigital\IDDFramework AS IDD;
use InDemandDigital\IDDFramework\Tests\Debug AS Debug;

class SocialAccount extends Entity{
    public function __construct($name = NULL){
        if($name !== NULL){
            $sql = "SELECT * FROM `accounts` WHERE `account_name`='$name'";
            $p = IDD\Database::query($sql);
            // Debug::nicePrint($sql);
            $r = $p->fetch_object('InDemandDigital\IDDFramework\Entities\SocialAccount');
            if($r !== NULL){
                foreach ($r as $key => $value) {
                    $this->$key = $value;
                }
            }
        }
    }

function getPosts(){
    if($this->include_any == 1){
        $sql = "SELECT * FROM `data` WHERE `publish_date`<NOW() AND `expires`>NOW() AND `publish`='1' AND(`account`='$this->account_name' OR `account`='any')";
    }
    else{
        $sql = "SELECT * FROM `data` WHERE `publish_date`<NOW() AND `expires`>NOW() AND `publish`='1' AND `account`='$this->account_name'";
    }
    $r = IDD\Database::query($sql);
    while($post = $r->fetch_object('InDemandDigital\IDDFramework\Entities\SocialPost')){
        $postarray [] = $post;
    }
        return $postarray;
}

function writeTwitterLog($logarray){
    if(getType($logarray) != 'array'){
        trigger_error('writeTwitterLog expects an array',E_USER_ERROR);
    }
    $logstring = implode(',',$logarray);
    $sql = "UPDATE `accounts` SET `twitter_log`='$logstring' WHERE `account_id`='$this->account_id'";
    // print_r($sql);
    return IDD\Database::query($sql);
}

}
?>
