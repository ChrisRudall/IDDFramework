<?php
namespace InDemandDigital\IDDFramework\Entities;
use InDemandDigital\IDDFramework as IDD;
use InDemandDigital\IDDFramework\Tests\Debug as Debug;

class Room extends Entity{

    public function getStageManager(){
        return new StaffMember($this->stage_manager);
    }
    public function getEvent(){
        return new Event($this->event);
    }
    public function getPerformances(){
        // Debug::niceprint("getPerformancesForRoomID");
        $sql = "SELECT * FROM `performances` WHERE `room_id`=$this->id ORDER BY `start_time`";
        $result = IDD\Database::query($sql);
        return self::convertResultSetToObjectArray($result,'InDemandDigital\IDDFramework\Entities\Performance');
    }
    public function getPerformancesByDisplayOrder($limit = NULL){

        $sql = "SELECT * FROM `performances` WHERE `room_id`=$this->id AND `display_order` != '0' ORDER BY `display_order`";
        if($limit != NULL){
            $sql = $sql . " LIMIT $limit";
        }
            Debug::niceprint($sql);
        $rs = IDD\Database::query($sql);
        while ($r = $rs->fetch_object('InDemandDigital\IDDFramework\Entities\Performance')){
            $r->artist = new Artist($r->artist_id);
            $a [] = $r;
        }
        return $a;
    }
}
?>
