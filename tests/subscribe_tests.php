<?php
namespace InDemandDigital;
session_start();
set_include_path('includes');
Date_default_timezone_set('UTC');
require '../vendor/autoload.php';

use \InDemandDigital\IDDFramework\Entities AS Ent;
use \InDemandDigital\IDDFramework AS IDD;
use \InDemandDigital\IDDFramework\Tests\Debug AS Debug;
use Defuse\Crypto\Crypto;
use Defuse\Crypto\Key;


$vars = ['email' => 'stu@indemandmusic.com'];

echo IDD\Mail::addSubscriber($vars);

 ?>
