<?php

session_start(['cookie_path' => '/', 'cookie_secure' => true, 'cookie_samesite' => 'none']);

  function keyEncrypt($key){
    $key =  json_encode($key);
    return base64_encode($key);
  }

  function keyDecrypt($key){
    $key = base64_decode($key);
    return json_decode($key, true);
  }

  function kiis($bits = 2048){
    $privateKey = openssl_pkey_new(array(
      "private_key_bits" => $bits,
      "private_key_type" => OPENSSL_KEYTYPE_RSA,
    ));
    openssl_pkey_export($privateKey, $privateKeyStr);
    $publicKey = openssl_pkey_get_details($privateKey)["key"];
    $mPubKeyEnc = keyEncrypt($publicKey);
    $mPubKeyEnc = rev(time().$mPubKeyEnc);
    $mPubKeyEnc = pubAddres($mPubKeyEnc);
    $mykey = [
      "private" => $privateKeyStr,
      "public"  => $publicKey,
      "address" => $mPubKeyEnc,
    ];
    return $mykey;
  }

  function messageDecrypt($hash, $key, $public = True){
    $data = rev($hash);
    $data = base64_decode($data);
    $data = gzdecode($data);
    return $data;
    $data = $public ? pubkeydecrypt($data, $key)['code'] : prvkeydecrypt($data, $key)['code'];
    $data = base64_decode($data);
    $data = json_decode($data, true);
    return $data;
  }

  function messageEncrypt($data, $key, $public = True){
    $hash = json_encode($data);
    $hash = base64_encode($hash);
    $hash = $public ? pubkeyencrypt($hash, $key)['message'] : prvkeyencrypt($hash, $key)['message'];
    $hash = gzencode($hash);
    $hash = base64_encode($hash);
    $hash = rev($hash);
    return $hash;
  }

  function prvkeydecrypt($data, $key){//requires public key
    $Data = "";
    $split = json_decode($data, true);
    try {
      foreach ($split as $part) {
        if (@openssl_public_decrypt(base64_decode($part), $partialData, $key)){
          $Data .= $partialData;
        } else {
          throw new Exception('Unable to decrypt data!');
        }
      }
      return ["code" => 200, "message" => json_decode($Data, true)];
    } catch (Exception $th) {
      return ["code" => 400, "message" => $th];
    }
  }

  function prvkeyencrypt($data, $key){
    $Data = [];
    $data = json_encode($data);
    $split = str_split($data, 117);
    try {
      foreach ($split as $part) {
        if (@openssl_private_encrypt($part, $partialData, $key)){
          $Data[] = base64_encode($partialData);
        } else {
          throw new Exception('Unable to encrypt data!');
        }
      }
      return ["code" => 200, "message" => json_encode($Data)];
    } catch (Exception $th) {
      return ["code" => 400, "message" => $th];
    }
    
  }

  function pubAddres($k = ""){
    $return = json_encode($k);
    $return = hash(hash_algos()[2], $return);
    $return = hash(hash_algos()[5], $return);
    return hash(hash_algos()[5], $return);
  }

  function pubkeydecrypt($data, $key){//requires private key
    $Data = "";
    $split = json_decode($data, true);
    try {
      foreach ($split as $part) {
        if (@openssl_private_decrypt(base64_decode($part), $partialData, $key)){
          $Data .= $partialData;
        } else {
          throw new Exception('Unable to encrypt data!');
        }
      }
      return ["code" => 200, "message" => $Data];
    } catch (Exception $th) {
      return ["code" => 400, "message" => $th];
    }
  }

  function pubkeyencrypt($data, $key){
    $Data = [];
    $data = json_encode($data);
    $split = str_split($data, 50);
    try {
      foreach ($split as $part) {
        if (@openssl_public_encrypt($part, $partialData, $key)){
          $Data[] = base64_encode($partialData);
        } else {
          throw new Exception('Unable to encrypt data!');
        }
      }
      return ["code" => 200, "message" => json_encode($Data)];
    } catch (Exception $th) {
      return ["code" => 400, "message" => $th];
    }
  }

  function rev($q){
    $r = $q;
    $l = strlen($r);
    for ($i = ($l % 2); $i < $l; $i += 2){
      $t = $r[$i];
      $r[$i] = $r[$i + 1];
      $r[$i + 1] = $t;
    }
    return $r;
  }

  $k = kiis();
  $l = kiis();

  $myKey = keyDecrypt($_SESSION['key']);

  $q = messageEncrypt("Girafee?", $_SESSION['init']['key']);//['message'];

  $y = '4HIsAAAAAAAAgCBX7yCpAQAAH4pd829L4SWsUmiELxMiy4czyRGlPh0P\/5\/9pdQWclU+UwjcOra5wCtFdDMbW9LFrG2lA4dzokSKj8crLSRamOUmWeRxST5\/YVNWSH9rMXzRoZoD92NLzLu+L8tbLP8FaftVuXWTqd33I+VmF8BZl85IWMoWCFYoVEu3iDnuVFyQRDB\/xWAiQchLQsG5F7I9vymi+ta+DUfA4ojBmVLtBcE0kINcO1SGxLX8tFrQPrkMtuTgSePg8B5S6nn79x4xnFkyW9RAzomLrMXEWiNfWLTpthfUuwFoX8XIXnfxGGi\/ZVgscfG8RHWBWRys6mAF2EHEN\/kqUCOjV2+E4yuTyrRj5EbtlMbdm3pp6gLAjI+kMCPhnHr82aXg6U3KX5aTf\/D4nBPuWXBAAA=A';

  //print_r($q);
  
  echo $q."</br>";
  echo $y."</br>";

  echo messageDecrypt($q, $myKey['private']);
  //echo hash(hash_algos()[2], $_SESSION['init']['key'])."</br>";
  //echo messageDecrypt($y, $_SESSION['init']['key']);


?>