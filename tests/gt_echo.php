<?php

namespace InDemandDigital;
session_start();
set_include_path('includes');
Date_default_timezone_set('UTC');
require '../vendor/autoload.php';

use \InDemandDigital\IDDFramework\Entities AS Ent;
use \InDemandDigital\IDDFramework AS IDD;
use \InDemandDigital\IDDFramework\Tests\Debug AS Debug;
use \InDemandDigital\IDDFramework\Crypto;
use \InDemandDigital\IDDFramework\Crypto\Exception as Ex;


 ?>

 <!DOCTYPE HTML>
<html>
<head>

</head>
<body>
<?php
IDD\Database::connect();
IDD\GroundTransport::calculateScheduleForEventID(4);
// Ent\Job::echoAllJobsByShiftForEventID(4);
?>

    </body>
</html>
