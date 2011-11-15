<?php
/**
 * sms-reply.php
 *
 * Appropriately respond to incoming text messages.
 */


/************
 * INCLUDES *
 ************/


require '../lib/twilio-php/Services/Twilio.php';
require 'constants.php';
require 'database.php';
session_start();


/********************
 * HELPER FUNCTIONS *
 ********************/


/**
 * makes a call to a specific number
 *
 * @param  to   string  number in the form of "+18005551234" 
 * @param  url  string  url of the response
 */
function make_call($to, $url) {
  $client = new Services_Twilio(TWILIO_ACCOUNT_SID, TWILIO_AUTH_TOKEN);
  $client->account->calls->create(TWILIO_PHONE_NUMBER, $to, $url);
}

/**
 * sends an sms to a specific number
 *
 * @param  to    string  number in the form of "+18005551234" 
 * @param  msg   format-string message to be sent
 * @param  args  variable number of args for the format string
 */
function send_sms(/*$to, $msg*/) {
  $args = func_get_args();
  $to = array_shift($args);
  $fmsg = array_shift($args);
  $msg = vsprintf($fmsg, $args);

  // instantiate a new Twilio Rest Client
  $client = new Services_Twilio(TWILIO_ACCOUNT_SID, TWILIO_AUTH_TOKEN);
  $client->account->sms_messages->create(TWILIO_PHONE_NUMBER, $to, $msg);
}					 


/**
 * sends a reply to the phone who sent us a text
 * IMPORTANT: THIS CAUSES THE THREAD TO EXIT
 *
 * @param  msg   format-string to reply with
 * @param  args  a variable number of args to be fed into the format string 
 */
function send_reply(/*$msg*/) {
  $args = func_get_args();
  $fmsg = array_shift($args);
  $msg = vsprintf($fmsg, $args);

  send_sms($_REQUEST['From'], $msg);
  exit();
}


/**
 * is the this person active in a game
 *
 * @param   phone  string  the phone number of the person
 * @return  bool   is this person an active admin  
 */
function is_active_participant($phone) {
  $participant = find_participant(array('phone' => $phone,
					'status' => PARTICIPANT_STATUS_ALIVE));
  return $participant;
}


/**
 * is the this person an active admin
 *
 * @param   phone  string  the phone number of the person
 * @return  bool   is this person an active admin
 */
function is_active_admin($phone) {
  return find_game(array('phone' => $phone,
			 '$or' => array(array('status' => GAME_STATUS_PENDING),
					array('status' => GAME_STATUS_ACTIVE))));
}						 


/******************
 * FLOW FUNCTIONS *
 ******************/


/**
 * the flow which creates a new game
 *
 * @param  from  string  number that the incoming text was sent from
 * @param  body  string  body of the incoming text
 */
function process_create($from, $body) {
  $person = find_person(array('phone' => $from));

  switch ($_SESSION['LOW']) {

  case CREATE_STATE_INIT:

    // error: don't allow active admins to create new games
    if (is_active_admin($person['phone'])) {
      send_reply(SMS_RESPONSE_CREATE_ERROR_ADMIN);
    }

    // error: don't allow active participants to create new games
    if (is_active_participant($person['phone'])) {
      send_reply(SMS_RESPONSE_CREATE_ERROR_ACTIVE);
    }

    $_SESSION['LOW'] = CREATE_STATE_RECEIVE;
    $_SESSION['HIGH'] = SMS_STATE_CREATE;
    send_reply(SMS_RESPONSE_CREATE_TITLE_REQUEST);

  case CREATE_STATE_RECEIVE:
    $title = $body;
    $code = strtolower(preg_replace('/\s+/', '_', $title)); 

    // error: the name they picked was a keyword
    if (strpos(SMS_KEYWORDS, $code)) {
      send_reply(SMS_RESPONSE_CREATE_ERROR_INVALID_TITLE, $title);
    }

    // error: check that game title doesn't already exist
    $game = find_game(array('code' => $code)); 
    if ($game) {
      send_reply(SMS_RESPONSE_CREATE_ERROR_TITLE_IN_USE, $title);
    }

    // create game and reply
    insert_game(array('title' => $title,
		      'code' => $code,
		      'admin' => $person['name'], 
		      'phone' => $from, 
		      'status' => GAME_STATUS_PENDING));
    $_SESSION['LOW'] = SMS_STATE_NULL;    
    $_SESSION['HIGH'] = SMS_STATE_NULL;
    send_reply(SMS_RESPONSE_CREATE_GAME_CREATED, $title, $code, TWILIO_PHONE_NUMBER_PRETTY);

  }
}


