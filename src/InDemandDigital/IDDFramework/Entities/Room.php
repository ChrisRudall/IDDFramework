<?php
namespace InDemandDigital\IDDFramework\Entities;
use InDemandDigital as IDD;
use InDemandDigital\Tests\Debug as Debug;

class Room extends Entity{

    public function getStageManager(){
        return new StaffMember($this->stage_manager);
    }
    public function getEvent(){
        return new Event($this->event);
    }
    public function getPerformances(){
        debug::niceprint("getPerformancesForRoomID");
        $sql = "SELECT * FROM `performances` WHERE `room_id`=$this->id ORDER BY `start_time`";
        $result = IDD\Database::query($sql);
        return self::convertResultSetToObjectArray($result,'InDemandDigital\Entities\Performance');
    }
}
?>
