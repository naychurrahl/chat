<?php

  require_once("dbcon.php");
  
  function database_delete_data($con, $table, $constriant, $identifier){
    $first_check = database_fetch_data($con, $table, $constriant, $identifier, $constriant);
    if($first_check['code'] === 200){
      $sql  = "DELETE FROM {$table} ";
      $sql .= " WHERE {$constriant} = :{$constriant}";

      try {
        $stmt = $con->prepare($sql);
        $stmt->execute([":{$constriant}" => $identifier]);
        $return = ["code" => 200, "message" => "ok"];
      } catch (PDOException $e){
        $return = ["code" => 501, "message" => $e->getMessage()];
      } finally{
        return $return;
      }
    }
    return ["code" => 404, "message" => $first_check['message']];
  }

  function database_fetch_data($con, $table, $constriant, $identifier, $columns = "*", $all = False){
    $sql  = "SELECT {$columns} FROM {$table} ";
    $sql .= " WHERE {$constriant} = :{$constriant}";
    try {
      $stmt = $con->prepare($sql);
      $stmt->execute([":{$constriant}" => $identifier]);
      $database = $all ? $stmt->fetchall(PDO::FETCH_ASSOC) : $stmt->fetch(PDO::FETCH_ASSOC);
      if($database){
        $return = ["code" => 200, "message" => $database];
      } else {
        $return = ["code" => 404, "message" => $database]; //empty
      }
    } catch (PDOException $e) {
      $return = ["code" => 501, "message" => $e->getMessage()];
    } finally{
      return $return;
    }
  }

  function database_fetch_table($con, $table, $columns = "*"){
    $sql = "SELECT {$columns} FROM {$table}";    
    try {
      $stmt = $con->prepare($sql);
      $stmt->execute();
      $database = $stmt->fetchAll(PDO::FETCH_ASSOC);
      if($database){
        $return = ["code" => 200, "message" => $database];
      } else {
        $return = ["code" => 404, "message" => $database]; //empty
      }
    } catch (PDOException $e) {
      $return = ["code" => 501, "message" => $e->getMessage()];
    } finally{
      return $return;
    }
  }

  function database_insert($con, $table, $data){
    if (is_assoc($data) && count($data) > 0){
      $columns = implode(", ", array_keys($data));
      $values  = [];
      foreach ($data as $key => $value){
        $values[] = ":{$key}";
        $Values[':'.$key] = $value;
      }
      $values = implode(", ", $values);
    } elseif (count($data) == 0){
      return [
        "code" => 400,
        "message" => "Empty array provided"
      ];
    } else{
      return [
        "code" => 400,
        "message" => "Invalid data type: non array provided"
      ];
    }
    $sql  = "INSERT INTO {$table} ($columns) ";
    $sql .= " VALUES ({$values})";

    try{
      $stmt = $con->prepare($sql);
      $stmt->execute($Values);
      $return = ["code" => 200, "message" => $con->lastInsertId()];
    } catch (PDOException $e){
      $return = ["code" => 501, "message" => $e->getMessage()];
    } finally {
      return $return;
    }
  }

  function database_insert_unique($con, $table, $data){
    $constriant = array_key_first($data);
    $first_check = database_fetch_data($con, $table, $constriant, $data[$constriant], "id");
    switch ($first_check ['code']) {
      case 404:
        return database_insert($con, $table, $data);
      case 200:
        return ["code" => "402", "message" => $first_check['message']['id']];      
      default:
        return ["code" => "501", "message" => $first_check['message']];
    }
  }

  function database_update_data($con, $table, $data, $constriant, $identifier){
    if (is_assoc($data) && count($data) > 0){
      $values  = [];
      foreach ($data as $key => $value){
        $values[] = "{$key} = :{$key}";
        $Values[':'.$key] = $value;
      }
      $values = implode(", ", $values);
    } else{
      return ["code" => 400, "message" => "Empty array or invalid data type provided"];
    }

    $first_check = database_fetch_data($con, $table, $constriant, $identifier, $constriant);
    if($first_check['code'] === 200){  
      $sql  = "UPDATE {$table} ";
      $sql .= " SET {$values} ";
      $sql .= " WHERE {$constriant} = :{$constriant}";
      $Values[':'.$constriant] = $identifier;
      
      try {
        $stmt = $con->prepare($sql);
        $stmt->execute($Values);
        $return = ["code" => 200, "message" => "ok"];
      } catch (PDOException $e){
        $return = ["code" => 501, "message" => $e->getMessage()];
      } finally{
        return $return;
      }
    }
    return ["code" => 501, "message" => "update error -> ".$first_check['message']];
  }

  function get_id($con, $tag, $table = "chat_users", $column = "id", $constriant = "user"){
    $tag_id = database_fetch_data($con, $table, $constriant, $tag, $column);
    if ($tag_id['code'] == 200){
      $return = ["code" => 200, "message" => $tag_id['message'][$column]];
    } elseif ($tag_id['code'] == 404){
      $return = ["code" => 404, "message" => null];
    } else {
      $return = ["code" => 400, "message" => "Unknown error"];
    }
    return $return;
  }

  function getMessage($con, $identifier, $all = False){
    return database_fetch_data(
      $con,
      "chat_test",
      "target",
      $identifier,
      "id, message",
      $all,
    );
  }
  
  function handShake($con, $step, $guest, $myKey, $return){
    $die = [
      "code"    => 321,
      "status"  => "ok",
      "message" => $return['message']['message'],
    ];
    switch ($step) {
      case '4'://Host step two -> sends public key
        $guest   = keyDecrypt($guest); // Guest's address
        $message = messageEnkrypt($myKey['public']); //Host's public key
        $message = "6".$message;
        $sendMsg = sendMessage($con, $message, rand(999, 9999), $guest);
        if ($sendMsg['code'] == 200){
          $die['status']  = "sent public key to guest";
          $die['message'] = $guest;
          database_delete_data($con, "chat_test", "id", $return['message']['id']);
          $_SESSION['init']['address'] = $guest;
          $_SESSION['t']  = "H2";
        } else {
          $die['code']    = 444;
          $die['status']  = "not ok";
          $die['message'] = $sendMsg['message'];
          $_SESSION['t']  = "2H";
        }
        break;
      case '6'://Guest step two
        $guest = messageDekrypt($guest); //Host's public key
        $message = messageEncrypt($myKey['public'], $guest); //Guest's public key {}
        $message = "5".$message;
        $sendMsg = sendMessage($con, $message, _rand(), $_SESSION['init']['address']);
        if ($sendMsg['code'] == 200){
          $die['status'] = "Sent public key to Host";
          $_SESSION['init']['key'] = $guest;
          database_delete_data($con, "chat_test", "id", $return['message']['id']);
          $_SESSION['t'] = "G2";
        } else {
          $die['code'] = 406;
          $die['status'] = "not ok";
          $die['message'] = $sendMsg['message'];
          $_SESSION['t'] = "2G";
        }
        break;
      case '5'://Host
        $guest = @messageDecrypt($guest, $myKey['private']); // Guest's public key
        $message = messageEncrypt("ack", $myKey['private'], False);
        $message = "7".$message;
        $sendMsg = sendMessage($con, $message, rand(999, 9999), $_SESSION['init']['address']);
        if ($sendMsg['code'] == 200){
          $die['code'] = 200;
          $die['status'] = "Sent ack to Guest";
          $die['message'] = pubAddres($guest);
          $_SESSION['init']['key'] = $guest;
          database_delete_data($con, "chat_test", "id", $return['message']['id']);
          $_SESSION['texts'] = messageEncrypt([], $myKey['public']);
          $_SESSION['t'] = "H3";
        } else {
          $die['code'] = 405;
          $die['status'] = $guest;
          $die['message'] = $sendMsg['message'];
          $_SESSION['t'] = "3H";
        }
        break;

      case '7'://Guest
        $die['code'] = 407;
        $die['status'] = "not ok";
        $_SESSION['t'] = "3G";
        $guest = messageDecrypt($guest, $_SESSION['init']['key'], False); // ack
        if ($guest == "ack"){
          $sendMsg = database_delete_data($con, "chat_test", "id", $return['message']['id']);
          if ($sendMsg['code'] == 200){
            $die['code'] = 200;
            $die['status'] = "Sent ack to Host";
            $die['message'] = $guest;
            database_delete_data($con, "chat_test", "id", $return['message']['id']);
            $_SESSION['texts'] = messageEncrypt([], $myKey['public']);
            $_SESSION['t'] = "G3";
          } else{
            $die['message'] = $sendMsg['message'];
          }
        }else{
          $die['message'] = $guest;
        }
        break;

      default:
        $die['code'] = 205;
        $die['status'] = $_SESSION['t'];
        $die['message'] = $_SESSION['t'][0] == "G" ? $myKey['address'] : $ink.$myKey['address'];
        break;
    }
    return $die;
  }

  function init ($con){
    $key = kiis(); // Generate keys
    $add = initX($con, "chat_users",[// add public key to database
      "user" => keyEncrypt($key["public"]), //encrypted public key
      "time" => time(),
    ]);
    if ($add['code'] == 200){ //if keys are added successfully
      //////////////////////////// keys ////////////////////////////
        $myKey = [                                                //
          "id"     => $add['message'],        //////////////////////
          "secret" => $key['private'],        // KEYS //////////////
          "public" => $key['public'],         //////////////////////
        ]; //so... we have our keys                               //
      //////////////////////////// keys ////////////////////////////

      return [
        "code"    => 200,
        "status"  => "init ok",
        "message" => keyEncrypt($myKey)
      ];
    } else { //if error occured adding keys
      return [
        "code"    => 501,
        "status"  => "internal error",
        "message" => "This is quiete embarassing. Kindly restart your browser and try again after a few minutes"
      ];
    }
  }

  function initX($con, $table, $data, $range = [999, 9999]){
    $data['id'] = rand($range[0], $range[1]);
    $counter = 0;
    do {
      $add = database_insert_unique($con, $table, $data);
      $counter++;
    } while (($add['code'] !== 200) & ($counter < 10));

    switch ($add['code']) {
      case 200:
        return ["code" => 200, "message" => $data['id']];
      
      default:
        return ["code" => 400, "message" => "k{$counter}: {$add['message']}"];
    }
  }

  function is_assoc ($array){
    if(is_array($array)){
      $keys = array_keys($array);
      if ($keys !== range(0, count($array) - 1)){
        return True;
      } else {
        return false;
      }
    } else {
      return False;
    }
  }

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

  function kron($con, $table, $constriant, $identifier){
    $sql  = "DELETE FROM {$table} ";
    $sql .= " WHERE {$constriant} < :{$constriant}";

    try {
      $stmt = $con->prepare($sql);
      $stmt->execute([":{$constriant}" => $identifier]);
      $return = ["code" => 200, "message" => "ok"];
    } catch (PDOException $e){
      $return = ["code" => 501, "message" => $e->getMessage()];
    } finally{
      return $return;
    }
  }

  function messageDecrypt($hash, $key, $public = True){
    $data = rev($hash);
    $data = base64_decode($data);
    $data = gzdecode($data);
    //echo $data;
    $data = $public ? pubkeydecrypt($data, $key)['message'] : prvkeydecrypt($data, $key)['message'];
    $data = base64_decode($data);
    $data = json_decode($data, true);
    return $data;
  }

  function messageDekrypt($data){
    return json_decode(
      base64_decode(
        gzdecode(
          base64_decode(
            rev($data)
          )
        )
      )
    );
  }

  function messageEncrypt($data, $key, $public = True){
    $hash = json_encode($data);
    $hash = base64_encode($hash);
    $hash = $public ? pubkeyencrypt($hash, $key) : prvkeyencrypt($hash, $key);
    //return $hash['code'];
    if ($hash['code'] == 200){
      $hash = gzencode($hash['message']);
      $hash = base64_encode($hash);
      $hash = rev($hash);
      return $hash;
    } 
    return $hash['code'];
  }

  function messageEnkrypt($data){
    return rev(
      base64_encode(
        gzencode(
          base64_encode(
            json_encode($data)
          )
        )
      )
    );
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
    //print_r($split);
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

  function _rand(){
    return rand(999, 9999);
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

  function sendMessage($con, $message, $myId, $target){
    $send = database_insert_unique($con, "chat_test", [
      "id"      => $myId,
      "message" => $message,
      "target"  => $target,
      "time"    => time(),
    ]);
    //return($send);
    switch ($send['code']) {
      case 402:
        return sendMessage($con, $message, rand(999, 9999), $target);
      case 200:
        return ["code" => 200, "message" => $myId];
      default:
        return ["code" => 400, "message" => $send['message']];
    }
  }

  function text($myKey, $message, $time){
    $text = messageDecrypt($_SESSION['texts'], $myKey['private']);
    $text[] = ["sender" => "sender", "message" => $message, "time" => $time];
    $_SESSION['texts'] = messageEncrypt($text, $myKey['public']);
  }

  function textNode($message, $time, $sender){
    echo "
      <div class=\"message-content {$sender}\">
        <label for=\"msg-block\">{$time}</label>
        <div class=\"msg-block\">
          <p>
            {$message}
          </p>
        </div>
      </div>
    ";
  }

?>
