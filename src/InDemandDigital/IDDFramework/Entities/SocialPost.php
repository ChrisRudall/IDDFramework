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
        if($this->text){
            echo "<div class='text'>";
            echo $post->text;
            echo "</div>";
        }
    }
    function printMedia(){
        if($this->media){
            echo "<div class='media'>";
            echo "<img src='assets/$post->media'>";
            echo "</div>";
        }
    }
    function printMediaURL(){
        if($this->media){
            echo $this->media;
        }
    }
}
?>
