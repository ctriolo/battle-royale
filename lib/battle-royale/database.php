<?php
/**
 * database.php
 *
 * Connect to the database and add minimal accessor and update functions.
 */

/**************
 * CONNECTION *
 **************/

$m = new Mongo();
$db = $m->battle_royale;

/***************
 * COLLECTIONS *
 ***************/

$people = $db->people;
$participants= $db->participants;
$games = $db->games;

/************
 * ACCESSOR *
 ************/

function find_person($query) {
  return $GLOBALS['people']->findOne($query);
}

function find_people($query) {
  return $GLOBALS['people']->find($query);
}

function find_participant($query) {
  return $GLOBALS['participants']->findOne($query);
}

function find_participants($query) {
  return $GLOBALS['participants']->find($query);
}

function find_game($query) {
  return $GLOBALS['games']->findOne($query);
}

function find_games($query) {
  return $GLOBALS['games']->find($query);
}

/************
 * MODIFIER *
 ************/

function insert_person($person) {
  return $GLOBALS['people']->insert($person);
}

function insert_game($game) {
  return $GLOBALS['games']->insert($game);
}

function insert_participant($participant) {
  return $GLOBALS['participants']->insert($participant);
}

function update_people($query, $set) {
  return $GLOBALS['people']->update($query,
				    array('$set'=>$set));
}

function update_participants($query, $set) {
  return $GLOBALS['participants']->update($query,
				    array('$set'=>$set));
}

function update_games($query, $set) {
  return $GLOBALS['games']->update($query,
				    array('$set'=>$set));
}

?>