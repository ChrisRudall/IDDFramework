<?php
namespace InDemandDigital\IDDFramework\Entities;
use InDemandDigital\IDDFramework as IDD;
use InDemandDigital\Tests\Debug as Debug;


class Shift extends Entity{

public function getShifts(){
    debug::niceprint("getShifts");
    $sql = "SELECT * FROM `gt_shifts`  ORDER BY `start_time`";
    $result = IDD\Database::query($sql);
    return self::convertResultSetToObjectArray($result,'InDemandDigital\Entities\Shift');
}

public function setLeadTime($job){
    $j = IDD\LocationMatrix::getJourney($job->from_location,$this->current_location);

    if($j !== NULL){
        $this->leadtime = new \DateInterval('PT'.$j->duration_in_traffic->value.'S');
    }else{
        $this->leadtime = new \DateInterval('PT0S');
    }
    $would_have_to_leave_at = clone $job->pickup_time;
    $would_have_to_leave_at->sub($this->leadtime);
    $would_have_to_leave_at_str = $would_have_to_leave_at->format("Y-m-d H:i:s");
    $this->would_have_to_leave_at = $would_have_to_leave_at_str;
    // print_r($would_have_to_leave_at_str);
    $sql = "UPDATE `shifts_temp` SET `would_have_to_leave_at`='$would_have_to_leave_at_str' WHERE `id`='$this->id'";
    // print_r($sql);
    IDD\Database::query($sql);
}


}
?>
