<?php
namespace InDemandDigital\IDDFramework\Entities;
use InDemandDigital\IDDFramework AS IDD;
use InDemandDigital\IDDFramework\Tests\Debug AS Debug;


class SocialPost extends Entity{
    public function __construct($id = NULL){
        if($id !== NULL){
            $sql = "SELECT * FROM `data` WHERE `id`='$id'";
            $p = IDD\Database::query($sql);
            // Debug::nicePrint($sql);
            $r = $p->fetch_object('InDemandDigital\IDDFramework\Entities\SocialPost');
            if($r !== NULL){
                foreach ($r as $key => $value) {
                    $this->$key = $value;
                }
            }
        }
    }


    function printText(){
        if($this->text != ""){
            echo "<div class='contentitem text'>";
            echo $this->text;
            echo "</div>";
        }
    }
    function printURL(){
        if($this->url != ""){
            echo "<div class='contentitem url'>";
            echo "<a href='$this->url'>";
            echo $this->url;
            echo "</a></div>";
        }
    }
    function printMedia(){
        if($this->media != ""){
            echo "<div class='contentitem media'>";
            echo "<img src='assets/$this->media'>";
            echo "</div>";
        }
    }
    function printMediaURL(){
        if($this->media != ""){
            echo "assets/".$this->media;
        }
    }
}
?>
