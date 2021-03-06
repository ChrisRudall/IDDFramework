<?php
namespace InDemandDigital\IDDFramework;
use InDemandDigital\IDDFramework\Tests\Debug as Debug;
use InDemandDigital\IDDFramework\Entities as Ent;
use DateTime;
use DateInterval;
Date_default_timezone_set('Europe/London');

// $p = get_include_path();
// set_include_path("v2/src/InDemandDigital/Portal/");
// require_once 'Database.php';
// require_once 'Entities/StaffMember.php';
//
// set_include_path($p);
class GroundTransport{
public static $drivers_added = 0;
public static $added_drivers = [];
private static $blocked_drivers = [];
private static $logfile;
private static $pointer = 0;
public static $driver_buffer = 5;


public static function calculateSchedules(){
    self::createTempTable();

    //logging
    $nn = new DateTime();
    $n = $nn->format("YmdHis");
    self::$logfile = fopen("data/logs/calculateSchedules_".$n.".txt",'w') or die("Unable to open file!");

    //get all the jobs
    $alljobs = Ent\Job::getFutureJobs();
    while ($job = $alljobs->fetch_object('InDemandDigital\IDDFramework\Entities\Job')) {
        unset($shift);

        self::$pointer++; //for backloop
        $success = 0; // for backloop


        print_r('pointer position: '.self::$pointer."<br>");
        print_r("job id: ".$job->id."<br>");

        $job->embellishJobWithObjects();

        // unset(self::$locked_drivers[self::$pointer]); //backloop

        // print_r("locked drivers: ");
        // var_dump(self::$locked_drivers);
        // print_r("<br>");





        //if job is locked
        //check if locked in driver is in available shifts_temp
        if($job->locked == '1'){
            print_r("Job locked<br>");
            $sql = "SELECT * FROM `shifts_temp` WHERE `id`='{$job->assigned_shift->id}'";
            $rs = Database::query($sql);
            if($rs->num_rows == 0){
                //if not, add him
                self::addDriver($job,$job->assigned_shift->id);
                // $sql = "SELECT * FROM `shifts_temp` WHERE `id`='{$job->assigned_shift->id}'";
                $rs = Database::query($sql);
            }
            $shift = $rs->fetch_object('InDemandDigital\IDDFramework\Entities\Shift');

            //check if lcoked in driver is available when needed
            //if yes, run assign success, otherwise go backwards or throw Error

            $shift->setLeadTime($job);
            // if($job->id == 66){die;}
            var_dump($shift);
            $would_have_to_leave_at = new \DateTime($shift->would_have_to_leave_at);
            $available_time = new \DateTime($shift->available_time);
            if($shift->current_location != 11 && $would_have_to_leave_at < $available_time){
                //error - driver is locked but wont make it
                $pickup_prettystr = $job->pickup_time->format('H:i');
                $errorstr = printf("driver id %s is locked but wont make it for job %s at %s %s",$hift->id,$job->id,$pickup_prettystr,$job->to->name);
                trigger_error($errorstr,E_USER_WARNING);
                // var_dump($);
                //get lastjob for lockd shift
                // $sql = "UPDATE `shifts_temp` SET `make_available`='0' WHERE `id`='$shift->last_job'";
                // IDD\Database::query($sql);
                self::$blocked_drivers [] = $shift->id;
                //go pointer back until find job id
                echo "x".$shift->last_job;
                self::$pointer--;
                do{
                    self::$pointer--;
                    $alljobs->data_seek(self::$pointer);
                    $j = $alljobs->fetch_object('InDemandDigital\IDDFramework\Entities\Job');
                    // self::$pointer--;
                    echo "Y".$j->id;
                }while($j->id != $shift->last_job);

                $alljobs->data_seek(self::$pointer);
                $lastjob = new Ent\Job($shift->last_job);
                $sql = "UPDATE `shifts_temp` SET `available_time`=`previous_available_time` WHERE `id`='{$shift->id}'";
                Database::query($sql);
                $sql = "UPDATE `shifts_temp` SET `current_location`='$lastjob->from_location' WHERE `id`='{$shift->id}'";
                // print_r($sql);
                Database::query($sql);
                continue;

                //run again avoiding clashing njob
            }else{
                // sorted - update driver location and log
                $job->assigned_shift = $shift;
                self::shiftAssignSuccess($job);
            }

        }else{
            print_r("Job not locked<br>");
            // if job is not locked
            // get all shifts that are available & made available
            // gets list of shift from temp table
            $rs = self::getListOfAvailableShifts($job->pickup_time,$job->arrival_time);
            while($rs->num_rows === 0){
                self::addDriver($job);
                $rs = self::getListOfAvailableShifts($job->pickup_time,$job->arrival_time);
            }

            //work out each drivers distance to job
            while( $shift = $rs->fetch_object('InDemandDigital\IDDFramework\Entities\Shift')){
                $shift->setLeadTime($job);
            }
            //assign best fit
            //selects best fit job
            $sql = "SELECT * FROM shifts_temp WHERE available_time<='$job->pickup_time_str' AND stop_time>'$job->arrival_time_str' AND `would_have_to_leave_at`>=`available_time` AND `make_available`='1' ORDER BY would_have_to_leave_at DESC";
            $rs = Database::query($sql);
            do{
                $job->assigned_shift = $rs->fetch_object('InDemandDigital\IDDFramework\Entities\Shift');
            }while(in_array($job->assigned_shift->id,self::$blocked_drivers));
            if($job->assigned_shift == NULL){
                trigger_error($sql,E_USER_WARNING);
                $job->assigned_shift = self::addDriver($job);
            }
            // run success
            // var_dump($job);
            self::shiftAssignSuccess($job);
        }


        self::printShiftStatuses();
    }//end of job loop
    $logtext = sprintf("\nTotal of %s drivers needed",self::$drivers_added);
    fwrite(self::$logfile,$logtext);
    fclose(self::$logfile);
    print_r($logtext);
    //drop temp table
    $sql = "DROP TABLE shifts_temp";
    // Database::query($sql);

}

private function myLog($logtext){
    fwrite(self::$logfile,$logtext);
    print_r($logtext."<br>");
}

private function shiftAssignSuccess($job){
    self::myLog("Success - assigned job ".$job->id);
    //update job in db
    $sql = "UPDATE `gt_jobs` SET `assigned_shift`='{$job->assigned_shift->id}' WHERE `id`='$job->id'";
    // print_r($sql);
    Database::query($sql);

    $k = array_search($job->assigned_shift->id,self::$blocked_drivers);
    unset(self::$blocked_drivers[$k]);
    //update shift in temp db
    $previous_available_time = $job->assigned_shift->available_time;
    $available_time = $job->arrival_time;
    $arrival_prettystr = $job->arrival_time->format('H:i');
    $available_time->modify('+'.$driver_buffer. 'minutes');
    $available_time_str = $available_time->format("Y-m-d H:i:s");
    $sql = "UPDATE shifts_temp SET `available_time`='$available_time_str',`previous_available_time`='$previous_available_time',`current_location`='{$job->to->id}',`last_job`='$job->id' WHERE `id`='{$job->assigned_shift->id}'";
    Database::query($sql);

    //logfile
    $pickup_prettystr = $job->pickup_time->format('H:i');
    $logtext = sprintf("Driver ID#%s pickup from %s at %s to %s arriving approx %s\n",$job->assigned_shift->id,$job->from->name,$pickup_prettystr,$job->to->name,$arrival_prettystr);
    self::myLog($logtext);
}

public static function printShiftStatuses(){
    $sql = "SELECT * FROM shifts_temp ";
    $rs = Database::query($sql);
    while ($shift = $rs->fetch_object()){
        $l = new Ent\Location($shift->current_location);
        $t = new DateTime($shift->available_time);
        $logtext = sprintf("    (Driver ID#%s is available at %s from %s)\n",$shift->id,$l->name,$t->format('H:i'));
        fwrite(self::$logfile,$logtext);
        print_r($logtext."<br>");
    }

}


private static function createTempTable(){
    //create a temp table
    $sql = "DROP TABLE shifts_temp";
    Database::query($sql);

    $sql = "CREATE TABLE shifts_temp (id int(11) unsigned NOT NULL AUTO_INCREMENT,available_time DateTime,previous_available_time DateTime,stop_time datetime,current_location int(2) DEFAULT 11,would_have_to_leave_at datetime,make_available int(1),last_job int(11),PRIMARY KEY (`id`)) ENGINE=MEMORY";
    Database::query($sql);
}


private static function addDriver($job,$id = None){
    // add any driver or specific driver to temp table
    if($id != None){
        print_r("Trying to add driver id ".$id);print_r("<br>");
        $sql = "SELECT * FROM `gt_shifts` WHERE `start_time`<='$job->pickup_time_str' AND `stop_time`>'$job->arrival_time_str' AND `id`='$id'";
    }else{
        $sql = "SELECT * FROM `gt_shifts` WHERE `start_time`<='$job->pickup_time_str' AND `stop_time`>'$job->arrival_time_str' AND `make_available`='1'";
    }
    $rs = database::query($sql);

    while ($shift = $rs->fetch_object('InDemandDigital\IDDFramework\Entities\Shift')){
        //if the driver is already in the added drivers list, continue to next entry
        if(in_array($shift->id,self::$added_drivers) || in_array($shift->id,self::$blocked_drivers)){
            continue;
        }else{
            // add new driver to added driver list
            self::$added_drivers [] = $shift->id;

            // get driver personal details to display
            $driver = new Ent\StaffMember($shift->driver_id);
            $logtext = sprintf("    Adding new driver# %s (%s %s)\n",$shift->id,$driver->firstname,$driver->lastname);
            self::myLog($logtext);

            // add driver to temp DB
            $sql = "INSERT INTO shifts_temp (`id`,`available_time`,`stop_time`,`make_available`) VALUES ('$shift->id','$shift->start_time','$shift->stop_time','$shift->make_available')";
            Database::query($sql);

            //
            self::$drivers_added++;
            return $shift;
        }
        }
        //if we get here, we've run out of drivers to add
        $logtext = sprintf("\n\nCAN'T ADD ANY MORE DRIVERS\n");
        self::myLog($logtext);
        die("CAN'T ADD ANY MORE DRIVERS");
}


private static function getListOfAvailableShifts($pickup_time,$arrival_time){
    $pickup_time_str = $pickup_time->format("Y-m-d H:i:s");
    $arrival_time_str = $arrival_time->format("Y-m-d H:i:s");
    $sql = "SELECT * FROM shifts_temp WHERE available_time<='$pickup_time_str' AND stop_time>'$arrival_time_str' AND `make_available`='1'";
    // print_r($sql);
    $rs = Database::query($sql);
    return $rs;
}
}
?>