/**
 * the user is added to a game after requesting to join
 * 
 * @param  from  string  number that the incoming text was sent from
 * @param  body  string  body of the incoming text
 */
function process_join($from, $body) {

  $tokens = preg_split("/\s+/", $body);

  // error: no game name
  if (count($tokens) < 2) {
    send_reply(SMS_RESPOSE_JOIN_ERROR_NO_CODE);
  }

  $person =  find_person(array('phone' => $from));
  $game = find_game(array('code' => strtolower($tokens[1])));

  // error: game doesn't exist
  if (!$game) {
    send_reply(SMS_RESPONSE_JOIN_ERROR_NO_GAME);
  }

  // error: check that participant isn't already active in a game
  if (is_active_participant($person['phone'])) {
    send_reply(SMS_RESPONSE_JOIN_ERROR_ACTIVE);
  }

  // error: game has already
  if ($game['status'] != GAME_STATUS_PENDING) {
    send_reply(SMS_RESPONSE_JOIN_ERROR_ALREADY_BEGUN);
  }

  insert_participant(array('name' => $person['name'], 
			   'phone' => $person['phone'], 
			   'game_id' => $game['_id'], 
			   'status' => PARTICIPANT_STATUS_ALIVE,
			   'kills' => 0,
			   'escapes' => 0));
  send_sms($game['phone'],
	   'ADMIN: '.SMS_RESPONSE_JOIN_ACTIVITY,
	   $person['name'],
	   $game['title']);
  send_reply(SMS_RESPONSE_JOIN_SUCCESSFUL, $person['name'], $game['title']);
}


/**
 * the game that the user admins will begin 
 * 
 * @param  from  string  number that the incoming text was sent from
 * @param  body  string  body of the incoming text
 */
function process_begin($from, $body) {
  $game = find_game(array('phone' => $from));

  // error: you are not the admin
  if (!$game) {
    send_reply(SMS_RESPONSE_BEGIN_NOT_ADMIN);
  }

  // error: the game has already begun
  if ($game['status'] == GAME_STATUS_ACTIVE) {
    send_reply(SMS_RESPONSE_BEGIN_ALREADY_BEGUN);
  }

  // fetch every participant ($cursor is not garunteed to fetch every one)
  $cursor = find_participants(array('game_id' => $game['_id']));
  $game_participants = array();
  foreach ($cursor as $participant) {
    $game_participants[] = $participant;
  }

  // update every participants' target
  shuffle($game_participants);
  for ($i = 0; $i < count($game_participants); $i++) {
    $current = $game_participants[$i];
    $target = $game_participants[$i+1 == count($game_participants) ? 0 : $i+1];
    update_participants(array('_id' => $current['_id']), 
			 array('target_id' => $target['_id']));
    update_participants(array('_id' => $target['_id']), 
			array('killer_id' => $current['_id']));
    make_call($current['phone'], CALL_URL_FIRST_TARGET);
    send_sms($current['phone'],
	     SMS_RESPONSE_BEGIN_ANNOUNCE_TARGET,
	     $current['name'],
	     $target['name']);
  }

  // update the game's status
  update_games(array('_id' => $game['_id']),
	       array('status' => GAME_STATUS_ACTIVE));
    
  exit();
}


