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
        return self::convertResultSetToObjectArray($result,'InDemandDigital\Entities\Performance');
    }
    public function getPerformancesByDisplayOrder($limit = NULL){

        $sql = "SELECT * FROM `performances` WHERE `room_id`=$this->id ORDER BY `display_order` LIMIT $limit";
            Debug::niceprint($sql);
        $rs = IDD\Database::query($sql);
        while ($r = $rs->fetch_object('InDemandDigital\Entities\Performance')){
            $a [] = $r;
        }
        return $a;
    }
}
?>
