<?php

function send_reply($str) {
  header("content-type: text/xml");
  echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
  echo "<Response>\n<Sms>\n";
  echo $str;
  echo "\n</Response>\n</Sms>\n";
  exit();
}

$from = $_REQUEST['From'];
$body = $_REQUEST['Body'];

$from = "+13057736239";
$body = "create game";

// CREATE GAME
if ($body == "create game") {
  fopen("../data/persons/" . $from, "w");

  $reply = "Welcome to KAOS! Please enter a group password ";
  $reply .= "that others will use to join your game. ";
  $reply .= "e.g. PrincetonClub2011";
  send_reply($reply);
}

// JOIN GAME
// START GAME
// REPORT KILL
// ANNOUNCE

send_reply($name .= ", thanks for the message!");

?>
