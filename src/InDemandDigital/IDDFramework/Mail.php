<?php
namespace InDemandDigital;

class Mail{
    const unsubscribeUrl = "http://listmanager.indemandmusic.com/unsubscribe.php?mailshot_id=##mailshot_id##&uuid=##UUID##";

    public static $test_mode = 0; //0=off 1=echo 2=send to me
    private static $logfile = None;
    private static $options;

    // SET CONTENT
    public  static $fromname = "Default name";
    public  static $fromaddress = "mail@example.com";
    public static $template_file;
    public static $template_text;
    public static $template_html;
    public static $template_subject;
    public static $template_unsubscribeurl;
    public static $senddate;

    public  $to_address = "Default to";
    public  $to_name = "";

    public  $uuid = "UUID";
    public  $htmlheaders = "";
    public $greeting = "Hi,";
    public $unsubscribeurl;

    public function __construct(){
        // $this->subject = self::$template_subject;
        // $this->bodytext = self::$template_text;
        // $this->bodyhtml = self::$template_html;
        // $this->unsubscribeurl = self::$template_unsubscribeurl;
        // $this->senddate = self::$senddate;

        // parent::__construct();
    }

public function buildMessage(){
    //DONT TAB IN TEXT
// SEND CODE FUNCTION
$fromname = $this->sender->name;
$fromaddress = $this->sender->from_address;
// Generate a random boundary string
$mime_boundary = '_x'.sha1(time()).'x';
$bodytext = $this->bodytext;
$bodyhtml = $this->bodyhtml;

// Using the heredoc syntax to declare the headers
$this->headers = <<<HEADERS
From: $fromname <$fromaddress>
MIME-Version: 1.0
Content-Type: multipart/alternative; charset=utf-8;
 boundary="PHP-alt$mime_boundary"
HEADERS;

// Use our boundary string to create plain text and HTML versions
$this->html = <<<MESSAGE
--PHP-alt$mime_boundary
Content-Type: text/plain; charset=utf-8
Content-Transfer-Encoding: 7bit

$bodytext

--PHP-alt$mime_boundary
Content-type: text/html; charset=utf-8
Content-Transfer-Encoding: 7bit

<!DOCTYPE html>
<html>
<head>
$htmlheaders
</head>
<body>
$bodyhtml
</body>
</html>

--PHP-alt$mime_boundary--
MESSAGE;
}

private function realSend(){
    // print_r(utf8_encode($this->shot->subject));
    return mail($this->recipient->email, utf8_encode($this->shot->subject), utf8_encode($this->html), utf8_encode($this->headers));
}

public function queue(){
    $sql = "SELECT count(*) FROM email_queue";
    if(!Database::query($sql)){
        self::createQueueTable();
    }
    // $htmlbody_safe = Database::test_input($this->htmlbody);
    // $htmlheaders_safe = Database::test_input($this->htmlheaders);
    $sql = "INSERT INTO `email_queue` (`id`, `uuid`, `mailshot_id`,`send_date`) VALUES (NULL, '$this->uuid', '$this->mailshot_id','$this->send_date');";
    Database::query($sql);
    // print_r($sql);
    $id = Database::getInsertedID();
    $logtext = sprintf("Queued iD%s For %s %s",$id,$this->send_date,$this->uuid);
    self::log($logtext);
    return $id;

}

private static function createQueueTable(){
    $sql = "CREATE TABLE `email_queue` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(255),
  `mailshot_id` varchar(8),
  `send_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;";
    Database::query($sql);
}

private static function dropQueueTable(){
    $sql = "DROP TABLE `email_queue`";
    Database::query($sql);
}
private function deQueue(){
    $sql = "DELETE FROM `email_queue` WHERE `uuid`='{$this->recipient->uuid}' AND `mailshot_id`='{$this->shot->mailshot_id}'";
    Database::query($sql);
    $this->incrementShotCount();
}

//ECHO INFO FOR
public function echoInfo(){
    echo nl2br("\nTo: ".$this->recipient->email);
    echo nl2br("\nUUID: ".$this->recipient->uuid);
    // echo nl2br("\nFrom name: ".self::$fromname);
    // echo nl2br("\nFrom address: ".self::$fromaddress);
    // echo nl2br("\nSubject: ".$this->subject);
    // // echo nl2br("\nBody text: ".$this->bodytext);
    // echo nl2br("\nhtml headers: ".$this->htmlheaders);
    // echo nl2br($this->bodyhtml);
}

public static function sendBottles($i,$to){
    while($i>0){
        $e = new Mail;
        $e->to_address = $to;
        self::$fromname = "The $i Bottle Shop";
        self::$fromaddress = "bottle$i@bottles.com";
        $e->subject = $i." green bottles, standing on the wall.";
        $e->bodytext = $subject;
        $e->bodyhtml = $subject;
        $e->send();
        $i--;
    }
}

public function replaceTags(){
    $this->html = str_replace("##greeting##",$this->greeting,$this->html);
    $this->html = str_replace("##unsubscribe##",$this->unsubscribeUrl,$this->html);
}

public function setTemplates(){
    $this->bodytext = file_get_contents("templates/". $this->shot->mailshot_id . ".txt",TRUE);
    $this->bodyhtml = file_get_contents("templates/". $this->shot->mailshot_id . ".htm",TRUE);
}

public static function sendQueue($c){
    self::setOptions();

    // var_dump($options[0]);

    $sql = "SELECT * FROM `email_queue` WHERE `send_date`<NOW() LIMIT $c;";
    if(!$r = Database::query($sql)){
        die("nothing to send");
    }
    while ($m = $r->fetch_object()){
        $e = new Mail;
        $e->shot = self::getMailshotWithName($m->mailshot_id);
        $e->sender = self::getSenderWithId($e->shot->sender);
        // var_dump($e->sender);

        $e->list = self::getListWithId($e->shot->list);
        $e->recipient = self::getPersonWithUUID($m->uuid,$e->list);
        $e->legacyDecode();
        $e->setGreeting();
        $e->setUnsubscribeUrl();
        $e->setTemplates();
        $e->buildMessage();
        $e->replaceTags();


    if(self::$options['echo_to_browser'] == '1'){
        $e->echoInfo();
        $e->deQueue();
    }
    if(self::$options['is_live'] == '1'){
        if($e->realSend() === True){
            $e->deQueue();
        }
    }
    if(self::$options['send_to_admin'] == '1'){
        $e->recipient->email = self::$options['admin_email'];
        // var_dump($e->recipient);
        if($e->realSend() === True){
            $e->deQueue();
        }
    }

    }
}

private static function log($text){
    if (self::$logfile === None){
        self::$logfile = fopen("data/logs/mailer.txt",'a') or die("Unable to open file!");
    }
    $now = new \DateTime();
    $logtext = $now->format('c') ."    ". $text."\n";
    fwrite(self::$logfile,$logtext);

}

public static function clearQueue(){
    self::dropQueueTable();
}

public static function getSenderWithId($id){
    $sql = "SELECT * FROM senders WHERE `id`='$id'";
    $r = Database::query($sql);
    $sender = $r->fetch_object();
    $r->close();
    return $sender;
}

public static function getListWithId($id){
    $sql = "SELECT * FROM lists WHERE `id`='$id'";
    $r = Database::query($sql);
    $list = $r->fetch_object();
    $r->close();
    return $list;
}

public static function getMailshotWithName($id){
    $sql = "SELECT * FROM mailshots WHERE `mailshot_id`='$id'";
    // print_r($sql);
    $r = Database::query($sql);
    $shot = $r->fetch_object();
    $r->close();
    return $shot;
}

public static function getPersonWithUUID($uuid,$list){
    $sql = "SELECT * FROM $list->listname WHERE `uuid`='$uuid'";
    $r = Database::query($sql);
    // print_r($sql);
    $recipient = $r->fetch_object();
    $r->close();
    return $recipient;
}


public function setGreeting(){
    $this->greeting = "Hi,";
    if ($this->recipient->name != ""){
        $firstname = strchr($this->recipient->name," ",true);
        $firstname = ucfirst(strtolower($firstname));
        if ($firstname == FALSE){
            $this->greeting = "Hi {$this->recipient->name},";
        }else{
            $this->greeting = "Hi $firstname,";
        }
    }
}

public function setUnsubscribeUrl(){
    $this->unsubscribeUrl = self::unsubscribeUrl;
    $this->unsubscribeUrl = str_replace("##mailshot_id##",$this->shot->mailshot_id,$this->unsubscribeUrl);
    $this->unsubscribeUrl = str_replace("##UUID##",$this->recipient->uuid,$this->unsubscribeUrl);
}

private static function setOptions(){
    $sql = "SELECT * FROM `options`";
    $o = Database::query($sql);
    while ($option = $o->fetch_object()){
        self::$options[$option->meta_key] = $option->meta_value;
    }
}
public function legacyDecode(){
    $this->recipient->email = \v1\decode($this->recipient->email);
    $this->recipient->name = \v1\decode($this->recipient->name);
}

private function incrementShotCount(){
    $sql = "UPDATE `mailshots` SET `number_sent` = `number_sent` + 1 WHERE `mailshot_id` = '{$this->shot->mailshot_id}'";
    Database::query($sql);
}

public static function unsubscribe($person,$shot){
    $list = self::getListWithId($shot->list);
    $sender = self::getSenderWithId($shot->sender);
    $shortcode = $sender->shortcode . "_optout";
    $sql = "UPDATE $list->listname SET `$shortcode`='1' WHERE `uuid`='$person->uuid'";
    // print_r($sql);
    return Database::query($sql);
}
}
?>