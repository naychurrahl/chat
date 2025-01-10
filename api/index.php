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
        $key = kiis(); // Generate keys
        $add = initX($con, $table['users'],[// add public key to database
          "user" => keyEncrypt($key["public"]), //encrypted public key
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
            "status"  => "init ok",
            "message" => keyEncrypt($myKey['public'])
          ];
          /**
           * at this point $_SESSION is not set yet
           * but, key is in database
           * and, keys has been collected in $myKey variable
           * hence, joining link is ready if host
           */

          $_SESSION["key"] = keyEncrypt(json_encode($myKey));
        } else { //if error occured adding keys
          $die = [
            "code"    => 501,
            "status"  => "internal error",
            "message" => "This is quiete embarassing. Kindly restart your browser and try again after a few minutes"
          ];
        }
        break;
      case 'connect':
        $key = json_decode(keyDecrypt($_SESSION["key"]), true); //my keys
        //die($key);
        ////////////////////// SQL Statement //////////////////////
          $sql  = "SELECT a.id, a.user as target, a.message, a.time ";
          $sql .= " FROM {$table['init']} AS a ";
          $sql .= " LEFT JOIN {$table['users']} AS b ";
          $sql .= " ON a.target = b.id ";
          $sql .= " WHERE b.user = :ad";
          try {
            $stmt = $con->prepare($sql);
            $stmt->execute([":ad" => keyEncrypt($key["public"])]);
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

            if ($packet == "ack"){ //guest
              $_SESSION['init']  = "auth";
              $_SESSION['texts'] = json_encode(messageEnkrypt([]));
              $die = [
                "code"    => 200,
                "status"  => "ok",
                "message" => "clear"
              ];
            }else {
              $guestAd = get_id($con, $packet, $table['users'], "user", "id");
              if($guestAd['code'] === 200){ //admin
                $guestAd = keyDecrypt($guestAd['message']);
                $message = @messageEncrypt("ack", $guestAd);
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
                  $_SESSION['texts'] = json_encode(messageEnkrypt([]));;
                  $_SESSION["keys"] = json_encode([["address" => $guestAd, "id" => $packet]]);
                } else {
                  $die = [
                    "code"    => 507,
                    "status"  => "server error",
                    "message" => "ack failed"
                  ];
                }
              }
            }
          } else {//if no messages yet
            $die = [
              "code"    => 299,
              "status"  => "awaiting handshake",
              "message" => keyEncrypt($key["public"]),
            ];
            if (isset($_POST['host'])){//guest step 1
              //$hostId = get_id($con, $_POST['host']);
              $hostId = get_id($con, $_POST['host'], $table['users'], "user", "id");
              if ($hostId['code'] !== 200){//if host id was not found
                $die = [
                  "code"    => 404,
                  "status"  => "user not found",
                  "message" => base64_encode($key['public'])
                ];
              } else {//if host id is valid
                //$hostAd  = base64_decode($_POST["host"]);}
                $hostAd  = $_POST["host"];
                $die['message'] = keyEncrypt($hostAd);
              }
            }
          }
        ////////////////////// Ma ga na //////////////////////
        break;
      case 'start':
        $key = json_decode(keyDecrypt($_SESSION["key"]), true); //my keys

        $die = [
          'code' => 200,
          'status' => "starting handshake",
          'message' => "started handshake",
        ];
        break;
      case 'hermes':
        //////////////// Variables ////////////////
          $key  = json_decode($_SESSION["key"], true);
          $text = json_decode(messageDekrypt($_SESSION['texts']), true);
        //////////////// Variables ////////////////
        //////////////// part ////////////////
          if (isset($_POST['send'])){//send
            //////////////// Variables ////////////////
              $target = json_decode($_SESSION["keys"], true)[0]; //contacts basically
            //////////////// Variables ////////////////
            //////////////// Magana ////////////////
              $message = @messageEncrypt(base64_encode($_POST['text']), $target["address"]);
              $send    = initX($con, $table['messages'], [
                "id" => rand(999, 9999),
                "reciver" => $target['id'],
                "message" => $message,
                "time"    => $_POST['time']
              ]);

              if ($send['code'] == 200){
                $text[] = ["sender" => "sender", "message" => $_POST['text'], "time" => $_POST['time']];
                $_SESSION['texts'] = json_encode(messageEnkrypt($text)); /////////////////////////////////
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
                  "status"  => "not ok",
                  "message" => "message not sent"
                ];
              }
            //////////////// Magana ////////////////
          }else{ //receive
            //////////////// Variables ////////////////
              $end = [];
            //////////////// Variables ////////////////
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
                  $message = base64_decode(messageDecrypt($message, $key['secret'])); // write decode function
                  $text[]  = ["sender" => "receiver", "message" => $message, "time" => $value["time"]];
                  
                  $delete  = database_delete_data($con, $table['messages'], "id", $value["id"]);
                  
                  $end[]   = ["message" => $message, "time" => $value["time"]];

                }
                do {
                  $userUpdate = database_update_data($con, $table['users'], ["time" => time() - (60 * 10)], "id", $key['id']);
                } while ($userUpdate['code'] != 200);
                $_SESSION['texts'] = json_encode(messageEnkrypt($text));
                $die = [
                  "code"    => 200,
                  "status"  => "ok",
                  "message" => $end
                ];
              } else {
                $die = [
                  "code"    => 555,
                  "status"  => "not ok",
                  "message" => $return
                ];
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
  }else {
    $die = [
      "code"    => 405,
      "status"  => "unclear instruction",
      "message" => $_POST
    ];
  }
  
  echo(json_encode($die));
  ////////////////////// footer //////////////////////
    kron($con, $table['messages'], "time", time() - (60 * 10));
    kron($con, $table['init'], "time", time() - (60 * 15));
    kron($con, $table['users'], "time", time() - (60 * 20));
  ////////////////////// footer //////////////////////
?>