/**
 * marks the user's target, and asks the victim for confirmation
 * 
 * @param  from  string  number that the incoming text was sent from
 * @param  body  string  body of the incoming text
 */
function process_kill($from, $body) {
  $current = find_participant(array('phone' => $from,
				    'status' => PARTICIPANT_STATUS_ALIVE));

  // error: not in a game
  if (!$current) {
    send_reply(SMS_RESPONSE_KILL_ERROR_NO_GAME);
  }    

  $game = find_game(array('_id' => $current['game_id']));

  // error: the game hasn't started
  if ($game['status'] == GAME_STATUS_PENDING) {
    send_reply(SMS_RESPONSE_KILL_ERROR_GAME_PENDING);
  }    

  // error: already dead
  // TODO: query is conditioned on being alive so this would never happen remove?
  if ($current['status'] == PARTICIPANT_STATUS_DEAD) {
    send_reply(SMS_RESPONSE_KILL_ERROR_DEAD);
  }    
  
  // request confirmation
  $target = find_participant(array('_id' => $current['target_id']));
  update_participants(array('_id' => $target['_id']),
		      array('confirm' => true));
  send_sms($target['phone'], SMS_RESPONSE_KILL_CONFIRM_REQUEST);
  send_reply(SMS_RESPONSE_KILL_CONFIRM_REQUESTED);
}


/**
 * responds to the response of the person who may have been eliminated
 * 
 * @param  from  string  number that the incoming text was sent from
 * @param  body  string  body of the incoming text
 */
function process_confirm($from, $body) {
  $current = find_participant(array('phone' => $from,
				    'status' => PARTICIPANT_STATUS_ALIVE));
  $killer = find_participant(array('_id' => $current['killer_id']));
  $target = find_participant(array('_id' => $current['target_id']));

  if ($body == "y") {
    // confirmation accepted
    update_participants(array('_id' => $current['phone']),
			array('confirm' => false,
			      'status' => PARTICIPANT_STATUS_DEAD));
    update_participants(array('_id' => $target['_id']),
			array('killer_id' => $current['killer_id'],
			      'escapes' => $killer['escapes']+1));
    update_participants(array('_id' => $killer['_id']),
			array('target_id' => $current['target_id'],
			      'kills' => $killer['kills']+1));
    // TODO: call the victim with this info
    send_sms($current['phone'], SMS_RESPONSE_CONFIRM_VICTIM_ACCEPTED);
    if ($target['_id'] == $killer['_id']) {
      // the game is over

      // update the game and winner status
      $game = find_game(array('_id' => $current['game_id']));
      update_games(array('_id' => $game['_id']),
		   array('status' => GAME_STATUS_COMPLETE));
      update_participants(array('_id' => $killer['_id']),
			  array('status' => PARTICIPANT_STATUS_WINNER));

      // message administrator
      send_sms($game['phone'], 
	       'ADMIN:'.SMS_RESPONSE_CONFIRM_ANNOUNCE_WINNER,
	       $killer['name']);

      // message the winner
      send_sms($killer['phone'], SMS_RESPONSE_CONFIRM_ANNOUNCE_WINNER_TO_WINNER);

      // message everyone else
      $cursor = find_participants(array('game_id' => $game['_id']));
      foreach ($cursor as $participant) {
	if ($participant['_id'] == $killer['_id']) continue; 
	send_sms($participant['phone'], SMS_RESPONSE_CONFIRM_ANNOUNCE_WINNER);
      }
    } else {
      // send the killer his new target
      // TODO: call the assassin with this info
      send_sms($killer['phone'], SMS_RESPONSE_CONFIRM_NEXT_TARGET, $target['name']); 
    }

    exit();

  } else if ($body == 'n') {
    // confirmation rejected
    update_participants(array('phone' => $current['phone']), array('confirm' => false));
    send_sms($killer['phone'], SMS_RESPONSE_CONFIRM_KILLER_REJECTED);
    send_reply(SMS_RESPONSE_CONFIRM_VICTIM_REJECTED);
    // TODO: actually notify the admin
  } else {
    // lolwut, try again
    send_reply(SMS_RESPONSE_KILL_CONFIRM_REQUEST);
  }

}
    

