<?php

  session_start(['cookie_path' => '/', 'cookie_secure' => true, 'cookie_samesite' => 'none']);
  require_once("../includes/php/functions.php");
  $ink = "http://127.0.0.1/dump/chat/sand/init.php?role=join&ad=";

  
  if (!isset($_SESSION["role"])){
    // create keys
      $key = kiis();
      $_SESSION["key"] = json_encode($key);
      do {
        $add = database_insert_unique($con, "chat_users", ["ad" => base64_encode($key["public"]), "time" => time()]);
        echo $add['code'];
      } while ($add['code'] !== 200);
    
      if (isset($_GET["role"])){
        switch (trim(strtolower($_GET["role"]))) {
          case 'join':
            $ad = base64_decode($_GET["ad"]);
            $_SESSION["keys"] = json_encode([$ad]);
            $_SESSION["role"] = "join";
            $id = get_id($con, $_GET['ad']);
            $message = base64_encode(pubkeyencrypt(base64_encode($add["message"]), $ad));
            do{
              $send = database_insert_unique($con, "chat_init", ["id" => rand(999, 9999), "target" => $id, "message" => $message, "time" => time()]);
            } while ($send['code'] !==  200);
            break;
          
          default:
            $_SESSION["role"] = "admin";  
            $link .= $ink.base64_encode($key["public"]);
            break;
        }
      } else {
        $_SESSION["role"] = "admin";  
        $link = $ink.base64_encode($key["public"]);
      }
    ///////////////////////////////////////////////
  } else {
    $send = ["Lol"];
    $link = $ink.base64_encode(json_decode($_SESSION["key"], true)["public"]);
  }

?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Init</title>
  </head>
  <body>
    <?php
      echo isset($link) & $_SESSION["role"] === "admin" ? $link : "Waiting for admin";
    ?>
  </body>
  <script>

    const lynks = {
      refreshRate: 3000,
      landingPage: "../",
      validation: "./init_message.php",
    };
   /////////////////////////////  Don't Touch  /////////////////////////
    setInterval(function(){
      fetchMovies();
    }, lynks.refreshRate);
   /////////////////////////////  Don't touch  /////////////////////////

   ///////////////////////////// Main character //////////////////////////////////////////
    async function fetchMovies() {
      const response = await fetch(lynks.validation);
      const movies = await response.json();
      
      console.log(movies.init);

      if (movies.init === "ok") {
        //redirect to chat page
        console.log("kk");
        location.href = lynks.landingPage;
      }
    }
   ///////////////////////////// Main character //////////////////////////////////////////

   ///////////////////////////// Side character //////////////////////////////////////////
    function getCookie(cookieName) {
      const cookies = document.cookie.split('; ');
      for (const cookie of cookies) {
        const [name, value] = cookie.split('=');
        if (name === cookieName) {
          return decodeURIComponent(value);
        }
      }
      return null;
    }

    function createTextNode(time_text, text, sender){
      const attribute = "message-content " + sender
 
      const cont = document.createElement("div")
      const labe = document.createElement("label")
      const blok = document.createElement("div")
      const para = document.createElement("p")
      const abel = document.createTextNode(time_text);
      const grap = document.createTextNode(text);
      labe.appendChild(abel);
      para.appendChild(grap);
      blok.appendChild(para);
      blok.setAttribute("class", "msg-block");
      cont.appendChild(labe);
      cont.appendChild(blok);
      cont.setAttribute("class", attribute);
      document.getElementById("ye").appendChild(cont);      
    }
   ///////////////////////////// Side character ////////////////////////////////////////// 
  </script>
</html>
