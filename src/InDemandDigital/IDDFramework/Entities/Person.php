<?php
namespace InDemandDigital\IDDFramework\Entities;
use InDemandDigital\IDDFramework as IDD;

class Person extends Entity{

    public function __construct($i){
        if($i){
            trigger_error("Please use custom function to create a person",E_USER_ERROR);
        }
    }

    public static function getPersonForUUID($uuid){
        IDD\Database::connectToMailingList();
        $sql = "SELECT * FROM `public` WHERE `uuid`='$uuid'";
        $rs = IDD\Database::query($sql);
        return $rs->fetch_object('\InDemandDigital\IDDFramework\Entities\Person');
    }

    public static function getStaffMemberWithID($id){
        // IDD\Database::connectToMailingList();
        $sql = "SELECT * FROM `staff` WHERE `id`='$id'";
        $rs = IDD\Database::query($sql);
        return $rs->fetch_object('\InDemandDigital\IDDFramework\Entities\Person');
    }

}
?>
