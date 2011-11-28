<?php
/**
 * br.php
 *
 * battle-royale library functions
 * 
 */

/******************
 * DEBUG CONTROLS *
 ******************/

$TEST_DB = false;
$TWILIO = false;

/************
 * INCLUDES *
 ************/

require_once(dirname(__FILE__).'/constants.php');
require_once(dirname(__FILE__).'/../facebook/src/facebook.php');
require_once dirname(__FILE__).'/../twilio-php/Services/Twilio.php';

/***************
 * CONNECTIONS *
 ***************/

// mongo
$m = new Mongo();
if (empty($TEST_DB)) $db = $m->br;
else $db = $m->test;
$users = $db->users;
$players= $db->players;
$games = $db->games;

// facebook
$facebook = new Facebook(array(
  'appId'  => FACEBOOK_APP_ID,
  'secret' => FACEBOOK_APP_SECRET,
));

// twilio
$twilio = new Services_Twilio(TWILIO_ACCOUNT_SID, TWILIO_AUTH_TOKEN);

/********
 * GETS *
 ********/

function br_get_user($user_id) {
  return $GLOBALS['users']->findOne(array('_id' => $user_id));
}

function br_get_user_by_fbid($facebook_id) {
  return $GLOBALS['users']->findOne(array('facebook_id' => $facebook_id));
}

function br_get_active_player($user_id) {
  return $GLOBALS['players']->findOne(array(
    'user_id' => $user_id,
    'status' => PLAYER_STATUS_ALIVE,
  ));
}

function br_get_active_game($user_id) {
  $player = br_get_active_player($user_id);
  return $GLOBALS['games']->findOne(array('_id' => $player['game_id']));
}

function br_get_past_games($user_id) {
  $players = $GLOBALS['players']->find(array(
    '$or' => array(
      array(
        'user_id' => $user_id,
        'status' => PLAYER_STATUS_WINNER,
      ),
      array(
        'user_id' => $user_id,
        'status' => PLAYER_STATUS_DEAD,
      ),
    ),
  ));

  $queries = array();
  foreach ($players as $player) {
    $queries[] = array('_id' => $player['game_id']);
  }

  if (!$queries) return $players; // TODO: fix hack, return empty cursor please

  return $GLOBALS['games']->find(array('$or' => $queries));
}

function br_get_active_admin($user_id) {
  return $GLOBALS['games']->findOne(array(
    '$or' => array(
      array(
        'admin_id' => $user_id,
        'status' => GAME_STATUS_PENDING,
      ),
      array(
        'admin_id' => $user_id,
        'status' => GAME_STATUS_ACTIVE,
      ),
    ),
  ));
}

function br_get_past_admins($user_id) {
  return $GLOBALS['games']->find(
    array('admin_id' => $user_id, 'status' => GAME_STATUS_COMPLETE));
}

function br_get_player($player_id) {
  return $GLOBALS['players']->findOne(array('_id' => $player_id));
}

function br_get_user_in_game($user_id, $game_id) {
  return $GLOBALS['players']->findOne(array('game_id' => $game_id,
					    'user_id' => $user_id));
}

function br_get_game_players($game_id) {
  return $GLOBALS['players']->find(array('game_id' => $game_id));
}

function br_get_winner($game_id) {
  return $GLOBALS['players']->findOne(array('game_id' => $game_id,
					    'status' => PLAYER_STATUS_WINNER));
}

function br_get_alive($game_id) {
  return $GLOBALS['players']->find(array('game_id' => $game_id,
					 'status' => PLAYER_STATUS_ALIVE));
}

function br_get_dead($game_id) {
  return $GLOBALS['players']->find(array('game_id' => $game_id,
					 'status' => PLAYER_STATUS_DEAD));
}

function br_get_game($game_id) {
  return $GLOBALS['games']->findOne(array('_id' => $game_id));
}

function br_get_game_by_code($game_code) {
  return $GLOBALS['games']->findOne(array('code' => $game_code));
}

/********
 * USER *
 ********/

function br_new_user($first_name, $last_name, $facebook_id, $phone='', $phone_verified=false) {
  $GLOBALS['users']->insert(array(
    'name' => $first_name.' '.$last_name,
    'first_name' => $first_name,
    'last_name' => $last_name,
    'facebook_id' => $facebook_id,
    'phone' => $phone,
    'phone_verified' => $phone_verified,
  ));
  return $GLOBALS['users']->findOne(array('facebook_id' => $facebook_id));
}

