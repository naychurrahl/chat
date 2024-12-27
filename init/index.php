<?php

  session_start(['cookie_path' => '/', 'cookie_secure' => true, 'cookie_samesite' => 'none']);
  require_once("../includes/php/functions.php");
  if (isset($_SESSION['init'])){
    header("location: {$landing}");
  } else {
    if (isset($_GET['ad'])){
      $mess = "action=init&sub={$_GET['ad']}&guest";
    } else {
      $mess = "action=init";
    }
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
    <div id="b"></div>
    </div>
  </body>
  <script>

    const lynks = {
      refreshRate: 1000,
      landingPage: "<?php echo $landing;?>",
      init: "../api/",
      mess: "<?php echo $mess;?>",
    };
   /////////////////////////////  Don't Touch  /////////////////////////
    setInterval(function(){
        initiate();
      }, lynks.refreshRate);
   /////////////////////////////  Don't touch  /////////////////////////

   ///////////////////////////// Main character //////////////////////////////////////////

    async function initiate() {
      let l = document.getElementById("b");
      const response = await fetch(lynks.init, {
        method: 'POST',
        body: new URLSearchParams(lynks.mess)
      });
      const movies = await response.json();
      console.log(movies);
      switch (movies.code) {
        case 200:
          if (movies.message == "clear"){
            location.href = lynks.landingPage;
          }
        case 209:
          l.innerHTML = movies.message;
          console.log("code: "+movies.code);
          break;
      
        default:
          console.log("not");
          break;
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