/**
 * retreives the name of the person texting us andassociates it with the number
 *
 * @param  from  string  number that the incoming text was sent from
 * @param  body  string  body of the incoming text
 */
function process_name($from, $body) {
  switch($_SESSION['LOW']) {

  case NAME_STATE_INIT:
    $_SESSION['LOW'] = NAME_STATE_RECEIVE;
    send_reply(SMS_RESPONSE_NAME_REQUEST);

  case NAME_STATE_RECEIVE:
    // error: the name they picked was a keyword
    if (strpos(SMS_KEYWORDS, $body)) {
      send_reply(SMS_RESPONSE_NAME_ERROR_INVALID_NAME);
    }
    
    // insert the person into our database
    insert_person(array('name' => $body, 'phone' => $from));
    $_SESSION['LOW'] = SMS_STATE_NULL;
    $_SESSION['HIGH'] = SMS_STATE_NULL;
  }
}


/**
 * processes the initial sms and sends it off to the correct lower level
 * process flow function based on the state stored in $_SESSION['HIGH']
 *
 * @param  from  string  number that the incoming text was sent from
 * @param  body  string  body of the incoming text
 */
function process_sms($from, $body) {
  $body = trim($body);
  $person = find_person(array('phone' => $from));
  $participant = find_participant(array('phone' => $from,
					'status' => PARTICIPANT_STATUS_ALIVE));

  // initialization
  if (!isset($_SESSION['HIGH'])) {
    $_SESSION['HIGH'] = SMS_STATE_NULL;
    $_SESSION['LOW'] = SMS_STATE_NULL;
  }
  
  // we have never been texted by this person before
  // so lets get their info
  if (!$person) {
    if ($_SESSION['HIGH'] != SMS_STATE_NAME) {
      $_SESSION['HIGH'] = SMS_STATE_NAME;
      $_SESSION['LOW'] = NAME_STATE_INIT;
      $_SESSION['BLOCKED_MESSAGE'] = $body;
      process_name($from, $body);
    } else {
      process_name($from, $body);
      $body = $_SESSION['BLOCKED_MESSAGE'];
    }
  }

  // they have been asked for a confirmation
  if (!empty($participant['confirm'])) {
    process_confirm($from, $body);
  }

  $tokens = preg_split("/\s+/", $body);

  if ($tokens[0] == SMS_KEYWORD_CANCEL) {
    $_SESSION['HIGH'] == SMS_STATE_NULL;
    send_reply(SMS_RESPONSE_CANCEL);
  }

  // if there is a current state go to the correct processor
  switch ($_SESSION['HIGH']) {
  case SMS_STATE_CREATE: process_create($from, $body);
  default: break; 
  }

  // if there is no current high level state reset the lower level state
  $_SESSION['LOW'] == SMS_STATE_NULL;

  // if there was a keyword go to the correct processor
  switch (strtolower($tokens[0])) {
  case SMS_KEYWORD_CREATE: process_create($from, $body);
  case SMS_KEYWORD_JOIN: process_join($from, $body);
  case SMS_KEYWORD_BEGIN: process_begin($from, $body);
  case SMS_KEYWORD_KILL: process_kill($from, $body);
  case SMS_KEYWORD_HELP: send_reply(SMS_RESPONSE_HELP);
  default: break;
  }
    
  send_reply(SMS_RESPONSE_DERP);
}


// MAIN
process_sms($_REQUEST['From'],
	    $_REQUEST['Body']);

?>