function br_get_current_user() {
  $user = null;
  $fb_user = $GLOBALS['facebook']->getUser();
  if ($fb_user) {
    try {
      // Proceed knowing you have a logged in user who's authenticated.
      $fb_user = $GLOBALS['facebook']->api('/me');

      // Grab battle-royal user
      $user = br_get_user_by_fbid($fb_user['id']);

      // The first time a user is logging in
      // insert him/her into our database
      if (!$user) {
	$user = br_new_user($fb_user['first_name'],
			    $fb_user['last_name'],
			    $fb_user['id']);
      }
    } catch (FacebookApiException $e) {
      error_log($e);
    }
  }

  return $user;
}

function br_get_log_in_url() {
  return $GLOBALS['facebook']->getLoginUrl(array(
    'scope' => 'user_education_history',
    'display' => 'page'
  ));
}

function br_get_log_out_url() {
  return $logoutUrl = $GLOBALS['facebook']->getLogoutUrl();
}

function br_user_phone_is_verified($user_id) {
  $user = br_get_user($user_id);
  return !empty($user['phone_verified']);
}

function br_user_request_verification($user_id) {
  $user = br_get_user($user_id);
  
  if (!empty($user['phone_verified'])) {
    return 'Your phone is already verified'; 
  }

  if (empty($user['phone_code'])) {
    return 'You don\'t have a phone attached to your account';
  }

  br_message_phone($user['phone'], 'Your Battle Royale verification code is: %s', $user['phone_code']);

  return '';
}

function br_user_new_phone($user_id, $phone) {
  $user = br_get_user($user_id);

  $length = strlen($phone);
  if ($length != 12 || '+' != $phone[0] || !ctype_digit(substr($phone, 1, 12))) {
    return 'The phone number is not in the correct format';
  }

  $phone_code = substr(base_convert(rand(10e16, 10e20), 10, 36), 0, 4);

  $GLOBALS['users']->update(
    array('_id' => $user_id),
    array('$set' => array(
      'phone' => $phone,
      'phone_verified' => false,
      'phone_code' => $phone_code)));

  return br_user_request_verification($user_id);
}

function br_user_verify_phone($user_id, $phone_code) {
  $user = br_get_user($user_id);

  if ($user['phone_code'] != $phone_code) {
    return 'Your code doesn\'t match';
  }

  // Mark phone as verified
  $GLOBALS['users']->update(
    array('_id' => $user['_id']),
    array('$set' => array('phone_verified' => true)));

  // Update all the other phone records
  $GLOBALS['players']->update(
    array('user_id' => $user['_id']),
    array('$set' => array('phone' => $user['phone'])));

  return '';
}

/*****************
 * COMMUNICATION *
 *****************/

function br_call_phone($phone, $url) {
  if (!empty($GLOBALS['TWILIO']))
    $GLOBALS['twilio']->account->calls->create(
      TWILIO_PHONE_NUMBER, $to, $url);
  else error_log('CALL '.$phone.': '.$url);
}

function br_message_phone(/*$phone, $message_format, $arg1, $arg2, ...*/) {
  $args = func_get_args();
  $phone = array_shift($args);
  $message_format = array_shift($args);
  $message = vsprintf($message_format, $args);

  if (!empty($GLOBALS['TWILIO']))
    $GLOBALS['twilio']->account->sms_messages->create(
      TWILIO_PHONE_NUMBER, $phone, $message);
  else error_log('MSG '.$phone.': '.$message);
}

function br_message_player(/*$player_id, $message_format, $arg1, $arg2, ...*/) {
  $args = func_get_args();
  $player_id = array_shift($args);
  $message_format = array_shift($args);
  $message = vsprintf($message_format, $args);
  $player = br_get_player($player_id);

  br_message_phone($player['phone'], $message);
}

function br_message_game(/*$game_id, $message_format, $arg1, $arg2, ...*/ ) {
  $args = func_get_args();
  $game_id = array_shift($args);
  $message_format = array_shift($args);
  $message = vsprintf($message_format, $args);
  $players = br_get_game_players($game_id);

  foreach ($players as $player) {
    br_message_phone($player['phone'], $message);
  }
}

