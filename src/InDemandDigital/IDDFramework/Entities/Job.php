<?php
namespace InDemandDigital\IDDFramework\Entities;
use InDemandDigital\IDDFramework as IDD;


class Job extends Entity{

    public function getFutureJobs(){
        $sql = "SELECT gt_jobs.*,performances.artist_id AS artist_id FROM gt_jobs,performances,rooms,events WHERE gt_jobs.`performance_id`=performances.id AND performances.`room_id`=rooms.id AND rooms.`event`=events.id AND `pickup_time`>NOW() ORDER BY pickup_time";
        $result = IDD\Database::query($sql);
        return $result;
    }

    public function getJobsForEventID($event_id){
        $sql = "SELECT gt_jobs.*,performances.artist_id AS artist_id FROM gt_jobs,performances,rooms,events WHERE gt_jobs.`performance_id`=performances.id AND performances.`room_id`=rooms.id AND rooms.`event`=events.id AND rooms.`event`=$event_id ORDER BY pickup_time";
        $result = IDD\Database::query($sql);
        return self::convertResultSetToObjectArray($result);
    }


    public function echoAllJobsForEventID($event_id){
        $sql = "SELECT gt_jobs.*,performances.artist_id AS artist_id FROM gt_jobs,performances,rooms,events WHERE gt_jobs.`performance_id`=performances.id AND performances.`room_id`=rooms.id AND rooms.`event`=events.id AND rooms.`event`=$event_id ORDER BY pickup_time";
        $result = IDD\Database::query($sql);
        if($result->num_rows == 0){
            echo "No jobs for this event";
            return;
        }
        $data = self::convertResultSetToObjectArray($result);

        foreach ($data as $job) {
            $job->embellishJob();
        }

        $fields = ['pickup_time'=>'Pickup Time',
        'from_location'=>'From',
                    'to_location'=>'To',
                    'estimated_time'=>'Estimated Time',
        'artistname'=>'Artist',
        'artistname2'=>'Artist 2',

        'notes'=>'notes','locked'=>'Locked?','id'=>'id'
];
        IDD\EchoData::echoArrayToTable($data,'t01',$fields);
    }

    public function echoAllJobsByShiftForEventID($event_id){
        //find which shifts are working this event
        $sql = "SELECT assigned_shift FROM gt_jobs,performances,rooms,events WHERE gt_jobs.`performance_id`=performances.id AND performances.`room_id`=rooms.id AND rooms.`event`=events.id AND rooms.`event`=$event_id ORDER BY assigned_shift,pickup_time";
        $result = IDD\Database::query($sql);
        $shiftarray = [];
        while ($j = $result->fetch_object()) {
            if(!in_array($j->assigned_shift,$shiftarray)){
                $shiftarray [] = $j->assigned_shift;
            }
        }
        //get this shift's jobs
        foreach ($shiftarray as $shift) {
            self::echoJobsForShift($shift);
    }
}

    public static function echoJobsForShift($shift){
        // var_dump($shift);
        //driver details
        // $sql = "SELECT driver_id FROM `gt_shifts` WHERE gt_shifts.id='$shift'";
        // $result = IDD\Database::query($sql);
        // if($result->num_rows == 0){
        //     return;
        // }

        $shift = new Shift($shift);
        $driver = new StaffMember($shift->driver_id);
        $vehicle = new Vehicle($shift->vehicle_id);
        printf("<br>Schedule for driver (ShiftID#%s) - %s %s (%s) - in a %s %s %s seater",$shift->id,$driver->firstname, $driver->lastname,$shift->notes,$vehicle->make,$vehicle->model,$vehicle->capacity);

        //job details
        // $sql = "SELECT * FROM `gt_jobs` WHERE `assigned_shift`='$shift' ORDER BY `pickup_time`";
        $sql = "SELECT gt_jobs.*,performances.artist_id AS artist_id FROM gt_jobs,performances WHERE gt_jobs.`performance_id`=performances.id AND `assigned_shift`='$shift->id' ORDER BY pickup_time";

        $result = IDD\Database::query($sql);
        $data = self::convertResultSetToObjectArray($result);

        foreach($data as $job){
            $job->embellishJob();
            $alljobs [] = $job;
        }
        $fields = ['pickup_time'=>'Pickup Time',
        'from_location'=>'From',
                    'to_location'=>'To',
                    'estimated_time'=>'Estimated Time',
        'artistname'=>'Artist',
        'artistname2'=>'Artist 2',
        'pickup_name'=>'Pickup Name',
        'passenger_contact'=>'Contact Number',
        'notes'=>'notes',
        'passengers'=>'passengers','id'=>'Job ID#'
        ];
        IDD\EchoData::echoArrayToTable($alljobs,'t01',$fields);
        $alljobs = [];
    }

    public function embellishJob(){
        // var_dump($this);

        $from_location = new Location($this->from_location);
        $this->from_location = $from_location->name;
        $to_location = new Location($this->to_location);
        $this->to_location = $to_location->name;
        $artist = new Artist($this->artist_id);
        $this->artistname = $artist->name;
        $this->passenger_contact = $artist->phone;
        $pt = new \DateTime($this->pickup_time);
        $this->pickup_time = $pt->format('d/m H:i');
        $journey = IDD\LocationMatrix::getJourney($from_location->id,$to_location->id);
        $this->estimated_time = $journey->duration_in_traffic->text;
        if($this->pickup_name == ""){
            if($artist->firstname != ""){
                $this->pickup_name = $artist->firstname . " " . $artist->lastname;
            }else{
                $this->pickup_name = $artist->name;
            }
        }
        if($this->performance_id_2 != "" && $this->performance_id_2 != "0"){
            $p2 = new Performance($this->performance_id_2);
            $a2 = new Artist($p2->artist_id);
            $this->artistname2 = $a2->name;
        }
        if($this->performance_id_3 != "" &&$this->performance_id_3 != "0"){
            $p3 = new Performance($this->performance_id_2);
            $a3 = new Artist($p3->artist_id);
            $this->artistname3 = $a3->name;
        }
    }

    public function embellishJobWithObjects(){
        //embellish job
        $journey = IDD\LocationMatrix::getJourney($this->from_location,$this->to_location);
        $this->from = new Location($this->from_location);
        $this->to = new Location($this->to_location);

        // var_dump($this->pickup_time);
            $arrival_time = new \DateTime($this->pickup_time);
        $this->pickup_time = new \DateTime($this->pickup_time);
// var_dump($this->pickup_time);
        $this->duration = new \DateInterval('PT'.$journey->duration_in_traffic->value.'S');
        $this->arrival_time = $arrival_time->add($this->duration);
        $this->pickup_time_str = $this->pickup_time->format('c');
        $this->arrival_time_str = $this->arrival_time->format('c');
        if($this->assigned_shift){
            $this->assigned_shift = new Shift($this->assigned_shift);
        }
        //end embellish
    }
}
?>
