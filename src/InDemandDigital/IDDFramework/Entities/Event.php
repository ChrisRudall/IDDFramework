<?php
namespace InDemandDigital\IDDFramework\Entities;
use InDemandDigital\IDDFramework as IDD;

class Event extends Entity{
    function getAllRooms($limit){
        $sql = "SELECT * FROM rooms WHERE event='$this->id' ORDER BY start_time LIMIT $limit";
        $rs = IDD\Database::query($sql);
        while ($r = $rs->fetch_object('\InDemandDigital\IDDFramework\Entities\Room')) {
            $array [] = $r;
        }
        return $array;

    }
    function getAllFutureRooms($limit){
        $sql = "SELECT * FROM rooms WHERE event='$this->id' AND `start_time`>NOW() ORDER BY start_time LIMIT $limit";
        $rs = IDD\Database::query($sql);
        while ($r = $rs->fetch_object('\InDemandDigital\IDDFramework\Entities\Room')) {
            $array [] = $r;
        }
        return $array;
    }
}
?>
