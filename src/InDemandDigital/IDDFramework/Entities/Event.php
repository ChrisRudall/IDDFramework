<?php
namespace InDemandDigital\IDDFramework\Entities;
use InDemandDigital\IDDFramework as IDD;

class Event extends Entity{
    function getAllRooms(){
        $sql = "SELECT * FROM rooms WHERE event='$this->id' ORDER BY start_time";
        $rs = IDD\Database::query($sql);
        while ($r = $rs->fetch_object('\InDemandDigital\IDDFramework\Entities\Event')) {
            $array [] = $r;
        }
        return $array;

    }
    function getAllFutureRooms(){
        $sql = "SELECT * FROM rooms WHERE event='$this->id' AND `start_time`>NOW() ORDER BY start_time";
        $rs = IDD\Database::query($sql);
        while ($r = $rs->fetch_object('\InDemandDigital\IDDFramework\Entities\Event')) {
            $array [] = $r;
        }
        return $array;
    }
}
?>
