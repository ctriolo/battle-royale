<?php

require_once 'constants.php';
require_once 'database.php';

include('header.php');

// decide on the content
if (!empty($_REQUEST['code'])) {
  $game = find_game(array('code' => $_REQUEST['code']));
  if ($game) {
    include('game.php');
  } else {
    echo '<p>Game not found try again!</p>';
    include('home.php');
  }
} else {
  include('home.php');
}

include('footer.php');
?>
