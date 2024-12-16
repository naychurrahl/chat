<?php
  session_start([ 
    'cookie_path' => '/',
    'cookie_secure' => true,
    'cookie_samesite' => 'none',
  ]);
  if(isset($_SESSION["init"]) & $_SESSION["init"] == "auth" & isset($_SESSION['texts'])){
    ///////////////////// Variable Declaration ////////////////////////////////
      
      $text = json_decode($_SESSION["texts"], true);

      function textNode($message, $time, $sender){
        echo "
        <div class=\"message-content {$sender}\">
          <label for=\"\">{$time}</label>
          <div class=\"msg-block\">
            <p>
              {$message}
            </p>
          </div>
        </div>";
      }

    ///////////////////// Variable Declaration ////////////////////////////////
    
    ///////////////////// Somethin ////////////////////////////////
  } else {
    //header("location: init.php");
    $text = "Guess not";
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
              <?php
              foreach ($text as $value) {
                echo textNode($value['message'], $value['time'], $value['sender']);
              }?>
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

  const lynks = {
    refreshRate: 3000,
    validation: "./sand/message.php",
  };

  /////////////////////////////   Don't Touch  /////////////////////////
    setInterval(function(){
      fetchMovies();
    }, lynks.refreshRate);

    var input = document.getElementById("input");
    input.addEventListener("keypress", function(event) {
      if (event.key === "Enter") {
        event.preventDefault();
        document.getElementById("basic-text1").click();
      }
    });
  /////////////////////////////   Don't touch  /////////////////////////    

  ///////////////////////////// Main character //////////////////////////////////////////
    async function fetchMovies() {
      const response = await fetch(lynks.validation+"?message");
      const movies = await response.json();

      //console.log(movies);
      switch (movies.code) {
       case "200":
          const messages = movies.message;
          Object.entries(messages).forEach(([key, val]) => {
            createTextNode(val.time, val.message, "receiver");
          });
          break;
        default:
          console.log("Well!! you just witnessed shit happen.");
          break;
      }
    }
  ///////////////////////////// Main character //////////////////////////////////////////

  ///////////////////////////// Side character //////////////////////////////////////////
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
   
  ////////////////////////////////// Send //////////////////////////////////////////
    async function onSend(params) {
      let messanger = document.getElementById("input");
      let message = messanger.value;
      messanger.value = "";

      if (message.length > 0){
        const t = new Date();
        let tyme = t.getTime();
        let mess = "send=send&text="+message+"&time="+tyme;
        const response = await fetch(lynks.validation, {
          method: 'POST',
          body: new URLSearchParams(mess)
         });
        const movies = await response.json();

        createTextNode(tyme, message, "sender");
      }
    }
  ////////////////////////////////// Send ////////////////////////////////////////// 

  </script>
</html>