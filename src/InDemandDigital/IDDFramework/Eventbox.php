<?php
namespace InDemandDigital\IDDFramework;
use InDemandDigital\IDDFramework\Entities AS Ent;
use InDemandDigital\IDDFramework\Tests\Debug AS Debug;


//SET ARTIT SQL TO ONLY GET DISPLAY OVER ZERO - GETPERFORMANCESFORDISPLAY()

class Eventbox{
    public $event;
    public $artistlimit = 1000;
    public $roomlimit = 1000;
    public $offset = 0;
    public $showtag = False;

    function __construct($eventid){
        Database::connect();
        $this->event = new Ent\Event($eventid);
        $this->id = "eventbox".rand();
    }

    function showRoom($id = NULL){
        if($id != NULL){
            $this->room = new Ent\Room($id);
            $this->room->prettydate = self::getNiceDate($this->room->start_time);
        }else{
            $rooms = $this->event->getAllFutureRooms($this->roomlimit);
            $this->room = $rooms[$this->offset];
            $this->room->prettydate = self::getNiceDate($this->room->start_time);
        }
        $this->renderRoom();
    }

    function showAllRooms(){
        $this->rooms = $this->event->getAllFutureRooms($this->roomlimit);
        $this->makeDatesPretty($this->rooms);
        $this->renderAllRooms();
    }




    private function renderAllRooms(){
        //set image to headliner pic
        $this->performances = $this->rooms[0]->getPerformancesByDisplayOrder(1);
        $this->setImage();

        echo "<div class='eventbox' id='$this->id'>";
        echo "<img class='eventboximage' src='$this->image'>";
        echo "<div class='eventboxtitle'>What's On</div>";

        echo "<div class='eventboxtext'>";

        foreach ($this->rooms as $room){
            echo "<div class='eventboxinfo'>";
            echo "<span class='fadedtext'>$room->prettydate</span> $room->name ";
            echo "<div class='tag'>";
            if($this->showtag == True){
                // $artists = getArtistNames($room['id']);
                $performances = $room->getPerformancesByDisplayOrder($this->artistlimit);
                if($performances != null){
                    foreach ($performances as $performance){
                        echo "<span> / ".$performance->artist->name."</span>";
                    }
                }

            }
            echo "</div></div>";

            }
            echo "</div></div>";
    }


    private function renderRoom (){
        $this->performances = $this->room->getPerformancesByDisplayOrder($artistlimit);
        $this->setImage();

        echo "<div class='eventbox' id='$this->id'>";
        echo "<img class='eventboximage' src='$this->image'>";
        echo "<div class='eventboxtitle'>{$this->room->name}<br><span class='fadedtext'>{$this->room->prettydate}</span></div>";
            echo "<div class='eventboxtext'>";
            foreach ($this->performances as $performance){
                    echo "<div class='eventboxinfo'>";
                    echo $performance->artist->name;
                    echo "<div class='tag'>&nbsp";
                    if($this->showtag == True){
                        echo $performance->artist->tagline;

                    }
                    echo "</div>";
                    echo "</div>";

        }
            echo "</div>";
        echo "</div>";


    }

