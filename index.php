<?php
  session_start([ 
    'cookie_path' => '/',
    'cookie_secure' => true,
    'cookie_samesite' => 'none',
  ]);
  if(isset($_SESSION["init"]) & $_SESSION["init"] == "auth" & isset($_SESSION['texts'])){
    ///////////////////// Variable Declaration ////////////////////////////////
      
      $keys = json_decode($_SESSION["keys"], true);
      $target = $keys[0];
      $key = json_decode($_SESSION["key"], true);

    ///////////////////// Variable Declaration ////////////////////////////////
    
    ///////////////////// Somethin ////////////////////////////////
  } else {
    //header("location: init.php");
    $keys = "Guess not";
  }
?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8">
    <title>Chat</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Free chat html download. Bootstrap 4 Chat Box html template download from Code Pen.">
    <meta name="keywords" content="Free chat htmlTML, Bootstrap 4 Chat Box, CSS">
    <link rel="stylesheet" href="./includes/css/bootstrap.css" >
    <link rel="stylesheet" href="./includes/css/index.css" >
  </head>
  
  <body>
    <div class="container" >
      <div class="row">
        <div class="col-12">
          <div class="single-chat-tab">
            <div class="chat-body" id="ye">
              <!--div class="message-content receiver">
                <label for="">11:33 PM, Yesterday</label>
                <div class="msg-block">
                  <p>
                    bibendum egestas augue.Duis sit amet ante feugiat enim viverra sagittis.
                  </p>
                </div>
              </div>
              <div class="message-content sender">
                <label for="">11:33 PM, Yesterday</label>
                <div class="msg-block">
                  <p>
                    bibendum egestas augue.Duis sit amet ante feugiat enim viverra sagittis.
                  </p>
                </div>
              </div>
              <div class="message-content sender">
                <label for="">11:33 PM, Yesterday</label>
                <div class="msg-block">
                  <p>
                    bibendum egestas augue.Duis sit amet ante feugiat enim viverra sagittis.
                  </p>
                </div>
              </div>
              <div class="message-content sender">
                <label for="">11:33 PM, Yesterday</label>
                <div class="msg-block">
                  <p>
                    b
                  </p>
                </div>
              </div>
              <div class="message-content receiver">
                <label for="">11:33 PM, Yesterday</label>
                <div class="msg-block">
                  <p>
                    Last from the other side
                  </p>
                </div>
              </div>
              <div class="message-content sender">
                <label for="">11:33 PM, Yesterday</label>
                <div class="msg-block">
                  <p>
                    Come
                  </p>
                </div>
              </div-->
            </div>
            <div class="chat-footer">
              <div class="input-group md-form form-sm form-2 pl-0">
                <input class="form-control my-0 py-1 red-border" id="input" type="text" placeholder="Write a message...">
                <div class="input-group-append">
                  <button class="btn input-group-text red lighten-3" id="basic-text1" onclick="onSend('Moo')">
                    <i class="material-icons">send</i>
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
</body>
  <script>

    let lynk = './sand/message.php';
    let refreshRate = 5000;
   /////////////////////////////   Don't Touch  /////////////////////////
    setInterval(function(){
      fetchMovies();
    }, refreshRate);
   /////////////////////////////   Don't touch  /////////////////////////

    var input = document.getElementById("input");
    input.addEventListener("keypress", function(event) {
      if (event.key === "Enter") {
        event.preventDefault();
        document.getElementById("basic-text1").click();
      }
    });

   ///////////////////////////// Main character //////////////////////////////////////////
    async function fetchMovies() {
      const response = await fetch(lynk+"?message");
      const movies = await response.json();

      console.log(movies);
      switch (movies.code) {
       case "200":
          const messages = movies.message;
          Object.entries(messages).forEach(([key, val]) => {
            createTextNode(val.time, val.message, "receiver");
          });
          break;
        default:
          console.log("now here");
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
      const element = document.getElementById("ye");
 
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
      element.appendChild(cont);

      let elements = document.querySelector('div#ye > div:last-of-type');
      elements.scrollIntoView(true);
    }
   ///////////////////////////// Side character ////////////////////////////////////////// 
   
   ///////////////////////////// Send //////////////////////////////////////////
    async function onSend(params) {
      let tyme = 0;
      let messanger = document.getElementById("input");
      let message = messanger.value;
      messanger.value = "";
      let data = {
        send:"send",
      }

      if (message.length > 0){
        const t = new Date();
        let tyme = t.getTime();
        let mess = "send=send&text="+message+"&time="+tyme;
        const response = await fetch(lynk, {
          method: 'POST',
          body: new URLSearchParams(mess)
        });
        const movies = await response.json();

        console.log(movies);
        createTextNode(tyme, message, "sender");
      }
    }
   ///////////////////////////// Send ////////////////////////////////////////// 

  </script>
</html>