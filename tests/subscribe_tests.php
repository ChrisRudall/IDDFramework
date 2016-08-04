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


// $vars = ['email' => 'steve@indemandmusic.com'];
// var_dump( IDD\Mail::addSubscriber($vars));
$d = 'hello';
// $e = Crypto::encrypt($d,$key);
// echo Crypto::decrypt($e,$key);

// require_once('../src/defuse_v1/php-encryption/autoload.php');
  try {
      $key = Crypto\Crypto::CreateNewRandomKey();
      // WARNING: Do NOT encode $key with bin2hex() or base64_encode(),
      // they may leak the key to the attacker through side channels.
  } catch (CryptoTestFailedException $ex) {
      die('Cannot safely create a key');
  } catch (CannotPerformOperationException $ex) {
      die('Cannot safely create a key');
  }

  $message = "ATTACK AT DAWN";
  try {
      $ciphertext = Crypto\Crypto::Encrypt($message, $key);
  } catch (CryptoTestFailedException $ex) {
      die('Cannot safely perform encryption');
  } catch (CannotPerformOperationException $ex) {
      die('Cannot safely perform decryption');
  }

  try {
      echo $decrypted = Crypto\Crypto::Decrypt($ciphertext, $key);
  } catch (InvalidCiphertextException $ex) { // VERY IMPORTANT
      // Either:
      //   1. The ciphertext was modified by the attacker,
      //   2. The key is wrong, or
      //   3. $ciphertext is not a valid ciphertext or was corrupted.
      // Assume the worst.
      die('DANGER! DANGER! The ciphertext has been tampered with!');
  } catch (CryptoTestFailedException $ex) {
      die('Cannot safely perform encryption');
  } catch (CannotPerformOperationException $ex) {
      die('Cannot safely perform decryption');
  }
 ?>
