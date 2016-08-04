<?php
namespace InDemandDigital\IDDFramework;
// use Defuse\Crypto\Crypto;
// use Defuse\Crypto\Key;
use \InDemandDigital\IDDFramework\Crypto;
use \InDemandDigital\IDDFramework\Crypto\Exception as Ex;

class Encryptor{
    private static $key;
    private static $keyV1;

    const keypath = "/keys/key.key";
    const keypathv1 = "/keys/x040.txt";
    const keypathv1_1 = "/keys/k1.txt";
    const keypathv1_2 = "/keys/k2.txt";

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

    // if(debug_backtrace()[1]['function'] == 'v1Decode'){
    //     $k1 = file_get_contents($_SERVER['DOCUMENT_ROOT'].self::keypathv1_1);
    //     $k2 = file_get_contents($_SERVER['DOCUMENT_ROOT'].self::keypathv1_2);
    //     if(!$k1 || !$k2){
    //         die("v1 Key not found");
    //     }else{
    //         self::$keyV1 = $k1 ^ $k2;
    //     }
    // }else{
    //     $keystring = file_get_contents($_SERVER['DOCUMENT_ROOT'].self::keypath);
    //     if(!$keystring){
    //         die("v2 Key not found");
    //     }
    //     self::$key = Key::loadFromAsciiSafeString($keystring);
    // }

        $k1 = file_get_contents($_SERVER['DOCUMENT_ROOT'].self::keypathv1_1);
        $k2 = file_get_contents($_SERVER['DOCUMENT_ROOT'].self::keypathv1_2);
        if(!$k1 || !$k2){
            die("v1 Key not found");
        }else{
            self::$keyV1 = $k1 ^ $k2;
        }
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
                $value = self::encode($value);
            }
            $object->$key = $value;
        }
    return $object;
}





private function v1Decode($data){
    if(!isset(self::$keyV1)){
        self::getKey();
    }

    	if($data){
            $data = base64_decode($data);

            try {$data = Crypto\Crypto::Decrypt($data,self::$keyV1);}
        	catch (Ex\InvalidCiphertextException $ex)
        		{trigger_error('DANGER! DANGER! The ciphertext has been tampered with!',E_USER_WARNING);}
        	catch (Ex\CryptoTestFailedException $ex)
        		{trigger_error('Cannot safely perform decryption',E_USER_WARNING);}
        	catch (Ex\CannotPerformOperationException $ex)
        		{trigger_error('Cannot safely perform decryption',E_USER_WARNING);}
        	catch (Exception $e)
        		{$data = "********";}
        }else{$data = NULL;}

        return $data;
    }

private function v2Decode($data){
    throw new \Exception("v2 Decryption Not Active");
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
    return $d;
}

private function encode($data){
        if(!isset(self::$keyV1)){
            self::getKey();
        }
        // $data = Crypto::encrypt($data, self::$key);
        try {
            $data = Crypto\Crypto::Encrypt($data, self::$keyV1);
        } catch (CryptoTestFailedException $ex) {
            die('Cannot safely perform encryption');
        } catch (CannotPerformOperationException $ex) {
            die('Cannot safely perform decryption');
        }


        return base64_encode($data);
}
}
?>
