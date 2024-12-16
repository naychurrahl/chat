<?php
  session_start([ 
    'cookie_path' => '/',
    'cookie_secure' => true,
    'cookie_samesite' => 'none',
  ]);
  if(isset($_SESSION["init"]) & $_SESSION["init"] == "auth"){
    ///////////////////// Variable Declaration ////////////////////////////////
      
      $keys = json_decode($_SESSION["keys"], true);
      $target = $keys[0];
      $key = json_decode($_SESSION["key"], true);

    ///////////////////// Variable Declaration ////////////////////////////////
    
    ///////////////////// Somethin ////////////////////////////////
    //header("location: init.php");
  } else {
    $keys = "Guess not";
  }
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?php print_r("Chat") ?></title>
</head>
<body>
  <div id = "no">
  <?php print_r($key["public"]); ?>
  </div>

  <div id = "ye">
  </div>
</body>
  <script>

    let refreshRate = 5000;
   /////////////////////////////   Don't Touch  /////////////////////////
    setInterval(function(){
      fetchMovies();
    }, refreshRate);
   /////////////////////////////   Don't touch  /////////////////////////


   ///////////////////////////// Main character //////////////////////////////////////////
    async function fetchMovies() {
      const response = await fetch('./message.php');
      const movies = await response.json();

      //let h = JSON.parse(getCookie("keys"));
      //let k = JSON.parse(getCookie("key"));
      //let h = <?php //echo $_SESSION["keys"]?>;
      //let k = <?php //echo $_SESSION["key"]?>;
      
     switch (movies.code) {
       case "200":
         const messages = movies.message;
         Object.entries(messages).forEach(([key, val]) => {
            
            //console.log(JSON.parse(getCookie("texts")));
            
            createTextNode(val.time, val.message, "receiver");
            console.log(val.message);
          });
          break;

        default:
          console.log("now here");
          break;
      //console.log("Part time");
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

    //fetchMovies();
    /**
     * push to cookies upon send
     * push to cookies upon receive
     * 
     * 
     * 
     * LS0tLS1CRUdJTiBQVUJMSUMgS0VZLS0tLS0KTUlJQklqQU5CZ2txaGtpRzl3MEJBUUVGQUFPQ0FROEFNSUlCQ2dLQ0FRRUF3RmM4V29HMG82MmlxdHhyL3BkNQpzZW1XRWpianJLYkxtS2dXd1gxSFl2b3V1ZE9GSWZpYll5OS8rQUpKUXQzTzgwVzlzL1hoamhzcWRrcWdZQ2FzCmlaTmR3QkJVRGZzZktmbGFLcjFOUnYvNHBDMkdmNkZmZmxwVVF5SEJYQWdSUW1lOEkwang1emVJN2k4czhDNDUKbFVHUzdpMEpZYTBvcnVZa2hHUVdPOWplenZ1TzV3R3BPTWd4YkgvVDZXME1GMDlBa2orYS9NVmlicWxhbUt3bgp2SUVJVFpmQkx4UHBraFJZMkViZVFLZXh5bHptU09JV0NyZ0xWZGZuU0E2eW1kandhWlp5SnFleWlKQnZjSjNPCkU3M2hZdURnYXg5YklXOTNVOU9TNHVpLzFQaVpWY3NJU3Vya1pKTWV1djVrV1lodi9jOGtxNXNIQTMrZHJXUDIKUVFJREFRQUIKLS0tLS1FTkQgUFVCTElDIEtFWS0tLS0tCg
     */
  </script>
</html>