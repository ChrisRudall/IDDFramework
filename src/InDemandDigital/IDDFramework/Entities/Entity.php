<?php
namespace InDemandDigital\IDDFramework\Entities;
use InDemandDigital\IDDFramework as IDD;
use InDemandDigital\IDDFramework\Tests\Debug as Debug;


class Entity{
//table index - which db table stores which entities
    protected static $table_index = ["Artist" => "artists",
                                    "Event" => "events",
                                "Job" => "gt_jobs",
                            "Shift" => "gt_shifts",
                        "Vehicle" => "gt_vehicles",
                    "Location" => "locations",
                "Performance" => "performances",
            "Room" => "rooms",
        "StaffMember" => "staff"];

//STATIC FUNCTIONS
    public static function getAllAsSQL(){
        $table = self::getTableNameFromEntity();
        $sql = "SELECT * FROM $table";
        return IDD\Database::query($sql);
    }
    public static function getAllRawWhere($x,$y){
        $table = self::getTableNameFromEntity();
        $sql = "SELECT * FROM $table WHERE `$x`='$y'";
        return IDD\Database::query($sql);
    }
    public static function getAllAsArray(){
        $result = self::getAllAsSQL();
        while ($r = $result->fetch_object()){
            $array [$r->id] = $r;
        }
        return $array;
    }
    protected static function exportArrayToCSV($array){
        $nn = new \DateTime();
        $n = $nn->format("YmdHis");
        $filename = "data/exports/".self::getEntityName()."_export_".$n.".csv";
        try{
            $file = fopen($filename,"w");
            fputcsv($file,array_keys($array[0]));
            foreach ($array as $r)
              {
                fputcsv($file,$r);
              }
              fclose($file);
          }catch(Exception $e){
              Echo "ERROR: ".$e->getMessage();
          }
          return $filename;
    }

//INSTANCE FUNCTIONS
//PUBLIC
    public function __construct($id = None){
        if ($id !== None){
            // Debug::NicePrint("Constructing type: ".getType($id));
            if(!is_object($id)){
                $o = self::getIDprotected($id);
            }else{
                $o = $id;
            }

            if($o){
                foreach ($o as $key => $value) {
                    $this->$key = $value;
                }
            }else{
                $this->error = "No data";
            }
        }
    }
    public function getObjectForID($id,$classtype = None){
        // debug::NicePrint("getObjectForID");
        if($classtype === None){
            $classtype = get_called_class();
        }
        return new $classtype($id);
    }


//PRIVATE
    protected function getIDprotected($id){
        // debug::NicePrint("getIDprotected");
        $table = $this->getTableNameFromEntity();
        $sql = "SELECT * FROM $table WHERE `id`=$id";
        Debug::NicePrint($sql);
        $rs = IDD\Database::query($sql);
        return IDD\Encryptor::decodeObject( $rs->fetch_object() );
    }

//GET ENTITY AND TABLE META
    private function getEntityName($object = None){
        if ($object !== None){
            return end(explode('\\', get_class($object)));
        }else{
        return end(explode('\\', get_called_class()));
    }
    }

    private function getTableNameFromEntity($entity_name = None){
        if ($entity_name === None){
            $entity_name = self::getEntityName();
        }
        return self::$table_index[$entity_name];
    }

    protected function convertResultSetToObjectArray($result,$classtype = None){
        while ($r = $result->fetch_object()){
            $array [] = self::getObjectForID($r,$classtype);
        }
        return $array;
    }


    public function writeToDatabase(){

        $type = self::getEntityName($this);
        $table = self::getTableNameFromEntity($type);
        foreach ($this as $key => $value) {
            if ($value == ''){continue;}
            $s [] = sprintf("`$key`='$value'");
        }
        $s = implode($s,',');

        $sql = "UPDATE `$table` SET $s WHERE `id`='$this->id'";
// print_r($sql);
        return IDD\Database::query($sql);
    }

    public static function createEmptyEntity($type){
        $table =  end(explode('\\', $type));
        $table = self::$table_index[$table];

        $sql = "INSERT INTO `$table` () VALUES ()";
        // echo $sql;
        IDD\Database::query($sql);
        return IDD\Database::getInsertedID();
    }

    public static function deleteEntityWithID($type,$id){
        // $table =  end(explode('\\', $type));
        $table = self::$table_index[$type];
        $sql = "DELETE FROM `$table` WHERE `id` IN ('$id')";
        // echo $sql;
        return IDD\Database::query($sql);
    }

}
?>
