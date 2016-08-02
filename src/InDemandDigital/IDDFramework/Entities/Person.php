<?php
namespace InDemandDigital\IDDFramework\Entities;
use InDemandDigital\IDDFramework as IDD;

class Person extends Entity{
    public static function getPersonForUUID($uuid){
        IDD\Database::connectToMailingList();
        $sql = "SELECT * FROM `public` WHERE `uuid`='$uuid'";
        $rs = IDD\Database::query($sql);
        return $rs->fetch_object('\InDemandDigital\IDDFramework\Entities\Person');
    }

}
?>
