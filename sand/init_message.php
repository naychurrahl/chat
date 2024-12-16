<?php

  header("Content-type: application/json; charset=utf-8");
  header('Access-Control-Allow-Origin: *');

  session_start(['cookie_path' => '/', 'cookie_secure' => true, 'cookie_samesite' => 'none']);
  require_once("../includes/php/functions.php");

  ///////////////////////// Variable declaration ///////////////////////////////////
    
    $key = json_decode($_SESSION["key"], true);    
    $table = ["a" => "chat_init", "b" => "chat_users"];
    $constriant = "target";
    $identifier = base64_encode($key["public"]);
    $role = $_SESSION["role"];

  ///////////////////////// Variable declaration ///////////////////////////////////

  ////////////////////// SQL Statement /////////////////////////////////////////////////////////////////
    $sql  = "SELECT a.id, b.ad as target, a.message, a.time ";
    $sql .= " FROM {$table['a']} AS a ";
    $sql .= " LEFT JOIN {$table['b']} AS b ";
    $sql .= " ON a.target = b.id ";
    $sql .= " WHERE b.ad = :ad";
    try {
      $stmt = $con->prepare($sql);
      $stmt->execute([":ad" => $identifier]);
      $database = $stmt->fetchAll(PDO::FETCH_ASSOC);
      if($database){
        $return = ["code" => 200, "message" => $database];
      } else {
        $return = ["code" => 404, "message" => $database]; //empty
      }
    } catch (PDOException $e) {
      $return = ["code" => 501, "message" => $e->getMessage()];
    } finally{
      $return;
    }
  ////////////////////// SQL Statement /////////////////////////////////////////////////////////////////

  if ($return["code"] == 200){
    foreach ($return["message"] as $value) {
      $target = base64_decode(pubkeydecrypt(base64_decode($value["message"]), $key["private"]));
      $delete = database_delete_data($con, $table['a'], "id", $value['id']);
    }
    switch ($role) {
      case 'admin':
        $otherAd = get_id($con, $target, $table['b'], "ad", "id");
        $otherAd = base64_decode($otherAd);
        $_SESSION["keys"] = json_encode([$otherAd]);
        $message = base64_encode(pubkeyencrypt(base64_encode("ack"), $otherAd));
        do{
          $send = database_insert_unique($con, $table['a'], ["id" => rand(999, 9999), "target" => $target, "message" => $message, "time" => time()]);
        } while ($send['code'] == 402);
        if ($send["code"] == 200){
          $echo = json_encode(["init" => "ok"]);
          $_SESSION['init'] = "auth";
          $_SESSION['texts'] = json_encode([]);
        } else {
          $echo = json_encode(["init" => $send['code']]);
        }
        break;
      
      case 'join':
        if ($target == "ack"){
          $echo = json_encode(["init" => "ok"]);
          $_SESSION['init'] = "auth";
          $_SESSION['texts'] = json_encode([]);
        }
        break;

      default:
        $echo = json_encode(["init" => "default"]);
        break;
    }
  } else {
    $echo = json_encode(["init" => $return['code']]);
  }

  echo $echo;

?>
