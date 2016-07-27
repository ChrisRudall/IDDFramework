<?php
namespace InDemandDigital;


class EchoData{

    public static function echoArrayToTable($data,$tableID = '',$fields = null){
        if(gettype($data) != 'array'){
            echo "Array type required";
            return;
        }
        echo "<table class='datatable' id='$tableID'>";
        self::echoHeaderRow($data,$fields);
        foreach ($data as $object) {
            self::echoSingleObjectAsRow($object,$fields);
        }
        echo "</table>";
        return;
    }

    private static function echoSingleObjectAsRow($object,$fields){
        echo "<tr>";
        echo "<td class='editbutton'>";
        $entitytype = get_class($object);
        $thisurl = $GLOBALS['thisurl'];
        echo "<a href='edit_entity.php?type=$entitytype&id=$object->id&returnurl=$thisurl'><img class='editbutton' src='assets/images/edit_icon.png'></a>";
        echo "</td>";

        if($fields === null){
            $array = get_object_vars($object);

            $fields = array_keys($array);
            foreach ($fields as $field) {
                echo "<td>";
                echo $object->$field;
                echo "</td>";
            }
        }else{
            foreach ($fields as $field => $displaytext) {
                echo "<td>";
                echo $object->$field;
                echo "</td>";
            }
        }
        echo "</tr>";
        return;
    }

    private static function echoHeaderRow($data,$fields){
        if($fields === null){
            $object = get_object_vars($data[0]);
            $fields = array_keys($object);
        }

        echo "<tr id='headerrow'>";
        echo "<th>Edit</th>";
        foreach ($fields as $field => $displaytext) {
            echo "<th>$displaytext</th>";
        }
        echo "</tr>";
    }


    public static function echoObjectToEdit($e){
        echo "<div class='editfieldname'>type</div>";
        self::echoEditFieldAstext('entity_type',get_class($e),'readonly');
        foreach ($e as $key => $value) {
        	echo "<div class='editfieldname'>$key</div>";
        	if ($key === "id") {
                self::echoEditFieldAsText($key,$value,"readonly");
        	}elseif(strpos($key,'location')){
                self::echoEditFieldAsLocationList($key,$value);
            }elseif (strpos($key,'perf') !== False) {
                self::echoEditFieldAsPerformanceList($key,$value);
            }else{
                self::echoEditFieldAsText($key,$value);
        	}
        }
        return;
    }

    private static function echoEditFieldAsText($key,$value,$readonly = ''){
        echo "<div class='editfielddata'><input type='text' $readonly size='40' name='$key' value='$value'></div><br>";
    }
    private static function echoEditFieldAsLocationList($key,$value){
        $sql = "SELECT id,name FROM locations ORDER BY name";
        $r = Database::query($sql);

        echo "<select name='$key'>";

        for ($f=0;$f<$r->num_rows;++$f){
        	$location = $r->fetch_object();
        	$selected = "";
        	if ($location->id == $value){$selected = "selected";}
        	echo "<option id='$location->id' value='$location->id' $selected>$location->name</option>";
        }
        echo "<select><br>";
    }


    private static function echoEditFieldAsPerformanceList($key,$value){
        $sql = "SELECT
`events`.`name` AS 'eventname',
`rooms`.`name` AS 'roomname',
`artists`.`name` AS 'artistname',
`performances`.`id` AS 'id'
FROM `performances`,`rooms`,`events`,`artists`
WHERE
`performances`.`room_id` = `rooms`.`id` AND
`rooms`.`event` = `events`.`id` AND
`performances`.`artist_id`= `artists`.`id`
ORDER BY
eventname,roomname,artistname";
        $r = Database::query($sql);

        echo "<select name='$key'>";

        for ($f=0;$f<$r->num_rows;++$f){
            $perf = $r->fetch_object();
            $selected = "";
            if ($perf->id == $value){$selected = "selected";}
            echo "<option id='$perf->id' value='$perf->id' $selected>$perf->eventname | $perf->roomname | $perf->artistname</option>";
        }
        echo "<select><br>";
    }
}
?>
