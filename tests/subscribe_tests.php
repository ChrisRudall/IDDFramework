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


// $vars = ['email' => 'steve@indemandmusic.com'];
var_dump(phpversion());
// var_dump( IDD\Mail::addSubscriber($vars));
$d = 'hello';
$key = Key::createNewRandomKey();
$e = Crypto::encrypt($d,$key);
echo Crypto::decrypt($e,$key);

 ?>
