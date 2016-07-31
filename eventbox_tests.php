<?php namespace InDemandDigital;
session_start();
set_include_path('includes');
Date_default_timezone_set('UTC');

// check for local
if($_SERVER['REMOTE_ADDR'] == "::1" || $_SERVER['REMOTE_ADDR'] == "127.0.0.1" || $_SERVER['HOME'] == "/Users/chrisrudall"){
    $_SESSION['local'] = True;
}else{
    $_SESSION['local'] = False;
}

require 'vendor/autoload.php';

use \InDemandDigital\IDDFramework\Entities AS Ent;
use \InDemandDigital\IDDFramework AS IDD;
use \InDemandDigital\IDDFramework\Tests\Debug AS Debug;


 ?>

 <!DOCTYPE HTML>
<html>
<head>
<link rel="stylesheet" type="text/css" href="css/eventbox.css">
</head>
<body>
<?php
Debug::$debug_level = 0;

$eventbox = new IDD\Eventbox(8);
$eventbox->roomlimit = 6;
$eventbox->showtag = true;
$eventbox->height = 200;

$eventbox->showAllRooms();

$eventbox = new IDD\Eventbox(8);
$eventbox->offset = 1;
$eventbox->showtag = true;
$eventbox->height = 400;
$eventbox->showRoom();
?>

    </body>
</html>
