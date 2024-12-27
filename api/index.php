<?php

  header("Content-type: application/json; charset=utf-8");
  header('Access-Control-Allow-Origin: *');

  session_start([
    'cookie_lifetime' => 1,
    'cookie_path' => '/',
    'cookie_secure' => true,
    'cookie_samesite' => 'none']);
  require_once("../includes/php/functions.php");

  if (isset($_POST['action'])){
    switch (trim(strtolower($_POST["action"]))) {
      case 'init':
        //////////////// Key set up ////////////////
          if (!isset($_SESSION["key"])){
            $key = kiis(); // Generate keys
            $add = initX($con, $table['users'],[// add public key to database
              "user" => base64_encode($key["public"]), //encrypted public key
              "time" => time(),
            ]);
            if ($add['code'] == 200){ //if keys are added successfully
              //////////////////////////// keys ////////////////////////////
                $myKey = [                                                //
                  "id"     => $add['message'],        //////////////////////
                  "secret" => $key['private'],        // KEYS //////////////
                  "public" => $key['public'],         //////////////////////
                ]; //so... we have our key                                //
              //////////////////////////// keys ////////////////////////////

              $die = [
                "code"    => 200,
                "status"  => "ok",
                "message" => $ink.base64_encode($myKey['public'])
              ];
              /**
               * at this point $_SESSION is not set yet
               * but, key is in database
               * and, keys has been collected in $myKey variable
               * hence, joining link is ready if host
               */
              $set = True;
              if (isset($_POST['guest'])){//if guest i.e if address is sent with post request
                $hostId = get_id($con, $_POST['sub']);
                if (($hostId == "null") | ($hostId == "error")){//if host id was not found
                  $die = [
                    "code"    => 404,
                    "status"  => "user not found",
                    "message" => "issue finding user"
                  ];
                  $set = False;
                } else {//if host id is valid
                  $hostAd  = base64_decode($_POST["sub"]);
                  $message = base64_encode(
                    pubkeyencrypt(
                      base64_encode(
                        $add["message"] //my Id
                      ),
                      $hostAd
                    )
                  );
                  $send = initX($con, $table['init'], [//send myId to host
                    "target"  => $hostId,
                    "message" => $message,
                    "time"    => time()
                  ]);
                  if ($send['code'] == 200){//if myId was sent successfully
                    $_SESSION["keys"] = json_encode([["address" => $hostAd, "id" => $hostId]]);
                    $die = [
                      "code"    => 200,
                      "status"  => "ok",
                      "message" => "awaiting ack..."
                    ];
                  } else{//myId was not sent successfully
                    $die = [
                      "code"    => 500,
                      "status"  => "Server error",
                      "message" => $send['message']
                    ];
                    $set = False;
                  }
                }
              }

              $set ? $_SESSION["key"] = json_encode($myKey) : "";
            } else { //if error occured adding keys
              $die = [
                "code"    => 501,
                "status"  => "internal error",
                "message" => "This is quiete embarassing. Kindly restart your browser and try again after a few minutes"
              ];
            }
          }
        //////////////// Key set up ////////////////

        //////////////// not Key set up ////////////////
          else {
            ////////////////////// Variables //////////////////////
              $key = json_decode($_SESSION["key"], true); //my keys              
            ////////////////////// Variables //////////////////////

            ////////////////////// SQL Statement //////////////////////
              $sql  = "SELECT a.id, b.user as target, a.message, a.time ";
              $sql .= " FROM {$table['init']} AS a ";
              $sql .= " LEFT JOIN {$table['users']} AS b ";
              $sql .= " ON a.target = b.id ";
              $sql .= " WHERE b.user = :ad";
              try {
                $stmt = $con->prepare($sql);
                $stmt->execute([":ad" => base64_encode($key["public"])]);
                $database = $stmt->fetchAll(PDO::FETCH_ASSOC);
                if($database){
                  $return = ["code" => 200, "message" => $database];
                } else {
                  $return = ["code" => 404, "message" => $database]; //empty
                }
              } catch (PDOException $e) {
                $return = ["code" => 501, "message" => $e->getMessage()];
              }
            ////////////////////// SQL Statement //////////////////////

            ////////////////////// Ma ga na //////////////////////
              if ($return["code"] == 200){//if there are messages for user in init
                foreach ($return["message"] as $value) {
                  $packet = base64_decode(pubkeydecrypt(base64_decode($value["message"]), $key["secret"]));
                  $delete = database_delete_data($con, $table['init'], "id", $value['id']);
                }

                if (isset($_POST["guest"])){ // Guest
                  if ($packet == "ack"){
                    $_SESSION['init']  = "auth";
                    $_SESSION['texts'] = json_encode([]);
                    $die = [
                      "code"    => 200,
                      "status"  => "ok",
                      "message" => "clear"
                    ];
                  }
                } else { //Admin
                  $guestAd = get_id($con, $packet, $table['users'], "user", "id");
                  $guestAd = base64_decode($guestAd);
                  $message = base64_encode(pubkeyencrypt(base64_encode("ack"), $guestAd));
                  do{
                    $send = database_insert_unique($con, $table['init'], [
                      "id" => rand(999, 9999),
                      "target" => $packet,
                      "message" => $message,
                      "time" => time()
                    ]);
                  } while ($send['code'] == 402);
          
                  if ($send["code"] == 200){
                    $die = [
                      "code"    => 200,
                      "status"  => "ok",
                      "message" => "clear"
                    ];
                    $_SESSION['init'] = "auth";
                    $_SESSION['texts'] = json_encode([]);
                    $_SESSION["keys"] = json_encode([["address" => $guestAd, "id" => $packet]]);
                  } else {
                    $die = [
                      "code"    => 507,
                      "status"  => "server error",
                      "message" => "ack failed"
                    ];
                  }
                }
              } else {//if no messages yet
                if (isset($_POST["guest"])){//Guest
                  $die = [
                    "code"    => 209,
                    "status"  => "awaiting Host",
                    "message" => "awaiting host ..."
                  ];
                } else {//Admin
                  $die = [
                    "code"    => 209,
                    "status"  => "awaiting guest",
                    "message" => $ink.base64_encode($key['public'])
                  ];
                }
              }
            ////////////////////// Ma ga na //////////////////////
          }
        //////////////// not Key set up ////////////////
        break;

      case 'hermes':
        //////////////// Variables ////////////////
          $key  = json_decode($_SESSION["key"], true);
          $text = json_decode($_SESSION['texts'], true);
        //////////////// Variables ////////////////
        //////////////// part ////////////////
          if (isset($_POST['send'])){//send
            //////////////// Variables ////////////////
              $target = json_decode($_SESSION["keys"], true)[0]; //contacts basically
            //////////////// Variables ////////////////
            //////////////// Magana ////////////////
              $message = base64_encode(pubkeyencrypt(base64_encode($_POST['text']), $target["address"]));
              $send    = initX($con, $table['messages'], [
                "id" => rand(999, 9999),
                "reciver" => $target['id'],
                "message" => $message,
                "time"    => $_POST['time']
              ]);

              if ($send['code'] == 200){
                $text[] = ["sender" => "sender", "message" => $_POST['text'], "time" => $_POST['time']];
                $_SESSION["texts"] = json_encode($text);
                $die = [
                  "code"    => 200,
                  "status"  => "ok",
                  "message" => "message sent"
                ];
                do {
                  $userUpdate = database_update_data($con, $table['users'], ["time" => time() - (60 * 10)], "id", $key['id']);
                } while ($userUpdate['code'] != 200);
              } else {
                $die = [
                  "code"    => 505,
                  "status"  => "ok",
                  "message" => "message not sent"
                ];
              }
            //////////////// Magana ////////////////
          }else{ //receive
            //////////////// Variables ////////////////
              //$identifier = base64_encode($key['public']);
              $end        = [];
            //////////////// Variables ////////////////
            //////////////// SQL Query ////////////////

              /*$sql  = "SELECT a.id, a.message, a.time ";
              $sql .= " FROM {$table['messages']} ";/*AS a ";
              $sql .= " LEFT JOIN {$table['users']} AS b ";
              $sql .= " ON a.reciver = b.id ";* /
              $sql .= " WHERE reciver = :rcver";
              //$sql .= " WHERE b.ad = :rcver";
              $sql .= " ORDER BY a.time ";
              
              try {
                $stmt = $con->prepare($sql);
                //$stmt->execute([":rcver" => $identifier]);
                $stmt->execute([":rcver" => $key['id']]);
                $database = $stmt->fetchAll(PDO::FETCH_ASSOC);
                if($database){
                  $return = ["code" => 200, "message" => $database];
                } else {
                  $return = ["code" => 404, "message" => $database]; //empty
                }
              } catch (PDOException $e) {
                $return = ["code" => 501, "message" => $e->getMessage()];
              }*/

            //////////////// SQL Query ////////////////
              
              $return = database_fetch_data(
                $con,
                $table['messages'],
                "reciver",
                $key['id'],
                "id, message, time",
                True
              );

            //////////////// SQL Query ////////////////
            //////////////// Magana ////////////////
              if($return['code'] == 200){
                foreach ($return["message"] as $value) {
                  $message = $value["message"];
                  $message = base64_decode(pubkeydecrypt(base64_decode($message), $key['secret'])); // write decode function
                  $text[]  = ["sender" => "receiver", "message" => $message, "time" => $value["time"]];
                  
                  $delete  = database_delete_data($con, $table['messages'], "id", $value["id"]);
                  
                  $end[]   = ["message" => $message, "time" => $value["time"]];

                }
                do {
                  $userUpdate = database_update_data($con, $table['users'], ["time" => time() - (60 * 10)], "id", $key['id']);
                } while ($userUpdate['code'] != 200);
                $_SESSION["texts"] = json_encode($text);
                $die = [
                  "code"    => 200,
                  "status"  => "ok",
                  "message" => $end
                ];
              } else {
                die(json_encode($return));
              }
            //////////////// Magana ////////////////
          }
        //////////////// part ////////////////
        break;
      
      default:
        $die = [
          "code"    => 403,
          "status"  => "invalid instruction",
          "message" => "are you lost?"
        ];
        break;
    }
  } else {
    $die = [
      "code"    => 405,
      "status"  => "unclear instruction",
      "message" => $_POST
    ];
  }

  echo(json_encode($die));
  /*///////////////////// footer //////////////////////
    kron($con, $table['messages'], "time", time() - (60 * 10));
    kron($con, $table['init'], "time", time() - (60 * 15));
    kron($con, $table['users'], "time", time() - (60 * 20));
  ////////////////////// footer /////////////////////*/
?>
