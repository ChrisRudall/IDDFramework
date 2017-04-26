<?php
namespace InDemandDigital\IDDFramework;
// use \InDemandDigital\IDDFramework AS IDD;
session_start();
Date_default_timezone_set('UTC');
require '../vendor/autoload.php';

$c = 0;
Database::connectToMailingList();

$sql = "DROP TABLE `public_decoded`";
Database::query($sql);


$sql = "CREATE TABLE `public_decoded` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(512) DEFAULT NULL,
  `firstname` varchar(512) DEFAULT NULL,
  `lastname` varchar(512) DEFAULT NULL,
  `email` varchar(512) DEFAULT NULL,
  `email_created` timestamp NULL DEFAULT NULL,
  `email_viewcount` varchar(255) DEFAULT NULL,
  `event` varchar(255) DEFAULT NULL,
  `orderid` varchar(255) DEFAULT NULL,
  `venue` varchar(255) DEFAULT NULL,
  `tickettype` varchar(255) DEFAULT NULL,
  `dob` varchar(512) DEFAULT NULL,
  `sex` varchar(5) DEFAULT NULL COMMENT 'true=male',
  `address` varchar(512) DEFAULT NULL,
  `status` varchar(512) DEFAULT NULL,
  `postcode` varchar(512) DEFAULT NULL,
  `mobile` varchar(512) DEFAULT NULL,
  `facebook` varchar(512) DEFAULT NULL,
  `tickets` int(11) DEFAULT NULL,
  `tickets_requested` int(1) DEFAULT NULL,
  `spend` varchar(255) DEFAULT NULL,
  `ip` varchar(16) DEFAULT NULL,
  `os` varchar(32) DEFAULT NULL,
  `reminisce` int(11) DEFAULT '0',
  `escapegravity` int(11) DEFAULT '0',
  `btta` int(11) DEFAULT '0',
  `statereunion` int(11) DEFAULT '0',
  `garlandsclub` int(1) NOT NULL DEFAULT '0',
  `bedlamclub` int(1) NOT NULL DEFAULT '0',
  `davegraham` int(1) NOT NULL DEFAULT '0',
  `uuid` varchar(255) DEFAULT NULL,
  `spin_optout` int(1) DEFAULT '0',
  `remfest_optout` int(1) DEFAULT '0',
  `garlandsclub_optout` int(1) NOT NULL DEFAULT '0',
  `bedlamclub_optout` int(1) NOT NULL DEFAULT '0',
  `davegraham_optout` int(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uuid` (`uuid`)
) ENGINE=InnoDB AUTO_INCREMENT=24585 DEFAULT CHARSET=utf8;";

Database::query($sql);

/* set autocommit to off */
Database::autocommit(FALSE);
$table = 'public';

$sql = "SELECT * FROM `$table`";
$data = Database::query($sql);
echo $data->num_rows;
while($person = $data->fetch_object('InDemandDigital\IDDFramework\Entities\Person')){
    try{
        $person = Encryptor::decodeObject($person);
        $c++;

        addPerson($person);
    }catch(\Exception $e){
        trigger_error("Decryption failed for person: <br>",E_USER_WARNING);
        var_dump($person);
    }
}




function addPerson($person){
    //build sql string
    foreach($person as $key => $value){
        // $sqlvar_array[] = "`$key`='{$person->$key}'";
        if ($key != 'id'){
            $sqlvar_array[] = "'{$value}'";
            $sqlcol_array[] = "'{$key}'";
        }
    }

    $sqlvar_string = implode(',',$sqlvar_array);
    $sqlcol_string = implode(',',$sqlcol_array);

    $sql = "INSERT INTO `public_decoded` (`email`,`name`,`firstname`,`lastname`,`dob`,`address`,`postcode`,`mobile`,`facebook`,`tickets_requested`,`sex`,`uuid`,`email_created`)
    VALUES ('$person->email','$person->name','$person->firstname','$person->lastname','$person->dob','$person->address','$person->postcode','$person->mobile','$person->facebook','$person->tickets_requested','$person->sex','$person->uuid','$person->email_created');";

    // $sql = "INSERT INTO `public_decoded` (`email`,`name`,`firstname`,`lastname`,`dob`,`address`,`postcode`,`mobile`,`facebook`,`tickets_requested`,`sex`)
    // VALUES ('$person->email','$person->name','$person->firstname','$person->lastname','$person->dob','$person->address','$person->postcode','$person->mobile','$person->facebook','$person->tickets_requested','$person->sex');";


    print($sql);
    try{
        Database::query($sql);
    }catch(\Exception $e){
        echo "SQL ERROR";
        print_r($sql);
    }
}


Database::commit();

Database::closeConnection();
echo "done";
?>