function br_message_admin(/*$game_id, $message_format, $arg1, $arg2, ...*/ ) {
  $args = func_get_args();
  $game_id = array_shift($args);
  $message_format = array_shift($args);
  $message = vsprintf($message_format, $args);
  $game = br_get_game($game_id);
  $user = br_get_user($game['admin_id']);

  br_message_phone($user['phone'], 'ADMIN: '.$message);
}

/********
 * GAME *
 ********/

function br_create_game($admin_id, $title, $description, $rules) {
  $admin = br_get_user($admin_id);
  $code = $title;
  $code = strtolower(preg_replace("/[^A-Za-z0-9]/", "", $code));

  if (!$admin['phone'] || !$admin['phone_verified']) {
    return 'You do not have a verified phone number.';
  }

  if (br_get_active_player($admin_id)) {
    return MSG_JOIN_ERROR_ACTIVE;
  }

  if (br_get_active_admin($admin_id)) {
    return 'You are already administrating a game.';
  }

  if (br_get_game_by_code($code)) {
    return 'Title already in use.';
  } else {
    $GLOBALS['games']->insert(array(
      'title' => $title,
      'description' => $description,
      'rules' => $rules,
      'code' => $code,
      'admin_id' => $admin_id,
      'status' => GAME_STATUS_PENDING,
    ));
  }
  
  return '';
}

function br_join_game($user_id, $game_code) {
  $user = br_get_user($user_id);
  $game = br_get_game_by_code($game_code);

  if (!$game) {
    return MSG_JOIN_ERROR_NO_GAME;
  }

  if ($game['status'] != GAME_STATUS_PENDING) {
    return MSG_JOIN_ERROR_ALREADY_BEGUN;
  }

  if (!$user['phone'] || !$user['phone_verified']) {
    return 'You do not have a verified phone number.';
  }

  if (br_get_active_player($user_id)) {
    return MSG_JOIN_ERROR_ACTIVE;
  }

  if (br_get_active_admin($user_id)) {
    return 'You are already administrating a game.';
  }

  $player_id = new MongoId();
  $GLOBALS['players']->insert(array(
    '_id' => $player_id,
    'user_id' => $user['_id'],
    'game_id' => $game['_id'],
    'facebook_id' => $user['facebook_id'],
    'name' => $user['name'],
    'first_name' => $user['first_name'],
    'last_name' => $user['last_name'],
    'phone' => $user['phone'],
    'status' => PLAYER_STATUS_ALIVE,
    'kills' => 0,
    'evades' => 0,
    'points' => 0,
    'killed' => array(),
    'evaded' => array(),
  ));

  br_message_phone($user['phone'], MSG_JOIN_SUCCESSFUL,
		   $user['first_name'], $game['title']);
  br_message_admin($game['_id'], MSG_JOIN_ACTIVITY,
		   $user['name'], $game['title']);

  return '';
}

function br_begin_game($admin_id) { 
  $game = br_get_active_admin($admin_id);
  
  if (!$game) {
    return MSG_BEGIN_NOT_ADMIN;
  }

  if ($game['status'] == GAME_STATUS_ACTIVE) {
    return MSG_BEGIN_ALREADY_BEGUN;
  }

  // fetch every participant ($cursor is not garunteed to fetch every one)
  $cursor = br_get_game_players($game['_id']);
  $game_participants = array();
  foreach ($cursor as $participant) {
    $game_participants[] = $participant;
  }

  // update every participants' target
  shuffle($game_participants);
  for ($i = 0; $i < count($game_participants); $i++) {
    $current = $game_participants[$i];
    $target = $game_participants[$i+1 == count($game_participants) ? 0 : $i+1];
    $GLOBALS['players']->update(
      array('_id' => $current['_id']),
      array('$set' => array('target_id' => $target['_id'])));
    $GLOBALS['players']->update(
      array('_id' => $target['_id']),
      array('$set' => array('killer_id' => $current['_id'])));
    br_call_phone($current['phone'], CALL_URL_FIRST_TARGET);
    br_message_phone($current['phone'], MSG_BEGIN_ANNOUNCE_TARGET,
		     $current['first_name'], $target['name']);
  }

  // update the game's status
  $GLOBALS['games']->update(
    array('_id' => $game['_id']),
    array('$set' => array(
      'status' => GAME_STATUS_ACTIVE,
      'startDate' => new MongoDate())));

  return '';
}

