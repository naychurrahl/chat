<?php

  session_start(['cookie_path' => '/', 'cookie_secure' => true, 'cookie_samesite' => 'none']);
  require_once("../includes/php/functions.php");

  if (isset($_SESSION['role'])) {
    echo $_SESSION['role'];
  } else {
    echo "Prank";
  }
?>