    private function setImage(){
        if ($this->performances[0]->artist->img1){
            $this->image = "https://portal.indemandmusic.com/assets/images/profile_images/" . $this->performances[0]->artist->img1;
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


}









// function getArtistNames($roomid){
//     $sql = "SELECT name,tagline,display_order,performances.id,img1 FROM performances,artists WHERE room_id='$roomid' AND performances.artist_id=artists.id AND display_order>0 ORDER BY display_order";
//     $rs=$GLOBALS["conn"]->query($sql);
//     $artists = $rs->fetch_all(MYSQLI_ASSOC);
//     return $artists;
// }






// function renderEvent($id,$artistlimit,$roomlimit,$displaytype) {
//     $sql = "SELECT * FROM rooms WHERE event='$id' AND `start_time`>NOW() ORDER BY start_time";
//     $rs=$GLOBALS['conn']->query($sql);
//     $rooms = $rs->fetch_all(MYSQLI_ASSOC);
//     $c = 0;
//     if ($displaytype == 'eventblock'){
//         renderEventBlock($id,$roomlimit,$artistlimit,$rooms);
//     }else{
//     foreach ($rooms as $room){
//         if($c >= $roomlimit){break;}
//         $roomid = $room["id"];
//         renderRoom($roomid,$artistlimit,$displaytype);
//
//         $c = $c +1;
//         }
//     }
// }


// function renderRoom ($id,$artistlimit,$displaytype){
//     $sql = "SELECT name,start_time FROM rooms WHERE id='$id'";
//     // echo $sql;
//     $rs=$GLOBALS['conn']->query($sql);
//     $room = $rs->fetch_all(MYSQLI_ASSOC);
//     $roomname = $room[0]['name'];
//     $nicedate = getNiceDate($room[0]['start_time']);
//     $artists = getArtistNames($id);
//     if ($artistlimit > count($artists)){
//         $artistlimit = count($artists);
//     }
//     if (!$displaytype){
//         $displaytype = "roomblock";
//     }
//     $columns = $GLOBALS["columns"];
//     // display types
//     // artistblock - each atist with pic
//     // roomblock - each room with listing, and pic of main artist
//     // eventblock - each event(venue) as a block with roomname & headliner
//     switch ($displaytype) {
//         case 'artistblock':
//             renderArtistBlock($artistlimit,$artists,$columns,$roomname,$nicedate);
//             break;
//         case 'roomblock':
//             renderRoomBlock($artistlimit,$artists,$columns,$roomname,$nicedate);
//             break;
//         default:
//             renderRoomBlock($artistlimit,$artists,$columns,$roomname,$nicedate);
//             break;
//     }
// }
function renderArtistBlock($artistlimit,$artists,$columns,$roomname,$nicedate){
    echo "<div class='roomtitle'>$roomname</div>";
    echo "<div class='roomdate'>$nicedate</div>";
    for ($i=0;$i<$artistlimit;$i++){
            if ($artists[$i]["img1"]){
                $image = "https://portal.indemandmusic.com/assets/images/profile_images/" . $artists[$i]["img1"];
               }
            else
                {$image = "http://www1.theladbible.com/images/content/53959ef6501b3.jpg";
           	}
        	echo "<div class='artistblock colcount$columns'>";
          echo "<div class='image-container'><img class='artistblockimage' src='$image'>";echo "</div>";
            echo "<div class='artistblockname' style='bottom:".$GLOBALS['textoffset']."px;'>".$artists[$i]["name"];
            echo printTag($artists[$i]);
            echo "</div>";
            echo "</div>";
    }
}
function renderRoomBlock($limit,$artists,$columns,$roomname,$nicedate){
    //set image to headliner pic
    if ($artists[0]["img1"]){
        $image = "https://portal.indemandmusic.com/assets/images/profile_images/" . $artists[0]["img1"];
       }
    else
        {$image = "http://www1.theladbible.com/images/content/53959ef6501b3.jpg";
    }

    echo "<div class='roomblock colcount$columns'><img class='roomblockimage' src='$image'>";
    // echo "<div class='artistbox$columns'>";
    echo "<div class='roomblocktitle'>$roomname<br><span class='fadedtext'>$nicedate</span></div>";
    // echo "<div class='roomblockdate'>$nicedate</div>";
    for ($i=0;$i<$limit;$i++){
            echo "<div class='roomblockname' style='bottom:".$GLOBALS['textoffset']."px;'>".$artists[$i]["name"];
            echo printTag($artists[$i]);
            echo "</div>";
    }
    echo "</div>";
}


function printTag($artist){
    $tag =$artist["tagline"];
    if($GLOBALS["showtag"] == 'true' && $tag  != ""){
        $tag = "<span class='tag'> $tag</span>";
    }
    else{
        $tag = "";
    }
    return $tag;
}
// END FUNCTIONS


// START SCRIPT
// GET PARAMS
// $id = $_GET["id"];
// $datatype = $_GET["datatype"];
// $displaytype = $_GET["displaytype"];
// $displaytype = $_GET["displaytype"];
// $GLOBALS["columns"] =  $_GET["columns"];
// $GLOBALS["showdate"] =  $_GET["showdate"];
//
// $roomlimit = $artistlimit = 100;
// $GLOBALS["showtag"] = $_GET["showtag"];
// if ($_GET["roomlimit"]){
//     $roomlimit = $_GET["roomlimit"];
// }
// if ($_GET["artistlimit"]){
//     $artistlimit = $_GET["artistlimit"];
// }
// if ($_GET["textoffset"]){
//     $GLOBALS['textoffset'] = $_GET["textoffset"];
// }
// else{$GLOBALS['textoffset'] = "120";
// }


// if ($datatype == 'event'){
//   renderEvent($id,$artistlimit,$roomlimit,$displaytype);
// }
// elseif ($datatype == 'room'){
//     if($displaytype == 'eventblock'){
//     echo "ERROR - DATATYPE MISMATCH - CANNOT USE EVENT DISPLAY TYPE FOR ROOM DATA";
//     }
//   renderRoom($id,$artistlimit,$displaytype);
// }
// else {
//   echo "no datatype set";
// }
// switch ($datatype) {
//     case 'event':
//         renderEvent($id,$artistlimit,$roomlimit,$displaytype);
//         break;
//     case 'room':
//     if($displaytype == 'eventblock'){
//         echo "ERROR - DATATYPE MISMATCH - CANNOT USE EVENT DISPLAY TYPE FOR ROOM DATA";
//         }
//       renderRoom($id,$artistlimit,$displaytype);
//         break;
//     default:
//         echo "no datatype set";
//         break;
//     }
?>
