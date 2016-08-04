<?php
namespace InDemandDigital\IDDFramework;
require "../vendor/autoload.php";
use InDemandDigital\IDDFramework\Entities as Ent;
//
// set_include_path("includes/");
// require "config.php";

$mailshot_id = $argv[1];

Database::connectToMailingList();
//get mailshot info
$shot = Mail::getMailshotWithName($mailshot_id);
//get list info
$list = Mail::getListWithId($shot->list);

//get sender info
$sender = Mail::getSenderWithId($shot->sender);

$limit = None; //or None

// Mail::$fromname = "Reminisce Festival 2016";
// Mail::$fromaddress = "noreply@reminiscefestival.com";
// Mail::$template_subject = "Secure your ticket now for just £40";
// Mail::$senddate = "2016-07-28 16:00:00";
// Mail::$template_unsubscribeurl = "http://listmanager.indemandmusic.com/unsubscribe.php?email=##to_address##&uuid=##UUID##&senderid=2&listtype=3";

// Mail::$template_file = "reminisce_mail_03";
// Mail::setTemplates();

$sql = "SELECT `uuid` FROM $list->listname WHERE {$sender->shortcode}_optout != '1'";
print_r($sql);
$r = Database::query($sql);

$counter = 0;
while ($person = $r->fetch_object()) {
    $e = new Mail;
    // $e->to_address = decode($person->email);
    // $e->to_name = decode($person->name);
    $e->uuid = $person->uuid;
    $e->mailshot_id = $shot->mailshot_id;
    $e->send_date = $shot->send_date;

    //
    // $e->replaceTags();
    // $e->buildMessage();
    $queue [] = $e->queue();
    $counter++;
    if($limit !== None && $counter >= $limit){
        print_r("Limit<br>");
        break;
    }
}
printf("Added %s emails to queue.<br>Start id: %s<br>End id: %s",$counter,$queue[0],end($queue));
$r->close();


Database::closeConnection();





?>
