<?php
namespace InDemandDigital\IDDFramework;
session_start();
Date_default_timezone_set('UTC');

require $_SERVER['DOCUMENT_ROOT']."/vendor/autoload.php";


$mailshot_id = $_POST['mailshot_id'];
$person = $_POST['json'];

Database::connectToMailingList();
//get mailshot info
$shot = Mail::getMailshotWithName($mailshot_id);

//get list info
$list = Mail::getListWithId($shot->list);

//get sender info
$sender = Mail::getSenderWithId($shot->sender);

$e = new Mail;

$e->uuid = $person['uuid'];
$e->mailshot_id = $shot->mailshot_id;
$e->send_date = $shot->send_date;

var_dump($e);
$e->queue();




Database::closeConnection();





?>

?>
