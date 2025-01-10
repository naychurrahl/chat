<?php

  header("Content-type: application/json; charset=utf-8");
  header('Access-Control-Allow-Origin: *');

  session_start([
    'cookie_lifetime' => 1,
    'cookie_path' => '/',
    'cookie_secure' => true,
    'cookie_samesite' => 'none']);
  require_once("../../includes/php/functions.php");
  
  if (isset($_POST['action'])){
    $myKey = keyDecrypt($_SESSION['key']);
    switch (trim(strtolower($_POST['action']))) {
      case 'init':
        $myKey = kiis();
        $_SESSION['key'] = keyEncrypt($myKey);
        $message = messageEncrypt($myKey['public'], $_SESSION['init']['address']); //Host's public key
        $die = [
          "code"    => 200,
          "status"  => "ok",
          "message" => $ink.$mPubKeyEnc,
        ];
        break;
      case 'hsec':
        if ($_SESSION['t'][1] === '3'){
          $die = [
            "code"    => 200,
            "status"  => $_SESSION['t'],
            "message" => $_SESSION['t'][0] == "G" ? $myKey['address'] : $ink.$myKey['address'],
          ];
        }else{
          ////////////////////// SQL Statement //////////////////////

            $return = getMessage(
              $con,
              $myKey['address'],
            );

          ////////////////////// SQL Statement //////////////////////
          if ($return['code'] == 200){
            $guest = substr($return['message']['message'], 1);
            $die = handShake($con, substr($return['message']['message'], 0, 1), $guest, $myKey, $return);
          } else{
            $die = [
              "code"    => 205,
              "status"  => $_SESSION['t'],
              "message" => $_SESSION['t'][0] == "G" ? $myKey['address'] : $ink.$myKey['address'],
            ];
          }
        }
        break;
      case 'join':
        $die = [
          "code"    => 800,
          "status"  => "initiating contact ...",
          "message" => $_POST['host'],
        ];
        if ($_POST['host'] != @$_SESSION['init']['address']){
          $message = $myKey['address'];
          $message = keyEncrypt($message);
          $message = '4'.$message;
          $return = sendMessage($con, $message, rand(999, 9999), $_POST['host']);
          if ($return['code'] == 200){
            $_SESSION['init']['address'] = $_POST['host'];
            $die['code'] = 200;
            $die['status'] = "sent address to Host";
            $die['message'] = $myKey['address'];
            $_SESSION['t'] = "G1";
          } else {
            $die['code'] = 400;
            $die['status'] = "not ok";
            $die['message'] = $return['message'];
          }
          
        } else {
          $die['code'] = 200;
          $die['message'] = "Refreshed hey?";
          $die['status'] = $_SESSION['t'];
        }
        break;
      case 'send':
        $message = messageEncrypt($_POST['text'], $myKey['private'], False);
        //$message = messageEncrypt($_POST['text'], $_SESSION['init']['key']);
        $return = sendMessage($con, $message, rand(999, 9999), $_SESSION['init']['address']);
        die(json_encode(['code' => 900, 'status' => $message, 'message' => hash(hash_algos()[2], $message)]));
        $message = '0'.$message;
        if ($return['code'] == 200){
          text($myKey, $_POST['text'], $_POST['time']);
          $die['code'] = 200;
          $die['status'] = "sent";
          $die['message'] = $message;
        } else{
          $die['code'] = 500;
          $die['status'] = "send error!";
          $die['message'] = $return['message'];
        }
        break;
      case 'fetch':
        ////////////////////// SQL Statement //////////////////////
          $end = [];                                             //
          $return = getMessage($con, $myKey['address'], True);   //
        ////////////////////// SQL Statement //////////////////////
        //////////////// Magana ////////////////
          switch ($return['code']) {
            case 200:
              foreach ($return["message"] as $value) {
                $message = hash(hash_algos()[2], $value["message"]);
                //$message = substr($value["message"], 1);
                //$message = messageDecrypt($message, $myKey['private']);
                //database_delete_data($con, "chat_test", "id", $value["id"]);
                die(json_encode(['code' => 900, 'status' => '', 'message' => $message]));
                $message = messageDecrypt($message, $_SESSION['init']['key'], False);
                //$message = base64_decode(messageDecrypt($message, $key['secret'])); // write decode function
                $text[]  = ["sender" => "receiver", "message" => $message, "time" => $value["time"]];
                
                //database_delete_data($con, "chat_test", "id", $value["id"]);
                //$_SESSION['texts'] = json_encode(messageEnkrypt($text));
                
                $end[]   = ["message" => $message, "time" => $value["time"]];
                
              }
              $die['code'] = 900;
              $die['status'] = 'ok';
              $die['message'] = $end;
              break;
            
            case 404:
              $die['code'] = 404;
              $die['status'] = 'listening...';
              $die['message'] = [];
              break;
            
            default:
              $die['code'] = 504;
              $die['status'] = 'error !...';
              $die['message'] = $return['message'];
              break;
          }
          if($return['code'] == 200){
            //$_SESSION['texts'] = json_encode(messageEnkrypt($text));
          }
        //////////////// Magana ////////////////
        break;
    }
  } else {
    $die = [
      "code" => 401,
      "status" => "mtcheeew",
      "message" => $_POST,
    ];
  }

  //kron($con, "chat_test", "time", ($identifier));
  die(json_encode($die));
?>
