<?php
namespace InDemandDigital\IDDFramework;
use \Defuse\Crypto\Crypto;
use \Defuse\Crypto\Key;

class Encryptor{
    private static $key;
    const keypath = "../key/key.key";
    private static $encrypted_tables = [
        'Artist'=>
                            ['email',
                            'sort_code',
                            'account_number']
        ,
        'Reservation' =>
                            ['reservation_number',
                            'room_type',
                            'booking_name',
                            'check_in',
                            'check_out',
                            'notes']

    ];

public function makeNewKey(){
    $key = Key::createNewRandomKey();
    echo $key->saveToAsciiSafeString();
    // copy to text file and rename key.key - make sure no newlines
}

private function getKey(){
    $keystring = file_get_contents(self::keypath);
    self::$key = Key::loadFromAsciiSafeString($keystring);
}

//ENCODE FUNCTION
public function encode($data){
	try {
        if(!isset(self::$key)){
            self::getKey();
        }
        $data = Crypto::encrypt($data, self::$key);
    }
	catch (Exception $e) {
        die("Encrytion Failed: $e");
    }
	return base64_encode($data);
}


//DECODE FUNCTION
public function decode($data){
	if($data){
        $data = base64_decode($data);

        try {
            if(!isset(self::$key)){
                self::getKey();
            }
            $data = Crypto::decrypt($data,self::$key);
        }
    	catch (Ex\InvalidCiphertextException $ex){
            die('DANGER! DANGER! The ciphertext has been tampered with!');
        }
    	catch (Ex\CryptoTestFailedException $ex){
            die('Cannot safely perform decryption');
        }
    	catch (Ex\CannotPerformOperationException $ex){
            die('Cannot safely perform decryption');
        }
    	catch (Exception $e){
            $data = "********";
        }
    }else{
        $data = NULL;
    }
    return $data;
}

// check for type
// function decodeArray($array){
//     for($c=0;$c<count($array);$c++){
//         self::decodeObject($array[$c]);
//         }
//     return $array;
// }

// data type check doesnt work
function decodeObject($object){

    // var_dump($object);
    // foreach($object as $key => $value){
    //         if (substr($value, -1) == "="){
    //             $value = self::decode($value);
    //         }
    //         $object->$key = $value;
    //     }
        return $object;
}

function legacyDecodeObject($object){
    $entity_type = end(explode('\\', get_class($object)));
    if (!self::$encrypted_tables[$entity_type]){
        return $object;
    }
    foreach($object as $key => $value){

            if (in_array($key,self::$encrypted_tables[$entity_type])){
                $value = \v1\decode($value);
            }
            $object->$key = $value;
        }
    return $object;
}

}
?>
