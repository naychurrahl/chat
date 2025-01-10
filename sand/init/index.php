<?php

  session_start([
    'cookie_path' => '/',
    'cookie_secure' => true,
    'cookie_samesite' => 'none'
  ]);
  require_once("../../includes/php/functions.php");

  if (!isset($_SESSION['t'])){
    $myKey = kiis();
    $_SESSION["key"] = keyEncrypt($myKey);
    $_SESSION["t"] = "H1";
  } else {
    $myKey = keyDecrypt($_SESSION['key']);
  }
?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
  </head>
  <body>
    <div id="id">
    </div>
    <div id="host">
      <?php echo isset($_GET['host']) ? $_GET['host'] : false; ?>
    </div>
    <div id="t">
    </div>
  </body>
  <script>
    const k = document.getElementById("id");
    const l = document.getElementById("host");
    const t = document.getElementById("t");
    var r = l.innerHTML.trim(); //Host address (if false, you are host)
    setInterval(() => {
      if (Boolean(l.innerHTML.trim()) === true){ //Guest
        (async function join(counter = 0) {
          if (counter < 100){
            const first = await fetch("../api/", {
              method: 'POST',
              body: new URLSearchParams("action=join&host="+r),
            });
            const sec = await first.json();
            console.log(sec.code);
            switch (sec.code) {
              case 200:
                k.innerHTML = sec.status;
                l.innerHTML = " ";
                break;
              case 400:
                k.innerHtml = sec.messageg;
                join(1000);
              default:
                join(counter + 1)
                break;
            }
          } else {
            location.href = "./";
          }
        })();
      } else {
        (async () => {
          const first = await fetch("../api/", {
            method: 'POST',
            body: new URLSearchParams("action=hsec"),
          });
          const sec = await first.json();
          switch (sec.code) {
            case 200:
              console.log(sec.status);
              k.innerHTML = "cool";
              location.href = "../";
              break;
              
            default:
              k.innerHTML = sec.message;
              t.innerHTML = sec.status;
              console.log(sec.status);
              break;
          }
        })();
      }
    }, 1000);
  </script>
</html>