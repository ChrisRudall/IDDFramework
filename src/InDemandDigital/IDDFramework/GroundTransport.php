<?php
namespace InDemandDigital\IDDFramework;
use InDemandDigital\Tests\Debug as Debug;
use InDemandDigital\Entities as Ent;
use DateTime;
use DateInterval;
// $p = get_include_path();
// set_include_path("v2/src/InDemandDigital/Portal/");
// require_once 'Database.php';
// require_once 'Entities/StaffMember.php';
//
// set_include_path($p);
class GroundTransport{
public static $drivers_added = 0;
public static $added_drivers = [];
private static $locked_drivers = [];
private static $logfile;
private static $pointer = 0;

private static function addDriver($job,$id = None){
    if($id != None){
        print_r("Trying to add driver id ".$id);print_r("<br>");
        $sql = "SELECT * FROM `gt_shifts` WHERE `start_time`<'$job->pickup_time_str' AND `stop_time`>'$job->arrival_time_str' AND `id`='$id'";
    }else{
        $sql = "SELECT * FROM `gt_shifts` WHERE `start_time`<'$job->pickup_time_str' AND `stop_time`>'$job->arrival_time_str'";
    }
// var_dump($sql);
// print_r($forjob->pickup_time);
    $rs = database::query($sql);
    while ($shift = $rs->fetch_object('InDemandDigital\Entities\Shift')){
        // var_dump(self::$added_drivers);
        if(in_array($shift->id,self::$added_drivers) || in_array($shift->id,self::$locked_drivers)){
            continue;
        }else{
            self::$added_drivers [] = $shift->id;
            $driver = new Ent\StaffMember($shift->driver_id);
            $logtext = sprintf("    Adding new driver# %s (%s %s)\n",$shift->id,$driver->firstname,$driver->lastname);
            print_r("adding new driver<br>");
            fwrite(self::$logfile,$logtext);
            $sql = "INSERT INTO shifts_temp (`id`,`available_time`,`stop_time`) VALUES ('$shift->id','$shift->start_time','$shift->stop_time')";
            Database::query($sql);
            self::$drivers_added++;
            return $shift;
        }
        }
        $logtext = sprintf("\n\nCAN'T ADD ANY MORE DRIVERS\n");
        fwrite(self::$logfile,$logtext);
        die("CAN'T ADD ANY MORE DRIVERS");
        return False;

    }

private static function addLockedDriver($id){

}

private static function getListOfAvailableShifts($pickup_time,$arrival_time){
    $pickup_time_str = $pickup_time->format("Y-m-d H:i:s");
    $arrival_time_str = $arrival_time->format("Y-m-d H:i:s");
    $sql = "SELECT * FROM shifts_temp WHERE available_time<'$pickup_time_str' AND stop_time>'$arrival_time_str' ";
    // print_r($sql);
    $rs = Database::query($sql);
    return $rs;
}

public static function calculateScheduleForEventID($event_id){

    self::createTempTable();

    //logging
    $nn = new DateTime();
    $n = $nn->format("YmdHis");
    self::$logfile = fopen("data/logs/calculateSchedules_".$n.".txt",'w') or die("Unable to open file!");

    //get all the jobs
    $alljobs = Ent\Job::getFutureJobs();
    while ($job = $alljobs->fetch_object('InDemandDigital\Entities\Job')) {
        self::$pointer++;
        $success = 0;
        $job->embellishJobWithObjects();

        print_r(self::$pointer."<br>");
        unset(self::$locked_drivers[self::$pointer]);

        var_dump(self::$locked_drivers);
        print_r("<br>");
        // //get a list of all available drivers
        // $rs = self::getListOfAvailableShifts($job->pickup_time,$job->arrival_time);
        //
        // //if no drivers returned, add a driver
        // while($rs->num_rows === 0){
        //     if(self::addDriver($job) !== False){
        //         $rs = self::getListOfAvailableShifts($job->pickup_time,$job->arrival_time);
        //     }else{
        //         return;
        //         break;
        //     }
        // }

        $rs = self::getListOfAvailableShifts($job->pickup_time,$job->arrival_time);
        while($rs->num_rows === 0){
            self::addDriver($job);
            $rs = self::getListOfAvailableShifts($job->pickup_time,$job->arrival_time);
        }



// switch out here to either assign lockd driver or choose best fit
//if locked driver not in list, callback with array of locked drivers ....
//move pointer back until find last job assigned to locked driver



        //work out each drivers distance to job
        while( $shift = $rs->fetch_object('InDemandDigital\Entities\Shift')){
            $shift->setLeadTime($job);
        }

        // var_dump($job->pickup_time);


        //selects best fit job
        $sql = "SELECT * FROM shifts_temp WHERE available_time<'$job->pickup_time_str' AND stop_time>'$job->arrival_time_str' AND `would_have_to_leave_at`>`available_time` ORDER BY would_have_to_leave_at DESC";
        // print_r($sql);
        $rs = Database::query($sql);


        if($job->locked == '1' && in_array($job->assigned_shift,self::$added_drivers)){
            printf('job %s at %s is locked to %s and driver is already added<br>',self::$pointer,$job->from->name,$job->assigned_shift);

            //try and add the locked driver from existing list
            while ($shift = $rs->fetch_object('InDemandDigital\Entities\Shift')) {
                // print_r($shift);
                if($job->assigned_shift == $shift->id){
                    $job->assigned_shift = $shift->id;
                    printf('success - assigned locked driver %s to job at pointer %s<br>',$shift->id,self::$pointer);
                    $success = 1;
                    break;
                }
            }
            if($shift == NULL){ // we got through list without assigning
                //driver was added but not available
                print_r("driver added but unavailable<br>");

                // unset driver from last job
                var_dump($job->assigned_shift);
                $sql = "UPDATE shifts_temp SET `available_time`=`previous_available_time` WHERE `id`='{$job->assigned_shift}'";
                Database::query($sql);

                // go backwards
                self::$locked_drivers [self::$pointer] = $job->assigned_shift;
                print_r('added driver to lock array<br>');



                do{
                    print_r('--go back one<br>');
                    self::$pointer--;
                    $alljobs->data_seek(self::$pointer);

                    print_r('--go check one<br>');
                    $tempjob = $alljobs->fetch_object('InDemandDigital\Entities\Job');
                    self::$pointer++;

                    print_r('--go back one<br>');
                    self::$pointer--;
                    $alljobs->data_seek(self::$pointer);

                    print_r('--go back one<br>');
                    self::$pointer--;
                    $alljobs->data_seek(self::$pointer);


                    // $alljobs->data_seek(self::$pointer);
                }while($tempjob->assigned_shift->id != $job->assigned_shift->id);
                $alljobs->data_seek(self::$pointer);
            }


        }elseif($job->locked == '1' && !in_array($job->assigned_shift,self::$added_drivers)){
                //job is locked and driver not added yet - attempt to add
                $job->assigned_shift = self::addDriver($job,$job->assigned_shift);
                if($job->assigned_shift === False){
                        return; //no more drivers
                }

        }else{
            //unlocked shift
            // if($rs->num_rows === 0){
            //     $assigned_shift = self::addDriver($job);
            //     if($assigned_shift === False){
            //         return; //no more drivers
            //     }
            // }
            do{
                $job->assigned_shift = $rs->fetch_object('InDemandDigital\Entities\Shift');

                if(empty($job->assigned_shift)){
                    // print_r('call');
                    $assigned_shift = self::addDriver($job);
                    if($assigned_shift === False){
                        return; //no more drivers
                    }
                }
                $success = 1;
            }
            while(in_array($job->assigned_shift->id,self::$locked_drivers));
        }
        if(gettype($job->assigned_shift) == 'string'){
            $job->assigned_shift = new Ent\Shift($job->assigned_shift);
        }


        // print_r($job->assigned_shift);



        //we found a driver, now if job is unlocked, assign best fit
        //if locked then check and assign locked in driver else...
        // if($job->locked == '1'){
        //     $shifts = $rs->fetch_all(MYSQL_ASSOC);
        //     print_r($shifts);
        //     if(in_array($job->assigned_shift,$shifts[0])){
        //         print_r("assign locked driver<br>"); // no need to reassign
        //         // $lockedshifts [] = $job->assigned_shift; -- opposite - pop from array
        //     }else{
        //         print_r("locked driver unavailable<br>");
        //         // locked driver is unavaiable
        //         // self::reverse($alljobs,$job->assigned_shift);
        //         // $lockedshifts [] = $job->assigned_shift;
        //         // continue;
        //     }
        // }else{
        //     // do{
        //     //             $assigned_shift = $rs->fetch_object();
        //     // }while(!in_array($assigned_shift->id,$lockedshifts));
        //
        // }

// var_dump($job->assigned_shift->available_time);
        //update job to active
        $previous_available_time = $job->assigned_shift->available_time;
        $available_time = $job->arrival_time;
        $arrival_prettystr = $job->arrival_time->format('H:i');
        $available_time->modify('+10 minutes');
        $available_time_str = $available_time->format("Y-m-d H:i:s");
        $sql = "UPDATE shifts_temp SET `available_time`='$available_time_str',`previous_available_time`='$previous_available_time',`current_location`='{$job->to->id}' WHERE `id`='{$job->assigned_shift->id}'";
// print_r($sql);
        Database::query($sql);

        if($success == 1){
            //here we need to write assigned shift to the job in DB
            $sql = "UPDATE `gt_jobs` SET `assigned_shift`='{$job->assigned_shift->id}' WHERE `id`='$job->id'";
            Database::query($sql);
            $pickup_prettystr = $job->pickup_time->format('H:i');
            $logtext = sprintf("Driver ID#%s pickup from %s at %s to %s arriving approx %s\n",$job->assigned_shift->id,$job->from->name,$pickup_prettystr,$job->to->name,$arrival_prettystr);
            fwrite(self::$logfile,$logtext);
            print_r($logtext."<br>");
            self::printShiftStatuses();
        }

    }//end of job loop

    $logtext = sprintf("\nTotal of %s drivers needed",self::$drivers_added);
    fwrite(self::$logfile,$logtext);
    fclose(self::$logfile);
    print_r($logtext);
    //drop temp table
    $sql = "DROP TABLE shifts_temp";
    Database::query($sql);

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

    $sql = "CREATE TABLE shifts_temp (id int(11) unsigned NOT NULL AUTO_INCREMENT,available_time DateTime,previous_available_time DateTime,stop_time datetime,current_location int(2) DEFAULT 11,would_have_to_leave_at datetime,PRIMARY KEY (`id`)) ENGINE=MEMORY";
    Database::query($sql);
}


}
?>
