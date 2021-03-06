<?php
namespace InDemandDigital\IDDFramework;
use InDemandDigital\IDDFramework\Entities AS Ent;
use InDemandDigital\IDDFramework\Tests\Debug AS Debug;



class Eventbox{

    // @PARAM event - int - set target event
    // @param artistlimit - int - limit artists displayed
    // @param roomlimit - int - limit rooms displayed (all rooms only)
    // @param offset - int - rooms display chronologically by default, use offset to target further into the future
    // @param showtag - bool - whether to show the additional tag info
    //@param $feature - sets whther to display in feature mode
    //@param $feature_offset - feature a different artist in the lineup. can also use to change picture on a box
    //@param $dark = true - set text darker for light backgrounds

    //@param showRoom - displays lineup of artists in a given room
    //@param showAllRooms - displays all the rooms at a given event

    public $event;
    public $artistlimit = 1000;
    public $roomlimit = 1000;
    public $room_offset = 0;
    public $showtag = True;
    public $feature = False;
    public $feature_offset = 0;


    const css = "<link rel='stylesheet' type='text/css' href='/vendor/InDemandDigital/IDDFramework/css/eventbox.css'>";
    private static $cssdone = 0;

    function __construct($eventid){
        Database::connect();
        $this->event = new Ent\Event($eventid);
        $this->id = "eventboxid-".rand();
        if(self::$cssdone == 0){
            echo self::css;
            self::$cssdone = 1;
        }
    }

    function showRoom($id = NULL){
        if($id != NULL){
            $this->room = new Ent\Room($id);
            $this->room->prettydate = self::getNiceDate($this->room->start_time);
        }else{
            $rooms = $this->event->getAllRooms($this->roomlimit);
            $this->room = $rooms[$this->room_offset];
            $this->room->prettydate = self::getNiceDate($this->room->start_time);
        }
        if($this->feature == True){
            $this->renderFeatureRoom();
        }else{
            $this->renderRoom();
        }
        $this->setStyle();
    }

    function showAllRooms(){
        $this->rooms = $this->event->getAllFutureRooms($this->roomlimit);
        $this->makeDatesPretty($this->rooms);
        $this->renderAllRooms();
        $this->setStyle();
    }




    private function renderAllRooms(){
        //set image to headliner pic
        $this->performances = $this->rooms[$this->room_offset]->getPerformancesByDisplayOrder(10);
        $this->setImage();
        // $this->id = $this->event->id;
        echo "<div class='eventbox' id='$this->id'>";
        echo "<div class='eventboxtitle mobile'>What's On</div>";

        echo "<img class='eventboximage' src='$this->image'>";


        echo "<div class='eventboxtext'>";
    echo "<div class='eventboxtitle desktop'>What's On</div>";
        foreach ($this->rooms as $room){
            echo "<div class='eventboxinfo'>";
            echo "<span class='fadedtext'>$room->prettydate</span> $room->name ";
            echo "</div>";
            echo "<div class='tag desktop'>";
            if($this->showtag == True){
                $performances = $room->getPerformancesByDisplayOrder($this->artistlimit);
                if($performances != null){
                    foreach ($performances as $performance){
                        echo "<span> / ".$performance->artist->name."</span>";
                    }
                }

            }
            echo "</div>";

            }
            echo "</div></div>";
    }


    private function renderRoom (){
        // $this->id = "eventbox-roomid-".$this->room->id;
        $this->performances = $this->room->getPerformancesByDisplayOrder($this->artistlimit);
        $this->setImage();

        echo "<div class='eventbox' id='$this->id'>";
        // echo "<div class='eventboxtitle mobile'>{$this->room->name}<br><span class='fadedtext'>{$this->room->prettydate}</span></div>";

        echo "<img class='eventboximage' src='$this->image'>";
        echo "<div class='eventboxtext'>";
        echo "<div class='eventboxtitle'>{$this->room->name}<br><span class='fadedtext'>{$this->room->prettydate}</span></div>";

        if($this->performances){
            foreach ($this->performances as $performance){
                    echo "<div class='eventboxinfo'>";
                    echo $performance->artist->name;
                    echo "</div>";
                    echo "<div class='tag'>&nbsp";
                    if($this->showtag == True){
                        echo $performance->artist->tagline;
                    }
                    echo "</div>";
            }
        }
        echo "</div>";
        echo "</div>";
    }

    private function renderFeatureRoom (){
        // $this->id = "eventbox-roomid-".$this->room->id;
        $this->performances = $this->room->getPerformancesByDisplayOrder($this->artistlimit);
        $this->setImage();

        echo "<div class='eventbox' id='$this->id'>";
        echo "<div class='eventboxtitle feature mobile'>{$this->performances[$this->feature_offset]->artist->name}<br><span class='fadedtext feature'>{$this->performances[$this->feature_offset]->artist->tagline}</span></div>";

        echo "<img class='eventboximage' src='$this->image'>";
        echo "<div class='eventboxtext feature'>";
        echo "<div class='eventboxtitle feature desktop'>{$this->performances[$this->feature_offset]->artist->name}<br><span class='fadedtext feature'>{$this->performances[$this->feature_offset]->artist->tagline}</span></div>";
        echo "<div class='eventboxinfo feature'>"; echo $this->room->name; echo "</div>";
        echo "<div class='tag feature'>&nbsp{$this->room->prettydate}</div>";
        echo "</div>";
        echo "</div>";
    }

    private function setImage(){
        if ($this->performances[$this->feature_offset]->artist->img1){
            $this->image = "https://portal.indemandmusic.com/assets/images/profile_images/" . $this->performances[$this->feature_offset]->artist->img1;
           }
        else
            {$this->image = "http://www1.theladbible.com/images/content/53959ef6501b3.jpg";
        }
        if($this->height){
            echo "<style>";
            echo "#$this->id img{ height: {$this->height}px;}";
            echo "</style>";
        }
    }

    private function getNiceDate($datestring){
        if($GLOBALS["showdate"] == "false"){
            return "";
        }
        $date = date_create($datestring);
        return date_format($date,"d/m/y");
    }

    private function makeDatesPretty($rooms){
        foreach ($rooms as $room) {
            $room->prettydate = self::getNiceDate($room->start_time);
        }
    }
    private function setStyle(){
        if($this->dark == True){
            echo "<style>";
            echo "#$this->id .eventboxtitle{ color: black;}";
            echo "</style>";
        }
    }

}
?>
