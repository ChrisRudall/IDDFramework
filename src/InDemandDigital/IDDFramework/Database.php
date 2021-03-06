<?php
namespace InDemandDigital\IDDFramework;
use InDemandDigital\IDDFramework\Tests\Debug as Debug;
use mysqli;

session_start();
//t
class Database{

public static $server;
public static $user;
public static $pass;
public static $name;
private static $conn; // the connection

public static function forceLocal(){
    $_SESSION['local'] = True;
}
public static function forceRemote(){
    $_SESSION['local'] = False;
}
private static function checkForLocal(){
    session_start();
    if($_SERVER['REMOTE_ADDR'] == "::1" || $_SERVER['REMOTE_ADDR'] == "127.0.0.1" || $_SERVER['HOME'] == "/Users/chrisrudall"){
        $_SESSION['local'] = True;
    }else{
        $_SESSION['local'] = False;
    }
}

public static function connect($server = null,$user = null,$pass = null,$name = null){
    self::checkForLocal();
    self::setDatabaseCredentials($server,$user,$pass,$name);
    self::realConnect();
}

public static function connectToMailingList(){
    self::checkForLocal();
    self::setDatabaseCredentialsMailingList();
    self::realConnect();
}

public static function connectToSocialManager(){
    self::checkForLocal();
    self::setDatabaseCredentialsSocialManager();
    self::realConnect();
}

private static function realConnect(){
    self::$conn = new mysqli(self::$server, self::$user, self::$pass, self::$name);
    return self::checkConnection();
}


public function closeConnection(){
    self::$conn->close();
    return;
}

/* set autocommit to off */
public function autocommit($v){
    self::$conn->autocommit($v);
    return;
}
/* commit */
public function commit(){
    self::$conn->commit();
    return;
}

private function setDatabaseCredentials($server,$user,$pass,$name){

    if ($_SESSION['local'] === True){
        self::$server = '127.0.0.1';
        self::$user = 'root';
        self::$pass = 'A101$pwmnnaDe';
        self::$name = 'portal16-local';
    }else{
        self::$server = 'db594839121.db.1and1.com';
        self::$user = 'dbo594839121';
        self::$pass = '5fF-S4r-C86-HzB';
        self::$name = 'db594839121';
    }
    //OVERRIDE
    if ($server && $user && $pass && $name){
            self::$server = $server;
            self::$user = $user;
            self::$pass = $pass;
            self::$name = $name;
    }

    return;
}

private function setDatabaseCredentialsMailingList(){

    if ($_SESSION['local'] === True){
        self::$server = '127.0.0.1';
        self::$user = 'root';
        self::$pass = 'A101$pwmnnaDe';
        self::$name = 'mailinglist';
    }else{
        self::$server = 'db617760955.db.1and1.com';
        self::$user = 'dbo617760955';
        self::$pass = 'G4M-ZJH-LKh-yMW';
        self::$name = 'db617760955';
    }
    return;
}

private function setDatabaseCredentialsSocialManager(){

    if ($_SESSION['local'] === True){
        self::$server = '127.0.0.1';
        self::$user = 'root';
        self::$pass = 'A101$pwmnnaDe';
        self::$name = 'idd';
    }else{
        self::$server = 'db568597162.db.1and1.com';
        self::$user = 'dbo568597162';
        self::$pass = 'hAK-3T4-8zT-vty';
        self::$name = 'db568597162';
    }
    return;
}

public function echoCredentials(){
    echo self::$server."<br>";
    echo self::$user."<br>";
    echo self::$pass."<br>";
    echo self::$name;
    return;
}

private function checkConnection(){
    /* check connection */
    if (self::$conn->connect_error) {
          trigger_error('Database connection failed: '  . self::$conn->connect_error, E_USER_ERROR);
          return False;
    }
    else{
        self::setDatabaseTimezone();
        return self::$conn;
    }
}

public function query($sql){
    // Debug::nicePrint("SQL CALL: ".$sql);
    try{
        $rs = self::$conn->query($sql);
    }catch(Exception $e){
        die("Did you forget to connect to the database? ".$e->getMessage());
    }
    return $rs;
}
public function getInsertedID(){
    return self::$conn->insert_id;
}


//FUNCTION TO ESCAPE AND CHECK DATA ENTRY
private function escape_apostrophes($string){
  $p =0;
  do {
    $p = strpos($string,"'",$p);
    if ($p !== FALSE){
    $string = substr_replace($string, "\'", $p,1);
    $p = $p + 2;
  }
  }
  while ($p !== FALSE);
  return $string;
}
//FUNCTION TO ESCAPE AND CHECK DATA ENTRY
public function test_input($data) {
  $data = trim($data);
  $data = stripslashes($data);
  $data = htmlspecialchars($data);
  $data = self::escape_apostrophes($data);
  return $data;
}

private static function setDatabaseTimezone(){
    $sql = "SET time_zone = '+02:00'";
    self::query($sql);
}
}
?>
