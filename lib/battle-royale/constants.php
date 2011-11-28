<?php
/**
 * constants.php
 *
 * Site-wide constants.
 */

// Credentials
require dirname(__FILE__).'/credentials.php';

// SMS Keywords
define('SMS_KEYWORD_CREATE', 'create');
define('SMS_KEYWORD_JOIN', 'join');
define('SMS_KEYWORD_BEGIN', 'begin');
define('SMS_KEYWORD_KILL', 'kill');
define('SMS_KEYWORD_CANCEL', 'cancel');
define('SMS_KEYWORD_HELP', 'help');
define('SMS_KEYWORDS',
       'SMS_KEYWORD_CREATE'.
       'SMS_KEYWORD_JOIN'.
       'SMS_KEYWORD_BEGIN',
       'SMS_KEYWORD_KILL'.
       'SMS_KEYWORD_CANCEL'.
       'SMS_KEYWORD_HELP');

// Game Status
define('GAME_STATUS_PENDING', 'pending');
define('GAME_STATUS_ACTIVE', 'active');
define('GAME_STATUS_COMPLETE', 'complete');

// Participant Status
define('PLAYER_STATUS_ALIVE', 'alive');
define('PLAYER_STATUS_DEAD', 'dead');
define('PLAYER_STATUS_WINNER', 'winner');

// SMS States
define('SMS_STATE_NULL', 0);
define('SMS_STATE_CREATE', 1);
define('SMS_STATE_JOIN', 2);
define('SMS_STATE_BEGIN', 3);
define('SMS_STATE_KILL', 4);
define('SMS_STATE_NAME', 5);

// Create States
define('CREATE_STATE_INIT', 0);
define('CREATE_STATE_RECEIVE', 1);

// Name States
define('NAME_STATE_INIT', 0);
define('NAME_STATE_RECEIVE', 1);

// SMS Responses
//   name
define('MSG_NAME_REQUEST',
       'Welcome to Battle Royale! What is your name?');
define('MSG_NAME_ERROR_INVALID_NAME',
       '%s is not a valid name.');
//   create
define('MSG_CREATE_TITLE_REQUEST',
       'What is the title of your game? (e.g. PtonStartupWeekend2011 or psw2011)');
define('MSG_CREATE_GAME_CREATED',
       '%s has been created. Please tell players to text \'join %s\' to %s to enter the game. When everyone has joined, text \'begin\' to begin.');
define('MSG_CREATE_ERROR_ADMIN',
       'You cannot create a new game if you are the active admin of another.');
define('MSG_CREATE_ERROR_ACTIVE',
       'You cannot create a new game if you are in the middle of another game.');
define('MSG_CREATE_ERROR_INVALID_TITLE',
       '%s is not a valid title. Please try again.');
define('MSG_CREATE_ERROR_TITLE_IN_USE',
       'The title \'%s\' is already in use. Please try again.');
//   join
define('MSG_JOIN_SUCCESSFUL',
       'Welcome, %s! You are now in %s. You will receive a text once the game begins.');
define('MSG_JOIN_ACTIVITY',
       '%s has joined %s.');
define('MSG_JOIN_ERROR_NO_CODE',
       'JOIN must specify which game to join, (e.g. JOIN psw2011) please try again.');
define('MSG_JOIN_ERROR_NO_GAME',
       'Sorry, this game doesn\'t exist. Are you sure you spelt it right?');
define('MSG_JOIN_ERROR_ACTIVE',
       'You cannot join a new game if you are in the middle of another game.');
define('MSG_JOIN_ERROR_ALREADY_BEGUN',
       'Sorry, game has already begun. Try again next time.');
//   begin
define('MSG_BEGIN_NOT_ADMIN',
       'Sorry, you are not the administrator of a game.');
define('MSG_BEGIN_ALREADY_BEGUN',
       'Sorry, the game has already begun.');
define('MSG_BEGIN_ANNOUNCE_TARGET',
       'Hello, %s. Your mission is to eliminate %s. If you eliminate them, reply with KILL. Good luck.');
//   kill
define('MSG_KILL_CONFIRM_REQUEST',
       'Your assassin has reported that you have been eliminated. Is this true? (y/n)');
define('MSG_KILL_CONFIRM_REQUESTED',
       'Confirmation requested.');
define('MSG_KILL_ERROR_NO_GAME',
       'You are not in a game');
define('MSG_KILL_ERROR_GAME_PENDING',
       'Calm down. The game hasn\'t even begun yet.');
define('MSG_KILL_ERROR_DEAD',
       'Sorry, you are already dead. Stay tuned for the winner.');
//   confirm
define('MSG_CONFIRM_VICTIM_ACCEPTED',
       'Thank you for your confirmation. You have been officially eliminated.');
define('MSG_CONFIRM_ANNOUNCE_WINNER',
       'The assassins have all perished but one. The master assassin is %s.'); 
define('MSG_CONFIRM_ANNOUNCE_WINNER_TO_WINNER',
       'You are the last living assassin. We hereby declare you winner. CONGRATS!');
define('MSG_CONFIRM_NEXT_TARGET',
       'Mission accomplished! Your next target is %s. Reply with KILL if you eliminate them.');
define('MSG_CONFIRM_VICTIM_REJECTED',
       'Thank you. The administrator will be notified.');
define('MSG_CONFIRM_KILLER_REJECTED',
       'Confirmation rejected. The Administrator will be notified.');
//   cancel
define('MSG_CANCEL',
       'Operation canceled, you have been returned to the main flow. Text HELP to get a list of commands.');
//   help
define('MSG_HELP',
	'Possible commands: create, join, begin, kill, help.');
//   derp
define('MSG_DERP',
       'I don\'t understand, please text HELP to retrieve a list of commands');

// Call Respones
define('CALL_FIRST_TARGET',
       'Hello %s. Your mission is to eliminate %s. If you succeed with your mission, text the word \'kill\' to this number. Goodbye.');
define('CALL_NEXT_TARGET',
       'Impressive. Your next target is %s. Upon elimination text the word \'kill\' to this number.');
define('CALL_ELIMINATED',
       'You have been eliminated. Better luck next time.');

// Sound URLs
define('SOUND_URL_GUNSHOT', 'http://battleroyale.mobi/sound/gunshot.mp3');

// Call URLs
define('CALL_URL_FIRST_TARGET', 'http://battleroyale.mobi/call/first_target.php');
define('CALL_URL_NEXT_TARGET', 'http://battleroyale.mobi/call/next_target.php');
define('CALL_URL_ELIMINATED', 'http://battleroyale.mobi/call/eliminated.php');
?>