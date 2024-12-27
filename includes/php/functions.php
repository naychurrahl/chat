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
      return $tag_id['message'][$column];
    } elseif ($tag_id['code'] == 404){
      return "null";
    } else {
      return "error";
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

  function kiis($bits = 2048){
    $privateKey = openssl_pkey_new(array(
      "private_key_bits" => $bits,
      "private_key_type" => OPENSSL_KEYTYPE_RSA,
    ));
    openssl_pkey_export($privateKey, $privateKeyStr);
    $publicKey = openssl_pkey_get_details($privateKey)["key"];
    return [
      "private" => $privateKeyStr,
      "public"  => $publicKey
    ];
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

  function messageDecrypt($data, $sender, $key){
    return prvkeydecrypt(pubkeydecrypt($data, $sender), $key);
  }

  function messageEncrypt($data, $receiver, $key){
    return prvkeyencrypt(pubkeyencrypt($data, $receiver), $key);
  }
  
  function prvkeydecrypt($data, $key){
    openssl_public_decrypt($data, $Data, $key);
    return $Data;
  }

  function prvkeyencrypt($data, $key){
    openssl_private_encrypt($data, $Data, $key);
    return $Data;
  }

  function pubkeydecrypt($data, $key){
    openssl_private_decrypt($data, $Data, $key);
    return $Data;
  }

  function pubkeyencrypt($data, $key){
    openssl_public_encrypt($data, $Data, $key);
    return $Data;
  }

  function textNode($message, $time, $sender){
    echo "
      <div class=\"message-content {$sender}\">
        <label for=\"\">{$time}</label>
        <div class=\"msg-block\">
          <p>
            {$message}
          </p>
        </div>
      </div>
    ";
  }

?>