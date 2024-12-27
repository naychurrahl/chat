<?php

/**
 * Research mysql onDelete cascade
 * we want on delete delete 
 * 
 */
  session_start([
    'cookie_lifetime' => 1,
    'cookie_path' => '/',
    'cookie_secure' => true,
    'cookie_samesite' => 'none']);
  //header("refresh: 5;");
  require_once("../includes/php/functions.php");
  if (!isset($_SESSION['key'])){
    
  } else {
    echo "<pre>";
    print_r($_SESSION);
  }
  
?>