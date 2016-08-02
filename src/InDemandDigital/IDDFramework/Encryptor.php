<?php
namespace InDemandDigital\IDDFramework;
use Defuse\Crypto\Crypto;
use Defuse\Crypto\Key;

class Encryptor{
    private static $key;
    const keypath = "/Library/WebServer/Documents/IDDFramework/key.key";
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
                            ,
        'Person' =>
                            ['name','firstname','lastname','email','dob','address','postcode','mobile','facebook']

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
    $e_data['data'] = self::v2Encode($data);
    $e_data['encoding_version'] = 2;
    return $e_data;
}


//DECODE FUNCTION
public function decode($data){

try{
        $data = self::v2Decode($data);
    }catch(\Exception $e){
        try{
            $data = self::v1Decode($data);
        }catch(\Exception $e){
            trigger_error("Could not decode with v1 or v2", E_USER_WARNING);
            return False;
        }
    }
    return $data;
}



public function decodeObject($object){
    $entity_type = end(explode('\\', get_class($object)));
    if (!self::$encrypted_tables[$entity_type]){
        return $object;
    }
    foreach($object as $key => $value){
            if (in_array($key,self::$encrypted_tables[$entity_type])){
                $d = self::decode($value);
                if($d === False){
                    throw new \Exception("Decryption failed", 1);
                }else{
                    $object->$key = self::decode($value);
                }
            }
        }
    return $object;
}

public function encodeObject($object){
    $entity_type = end(explode('\\', get_class($object)));
    if (!self::$encrypted_tables[$entity_type]){
        return $object;
    }
    foreach($object as $key => $value){
            if (in_array($key,self::$encrypted_tables[$entity_type])){
                $value = self::v2Encode($value);
            }
            $object->$key = $value;
        }
    return $object;
}





private function v1Decode($data){
	$key = file_get_contents('/Library/WebServer/Documents/IDDFramework/x040.txt');

    	if($data){
            $data = base64_decode($data);

            try {$data = Crypto::legacyDecrypt($data,$key);}
        	catch (Ex\InvalidCiphertextException $ex)
        		{die('DANGER! DANGER! The ciphertext has been tampered with!');}
        	catch (Ex\CryptoTestFailedException $ex)
        		{die('Cannot safely perform decryption');}
        	catch (Ex\CannotPerformOperationException $ex)
        		{die('Cannot safely perform decryption');}
        	catch (Exception $e)
        		{$data = "********";}
        }else{$data = NULL;}

        return $data;
    }

private function v2Decode($data){
        $data = base64_decode($data);
        if(!isset(self::$key)){
            self::getKey();
        }

        try{
            $d = Crypto::decrypt($data,self::$key);
        }
        catch(\Defuse\Crypto\Exception\WrongKeyOrModifiedCiphertextException $e){
            throw new \Exception("v2 Decryption Not Succesful");
        }
    return $data;
}

private function v2Encode($data){
        if(!isset(self::$key)){
            self::getKey();
        }
        $data = Crypto::encrypt($data, self::$key);
        return base64_encode($data);
}
}
?>
