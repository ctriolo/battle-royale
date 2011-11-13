<?php


$keywords = " CREATE JOIN BEGIN KILL";

// Post values
$to = $_REQUEST['To'];
$from = $_REQUEST['From'];
$body = $_REQUEST['Body'];
$body = trim($body);

$tokens = $tokens = preg_split("/\s+/", $body);

// Connect to Mongo, Set up Collections
$m = new Mongo();
$db = $m->battle_royale;
$people = $db->people;
$participants= $db->participants;
$games = $db->games;

session_start();

function send_reply($str) {
  header("content-type: text/xml");
  echo "<?xml version=\"1.0\" encoding=\"UTF-8\"" . chr(63) . ">\n";
        echo "<Response>\n<Sms>";
        echo $str;
        echo "\n</Sms>\n</Response>\n";
        exit();
}


    
$person_name = "";
    
$awaiting = false;
if ($_SESSION['awaiting_person_name'] || $_SESSION['awaiting_game_name'])
  $awaiting = true;
if ( $awaiting && strpos($keywords, $tokens[0]) )
  send_reply($tokens[0] . " not a valid name");
        

    
//$from = "+13057736239";
//$body = "CREATE";

/* * * * * * * * * * *
 *                   *
 *   REQUEST NAME    *
 *                   *
 * * * * * * * * * * */
function request_name() 
{
  $reply = "Welcome to Battle Royale! What is your name?";
  $_SESSION['awaiting_person_name'] = true;
  
  send_reply($reply);
}

/* * * * * * * * * * *
 *                   *
 *   RECEIVE NAME    *
 *                   *
 * * * * * * * * * * */
if ( $_SESSION['awaiting_person_name'] ) 
  {
    $person_name = $body;
    $person = array('name' => $person_name, 'phone' => $from);
    $people->insert($person);
    
    $_SESSION['awaiting_person_name'] = false;
  }

/* * * * * * * * * * * *
 *                     *
 *   RECEIVE CREATE    *
 *                     *
 * * * * * * * * * * * */
if ( $tokens[0] == "CREATE" ) 
  {
    // HANDLE ERROR
    // if person exists as active participant, ask if sure? 
    // (can only participate in one game)
    
    $_SESSION['create_dialog'] = true;
    
    $person = $people->findOne(array('phone' => $from));
    if (!$person) request_name(); // if no entry, get person's name
    
    $person_name = $person['name'];
    
    // HANDLE ERROR
    // if ( $person.status == "ACTIVE" )
    // send_reply("are you sure?");
  }

/* * * * * * * * * * * * *
 *                       *
 *   REQUEST GAME TITLE  *
 *                       *
 * * * * * * * * * * * * */
if ( $_SESSION['create_dialog'] ) 
  {
    $reply  = "Welcome, " . $person_name . "! ";
    $reply .= "What is the name of your game? ";
    $reply .= "e.g. PtonStartupWeekend2011 or psw2011";
    
    $_SESSION['create_dialog'] = false;
    $_SESSION['awaiting_game_name'] = true;
    
    send_reply($reply);
  }

/* * * * * * * * * * * * *
 *                       *
 *   RECEIVE GAME TITLE  *
 *                       *
 * * * * * * * * * * * * */
if ( $_SESSION['awaiting_game_name'] ) 
  {
    $game_name = $body;
    $game = array('title' => $game_name, 
		  'admin' => $person_name, 
		  'phone' => $from, 
		  'status' => 'PENDING');
    $games->insert($game);
    
    $reply = "Your game has been created with name " . $game_name . ". ";
    $reply .= "Please tell players to text JOIN " . $game_name . " to $to to enter game.";
    $reply .= "When you are ready to begin, text BEGIN.";
    
    $person = $people->findOne(array('phone' => $from));
    $person_name = $person['name'];

    $_SESSION['awaiting_game_name'] = false;
    
    send_reply($reply);
  }
    
/* * * * * * * * * * *
 *                   *
 *   RECEIVE JOIN    *
 *                   *
 * * * * * * * * * * */
if ( $tokens[0] == "JOIN") 
  {
    $participant = $participants->findOne( array('phone' => $from) );
    if ($participant)
      send_reply("Sorry, you have already joined a game. Please wait until your admin begins the game.");

    if (count($tokens) < 2)
      send_reply('JOIN must specify which game to join, e.g. JOIN psw2011. Please try again.'); // ERROR
    
    $_SESSION['join_dialog'] = true;
    $_SESSION['game_name'] = $tokens[1];
    
    // TODO_CHRIS::
    $person = $people->findOne(array('phone' => $from));
    if (!$person) request_name(); // if no entry, get person's name
    
    $person_name = $person['name'];
    
    // if ( $person.status == "ACTIVE" )
    // send_reply("are you sure?");
  }

/* * * * * * * * * * * * *
 *                       *
 *   REQUEST GAME TITLE  *
 *                       *
 * * * * * * * * * * * * */
