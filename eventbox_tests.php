<?php namespace InDemandDigital;
session_start();
set_include_path('includes');
Date_default_timezone_set('UTC');


require 'vendor/autoload.php';

use \InDemandDigital\IDDFramework\Entities AS Ent;
use \InDemandDigital\IDDFramework AS IDD;
use \InDemandDigital\IDDFramework\Tests\Debug AS Debug;


 ?>

 <!DOCTYPE HTML>
<html>
<head>
<link rel="stylesheet" type="text/css" href="css/eventbox.css">
<style>
body{
    /*background-color: black;*/
}
</style>
</head>
<body>
<?php
Debug::$debug_level = 2;

$eventbox = new IDD\Eventbox(8);
$eventbox->roomlimit = 6;
$eventbox->showtag = true;
// $eventbox->height = 200;

$eventbox->showAllRooms();
// var_dump($eventbox);
$eventbox = new IDD\Eventbox(8);
// $eventbox->room_offset = 0;
$eventbox->showtag = true;
// $eventbox->height = 200;
// $eventbox->feature = True;
$eventbox->feature_offset = 0;

$eventbox->showRoom();

$eventbox = new IDD\Eventbox(8);
$eventbox->room_offset = 1;
$eventbox->showtag = true;
// $eventbox->height = 200;
// $eventbox->feature = True;
$eventbox->artistlimit = 4;
$eventbox->feature_offset = 1;
$eventbox->dark = True;

$eventbox->showRoom();
?>

    </body>
</html>
