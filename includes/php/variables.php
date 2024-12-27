<?php

  $die = [
    "code" => 409,
    "message" => "something went wrong. Refresh",
    "status" => "unknown error!"
  ];

  $ink = "http://127.0.0.1/dump/chat/init/?ad=";
  $landing = "../";

  $table = [
    "messages" => "chat_messages",
    "users" => "chat_users",
    "init" => "chat_init"
  ];

  /**
   * Disconnect button
   * 
   * ************ variables **************
   * Sessions
   *  key
   *  texts
   *  //init
   * 
   * Post
   *  action
   *    init
   *    transport
   *      send
   *      receive
   *  ad | sub
   *    address
   *  role
   *    host
   *    guest
   * 
   * *************** codes ***************
   * 2xx -> ok
   * 3xx -> redirect
   * 4xx -> errors
   * 5xx -> server
   */
?>