if ( $_SESSION['join_dialog'] ) 
  {
    $game_name = $_SESSION['game_name'];
    
    $person =  $people->findOne(array('phone' => $from));
    $game = $games->findOne(array('title' => $game_name));
    if ($game['status'] == 'ACTIVE')
      send_reply('Sorry, game has already begun. Try again next time.');
    $participants->insert(array('name' => $person['name'], 
				'phone' => $person['phone'], 
				'gameID' => $game['_id'], 
				'status' => 'ALIVE',
				'kills' => 0,
				'escapes' => 0));
    
    $reply  = "Welcome, " . $person_name . "! ";
    $reply .= "You are now in game, " . $game_name . ". ";
    $reply .= "You will receive a text once the game begins.";
    
    $_SESSION['join_dialog'] = false;
    
    send_reply($reply);
  }

// include the PHP TwilioRest library
require "../lib/twilio-php/Services/Twilio.php";
// set our AccountSid and AuthToken
$AccountSid = "AC152c59215d81468b89c8384976f2c540";
$AuthToken = "5cea9893f16c041e3ea66a6618af8267";
// instantiate a new Twilio Rest Client
$client = new Services_Twilio($AccountSid, $AuthToken);


if ( $tokens[0] == "BEGIN" )
  {
    // TODO_CHRIS:
    // get the admin; make sure $from is the $admin
    
    $game = $games->findOne( array('phone' => $from));
    if ($game['status'] == 'ACTIVE')
      send_reply('Sorry, game already begun.');
    $cursor = $participants->find( array('gameID' => $game['_id']));
    /*
    $players = array("+13057736239" => "Rafi",
		     "+16094238157" => "Emily",
		     "+16313552173" => "Chris",
		     "+16097513474" => "Jess",
		     );
    */

    $numbers; $names;
    
    $i = 0;
    foreach ($cursor as $participant) 
      {
	//$game_participants[$i] = $participant;
	$numbers[$i] = $participant['phone'];
	$names[$i] = $participant['name'];
	$i++;
      }
    $num = $i;

    $index = range(0, $num-1);
    shuffle($index);
    
    for ($i = 0; $i < $num; $i++) 
      {
	$j = $index[$i];
	
	$name = $names[$j];
	$number = $numbers[$j];
	
	$k = $index[ $i+1 == $num ? 0 : $i+1 ];
	$target_name = $names[$k];
	$target_phone = $numbers[$k];

	$participants->update(array('phone' => $number), 
			      array('$set' => array('target_name' => $target_name,
						    'target_phone' => $target_phone) ));
	
	if (!$to)
	  $to = "+15415267609";
	
	$msg =  "Hello, " . $name . ". Your target is " . $target_name . ". ";
	$msg .= "If you assassinate them, reply with KILL. Happy hunting!";
	$sms = $client->account->sms_messages->create($to,
						      $number,
						      $msg);
	
	echo "$name of $number targeting $target\n";
      }
    
    $games->update(array('phone' => $from),
		   array('$set' => array('status' => 'ACTIVE') ));
    
    exit();
  }

if( $tokens[0] == "KILL" )
  {
    $assassin = $participants->findOne(array('phone' => $from));
    if ($assassin['status'] == 'DEAD')
      send_reply('Sorry, you are already dead. Stay tuned for the winner.');
    
    $target = $participants->findOne( array('phone' => $assassin['target_phone']) );
    //send_reply("x- " . $victim['status']);

    // DEADen the target. Tell them this.
    $participants->update(array('phone' => $target['phone']),
			  array('$set' => array('status' => 'DEAD')));
    $target = $participants->findOne( array('phone' => $assassin['target_phone']) );
    $sms = $client->account->sms_messages->create($to,
						  $target['phone'],
						  "You have been assassinated! " . 
						  "Stay tuned for the winner.");

    if ( $assassin['phone'] == $target['target_phone'] )
      {
	$msg = "The assassins have all perished but one. The master assassin is " . $assassin['name'] . "."; 

	$game = $games->findOne( array('_id' => $assassin['gameID']) );
	$admin = $game['phone'];
	$sms = $client->account->sms_messages->create($to,
						      $admin,
						      $msg);
	
	send_reply("You are the last living assassin. We hereby declare you winner. CONGRATS!");
      }
      
    $next_tn = $target['target_name'];
    $next_tp = $target['target_phone'];
    $participants->update(array('phone' => $from),
			  array('$set' => array('target_name'  => $next_tn, 
						'target_phone' => $next_tp) ));

    send_reply("Mission accomplished! Your next target is " . $next_tn . ".");
    // DEADen the target. Tell them this.
    // Congrats the winner. Give next target. Assign.
    // If target, is self, announce winner.
    // Give next target.
  }

// if START
// if not admin, invalid command
// get list of numbers
// randomize, assign targets
// send out message

// JOIN GAME
// START GAME
// REPORT KILL
// ANNOUNCE

send_reply("That's nice...");

?>