function br_kill_request($killer_id) {
  $killer = br_get_active_player($killer_id);

  if (!$killer) {
    return MSG_KILL_ERROR_NO_GAME;
  }

  $game = br_get_game($killer['game_id']);

  if ($game['status'] == GAME_STATUS_PENDING) {
    return MSG_KILL_ERROR_GAME_PENDING;
  }

  // request confirmation
  $target = br_get_player($killer['target_id']);
  $GLOBALS['players']->update(
    array('_id' => $target['_id']), 
    array('$set' => array('kill_request' => true)));
  br_message_phone($target['phone'], MSG_KILL_CONFIRM_REQUEST);
  br_message_phone($killer['phone'], MSG_KILL_CONFIRM_REQUESTED);

  return '';
}

function br_kill_accept($victim_id) {
  $victim = br_get_active_player($victim_id);
  $killer = br_get_player($victim['killer_id']);
  $target = br_get_player($victim['target_id']);

  if (!$victim['kill_request']) return 'You haven\'t been killed yet!';

  // victim
  $GLOBALS['players']->update(
    array('_id' => $victim['_id']),
    array(
      '$set' => array(
        'kill_request' => false,
        'killed_by' => $killer['_id'],
	'killed_on' => new MongoDate(),
        'status' => PLAYER_STATUS_DEAD
      ),
      '$unset' => array(
	'killer_id' => 1,
	'target_id' => 1
      ),
    )
  );

  // victim's target
  $GLOBALS['players']->update(
    array('_id' => $target['_id']),
    array(
      '$set' => array(
        'killer_id' => $victim['killer_id'],
      ),
      '$push' => array(
        'evaded' => $victim['_id']
      ),
      '$inc' => array( 
        'evades' => 1,
	'points' => 1
      ),
    )
  );

  // victim's killer
  $GLOBALS['players']->update(
    array('_id' => $killer['_id']),
    array(
      '$set' => array(
        'target_id' => $victim['target_id'],
      ),
      '$push' => array(
        'killed' => $victim['_id']
      ),
      '$inc' => array( 
        'kills' => 1,
	'points' => 1
      ),
    )
  );

  br_call_phone($victim['phone'], CALL_URL_ELIMINATED);
  br_message_phone($victim['phone'], MSG_CONFIRM_VICTIM_ACCEPTED);
  br_message_admin($killer['game_id'], '%s killed %s.',
		   $killer['name'], $victim['name']);

  if ($target['_id'] == $killer['_id']) {
    // the game is over

    $GLOBALS['games']->update(
      array('_id' => $killer['game_id']),
      array('$set' => array(
        'status' => GAME_STATUS_COMPLETE,
        'endDate' => new MongoDate())));
    $GLOBALS['players']->update(
      array('_id' => $killer['_id']),
      array('$set' => array('status' => PLAYER_STATUS_WINNER)));

    br_message_admin($killer['game_id'], MSG_CONFIRM_ANNOUNCE_WINNER, $killer['name']);
    br_message_game($killer['game_id'], MSG_CONFIRM_ANNOUNCE_WINNER, $killer['name']);
    //br_message_phone($killer['phone'], MSG_CONFIRM_ANNOUNCE_WINNER_TO_WINNER);
  } else { 
    // send the killer his new target

    br_call_phone($killer['phone'], CALL_URL_NEXT_TARGET);
    br_message_phone($killer['phone'], MSG_CONFIRM_NEXT_TARGET, $target['name']);
  }

  return '';
}

function br_kill_reject($victim_id) {
  $victim = br_get_active_player($victim_id);
  $killer = br_get_player($victim['killer_id']);

  if (!$victim['kill_request']) return 'You haven\'t been killed yet!';

  $GLOBALS['players']->update(
    array('_id' => $victim_id),
    array('$set' => array('kill_request' => false)));

  br_message_phone($killer['phone'], MSG_CONFIRM_KILLER_REJECTED);
  br_message_phone($victim['phone'], MSG_CONFIRM_VICTIM_REJECTED);
  br_message_admin($victim['game_id'], '%s rejected %s\'s kill request.',
		   $victim['name'], $killer['name']);

  return '';
}
