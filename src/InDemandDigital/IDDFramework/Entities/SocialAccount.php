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

    function printAccountName(){
        echo "<div class='accountname'>";
        if($this->pretty_name != ""){
            echo $this->pretty_name;
        }else{
            echo $this->account_name;
        }
        echo "</div>";

    }

}
?>
