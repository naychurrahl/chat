<?php

  session_start([
    'cookie_path' => '/',
    'cookie_secure' => true,
    'cookie_samesite' => 'none',
  ]);
  require_once("../includes/php/functions.php");

  $target = json_decode($_SESSION["keys"], true)[0];
  $key = json_decode($_SESSION["key"], true);
  $table = ["a" => "chat_messages", "b" => "chat_users"];
  $text = json_decode($_SESSION['texts'], true);


  if (isset($_GET['message'])){
    
    ///////////////////////////////////////////  Variable declaration  ///////////////////////////////////////////////////////////////////
      
      $end = [];
      $constriant = "reciver";
      $identifier = base64_encode($key['public']);
      
    ///////////////////////////////////////////  Variable declaration  ///////////////////////////////////////////////////////////////////
    
    /////////////////////////////////////////////////  SQL Query  ///////////////////////////////////////////////////////////////////
      $sql  = "SELECT a.id, a.message, a.time ";
      //$sql  = "SELECT b.ad as sender, c.ad as reciver, a.message, a.time ";
      $sql .= " FROM {$table['a']} AS a ";
      //$sql .= " LEFT JOIN {$table['b']} AS b ";
      //$sql .= " ON a.sender = b.id ";
      $sql .= " LEFT JOIN {$table['b']} AS c ";
      $sql .= " ON a.reciver = c.id ";
      $sql .= " WHERE c.ad = :rcver";
      //$sql .= " OR {$constriant1} = :{$constriant1}"; // to delete
      try {
        $stmt = $con->prepare($sql);
        $stmt->execute([":rcver" => $identifier]);
        //$stmt->execute([":{$constriant}" => $identifier, ":{$constriant1}" => $identifier]);
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
    /////////////////////////////////////////////////  SQL Query  ///////////////////////////////////////////////////////////////////
    
    ////////////////////////////////////////////////////  ETC  ///////////////////////////////////////////////////////////////////
      switch ($return["code"]) {
        case 200:
          foreach ($return["message"] as $value) {
            $message = $value["message"];
            $message = base64_decode(pubkeydecrypt(base64_decode($message), $key['private']));
            $time = $value["time"];
            $text[] = ["sender" => "receiver", "message" => $message, "time" => $time];
            
            /////////////// Delete sequence ///////////////////////
              
              $message_id = $value["id"];
              $delete = database_delete_data($con, $table['a'], "id", $message_id);
              $end[] = ["message" => $message, "time" => $time,];// "delete" => $delete['code']]; 

            /////////////// Delete sequence ///////////////////////
            
          }
          break;
        
        default:
          # code...
          break;
      }
      
      echo (json_encode(["code" => "200", "message" => $end]));
    ////////////////////////////////////////////////////  ETC  ///////////////////////////////////////////////////////////////////
  } elseif (isset($_POST['send'])){
    $target_id = get_id($con, base64_encode($target));
    $message = base64_encode(pubkeyencrypt(base64_encode($_POST['text']), $target));
    do {
      $send = database_insert_unique($con, $table['a'], ["id" => rand(999, 9999),"reciver" => $target_id, "message" => $message, "time" => $_POST['time']]);
    } while ($send['code'] !== 200);
    $text[] = ["sender" => "sender", "message" => $_POST['text'], "time" => $_POST['time']];
    echo json_encode($send['code']);
  }

  $_SESSION["texts"] = json_encode($text);

?>