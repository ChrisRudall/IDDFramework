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
    if($account->include_any == 1){
        $sql = "SELECT * FROM `data` WHERE `publish_date`<NOW() AND `expires`>NOW() AND `publish`='1' AND(`account`='$account->account_name' OR `account`='any')";
    }
    else{
        $sql = "SELECT * FROM `data` WHERE `publish_date`<NOW() AND `expires`>NOW() AND `publish`='1' AND `account`='$account->account_name'";
    }
    $r = IDD\Database::query($sql);
    return $r->fetch_all(MYSQLI_NUM);
}


}
?>
