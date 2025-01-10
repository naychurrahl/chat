<?php

  session_start(['cookie_path' => '/', 'cookie_secure' => true, 'cookie_samesite' => 'none']);
  require_once("../includes/php/functions.php");
  if (isset($_SESSION['init'])){
    if ($_SESSION['init'] === "auth"){
      header("location: {$landing}");
    }
  }
  if (isset($_GET['host'])){
    $host = $_GET['host'];
  } else {
    $host = FALSE;
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
    <div id="a"></div>
    <div id="b"></div>
    <div id="c"></div>
  </body>
  <script>

    //var init = Boolean(<?php //echo isset($_SESSION['key']); ?>);
    const lynks = {
      refreshRate: 1000,
      landingPage: "<?php echo $landing;?>",
      init: "../api/",
      //mess: "<?php //echo $mess;?>",
    };
    const j = document.getElementById("a");
    var host = "<?php echo $host; ?>";
    const k = document.getElementById("b");
    k.innerHTML = Boolean(<?php echo isset($_SESSION['key']); ?>);

   /////////////////////////////  Don't Touch  /////////////////////////
    async function nit(){
      //const k = document.getElementById("b");
      const resp = await fetch(lynks.init,{
        method: 'POST',
        body: new URLSearchParams('action=init')
      });
      const toj = await resp.json();
      if (toj.code == 200){
        console.log(toj.message);
        k.innerHTML = true;
      } else {
        k.innerHTML = false;
      }
    }
    
    async function beut(param) {
      /* if (k.innerHTML == 'true'){
        console.log("coo");
      } else {
        console.log("loo");
      } */
     let l = document.getElementById("c");
     let mess = "action=connect";
      if (param != false){
        mess += "&host="+param
      }
      console.log("step 3: "+param)
      const response = await fetch(lynks.init, {
        method: 'POST',
        body: new URLSearchParams(mess)
      });
      const movies = await response.json();
      //console.log(movies);
      switch (movies.code) {
        case 200:
          if (movies.message == "clear"){
            location.href = lynks.landingPage;
          }
        case 404:
        case 299:
        case 209:
          l.innerHTML = "host => http://127.0.0.1/dump/chat/init?host="+movies.message;
          console.log("code: "+movies.status);
          break;
      
        default:
          console.log(movies);
          break;
      }
    }
   /////////////////////////////  Don't Touch  /////////////////////////
  
   /////////////////////////////  Don't Touch  /////////////////////////
    setInterval(function(){
      //initiate();
      if(k.innerHTML == 'true'){
        console.log("step 1: "+k.innerHTML);
        let i = Boolean(host);
        if (i == false){
          console.log("step 2a: "+i);
          //console.log("wood");
          beut(i);
        } else {
          console.log("step 2b: "+host);
          beut(host);
        }
      } else {
        nit();
      }
      //init = k.innerHTML
    }, lynks.refreshRate);
   /////////////////////////////  Don't touch  /////////////////////////

   /*//////////////////////////// Main character //////////////////////////////////////////

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
        case 404:
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
   ///////////////////////////// Side character ///////////////////////////////////////// */
  </script>
</html>
