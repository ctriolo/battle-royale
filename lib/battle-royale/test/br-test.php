<?php

require_once(dirname(__FILE__).'/../br.php');

$users->drop();
$players->drop();
$games->drop();

function assure($cond, $line, $file, $msg) {
  if (!$cond) print('Test failed at '.$line.' in '.$file.': '.$msg.PHP_EOL);
}

// CREATE USERS
$me = br_new_user('Christopher', 'Triolo', '1357410501', '+16313552173', true);
$test_admin = br_new_user('Madeleine', 'Ng', '656409011', '2', true);
$test_users = array( 
  br_new_user('Emily', 'Lancaster', '805075337', '4', true),
  br_new_user('Rafi', 'Romero', '1101120119', '6', true),
  br_new_user('Jasika', 'Bawa', '1665073483', '8', true),
  br_new_user('Nicolas', 'Hybel', '505085603', '10', true),
  br_new_user('Sean', 'Yi', '100003085552004', '12', true),
  br_new_user('Nitin', 'Viswanathan', '1367941817', '14', true),
  br_new_user('Damjan', 'Korac', '1223851741', '16', true),
  $me,
);
shuffle($test_users);

// NEW GAME
$error = br_create_game($test_admin['_id'],
			'Fun fun game',
			'This is a fun game.',
			'There are no rules.');
assure(!$error, __LINE__, __FILE__, $error);

// JOIN GAME
foreach ($test_users as $user) {
  $error = br_join_game($user['_id'], 'funfungame');
  assure(!$error, __LINE__, __FILE__, $error);
}

// BEGIN GAME
$error = br_begin_game($test_admin['_id']);
assure(!$error, __LINE__, __FILE__, $error);

$counter = 0;
$num = count($test_users);

// KILLING OMG KILLING
foreach ($test_users as $vuser) {
  $victim = br_get_active_player($vuser['_id']);
  if (!$victim) continue;
  foreach ($test_users as $kuser) {
    $killer = br_get_active_player($kuser['_id']);
    if (!$killer) continue;
    if ($victim['_id'] == $killer['target_id']) {
      $error = br_kill_request($killer['user_id']);
      assure(!$error, __LINE__, __FILE__, $error);
      $error = br_kill_accept($victim['user_id']);
      assure(!$error, __LINE__, __FILE__, $error);
      $counter++;
      //if ($num/2 < $counter) exit(); // testing mid way through game
      break;
    }
  }
}

// THE GAME IS OVER BITCHES

?>