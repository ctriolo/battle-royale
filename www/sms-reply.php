<?php



    $keywords = " CREATE JOIN START KILL";    
    $from = $_REQUEST['From'];
    $body = $_REQUEST['Body'];
    $body = trim($body);
    
    $tokens = $tokens = preg_split("/\s+/", $body);
    
    
    
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
        // TODO_CHRIS
        // put_person($person_name);
        
        $_SESSION['awaiting_person_name'] = false;
    }
    
    /* * * * * * * * * * * *
     *                     *
     *   RECEIVE CREATE    *
     *                     *
     * * * * * * * * * * * */
    if ( $tokens[0] == "CREATE") 
    {
        $_SESSION['create_dialog'] = true;

        // TODO_CHRIS::
        // $person = get_person($from);
        // if (!person)
        request_name(); // if no entry, get person's name
        
        // TODO_CHRIS::
        // $person_name = $person.name;
        
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
        // TODO_CHRIS
        // put_participant($person, ADMIN)
        
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
        // TODO_CHRIS
        // put_game($game_name);
        
        $reply = "Your game has been created with name " . $game_name . ". ";
        $reply .= "Please tell players to text JOIN " . $game_name . " to enter game.";
        
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
        if ($_SESSION['awaiting_person_name'])
            send_reply("JOIN not a valid name. Try again.");
        if ($_SESSION['awaiting_game_name'])
            send_reply("JOIN not a valid name. Try again.");
                
        if (count($tokens) != 2)
            ; // ERROR
        
        $_SESSION['join_dialog'] = true;
        $_SESSION['game_name'] = $tokens[1];
        
        // TODO_CHRIS::
        // $person = get_person($from);
        // if (!person)
        request_name(); // if no entry, get person's name
        
        // TODO_CHRIS::
        // $person_name = $person.name;
        
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

        // TODO_CHRIS
        // $game = get_game($game_name);
        // put_participant($person, PLAYER, $game);
        
        $reply  = "Welcome, " . $person_name . "! ";
        $reply .= "You are now in game, " . $game_name . ". ";
        $reply .= "You will receive a text once the game begins.";
        
        $_SESSION['join_dialog'] = false;
        
        send_reply($reply);
    }
    
    // include the PHP TwilioRest library
	require "../lib/twilio-php/Services/Twilio.php";
    // set our AccountSid and AuthToken
	$AccountSid = "ACXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX";
	$AuthToken = "YYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYY";
    // instantiate a new Twilio Rest Client
	$client = new Services_Twilio($AccountSid, $AuthToken);
    
    $tokens[0] = "START";
    if ( $tokens[0] == "START" )
    {
        // TODO_CHRIS:
        // get the admin; make sure $from is the $admin
        
        $players = array("+13057736239" => "Rafi",
                         //"+16094238157" => "Emily",
                         //"+16313552173" => "Chris",
                         //"+16097513474" => "Jess",
                         );
        
        $numbers; $names;
        
        $num = count($players);
        $i = 0;
        foreach ($players as $number => $name) {
            $numbers[$i] = $number;
            $names[$i] = $name;
            $i++;
        }
        
        $index = range(0, $num-1);
        shuffle($index);
        
        for ($i = 0; $i < $num; $i++) {
            $j = $index[$i];
            
            $name = $names[$j];
            $number = $numbers[$j];
            
            $k = $index[ $i+1 == $num ? 0 : $i+1 ];
            $target = $names[$k];
            
            $msg =  "Hello, " . $name . ". Your target is " . $target . ". ";
            $msg .= "If you assassinate them, reply with KILL. Happy hunting";
            $sms = $client->account->sms_messages->create(
                                                          "YYY-YYY-YYYY",
                                                          $number,
                                                          "Hey $name, Monkey Party at 6PM. Bring Bananas!"
                                                          );
            
            echo "$name of $number targeting $target\n";
        }
        
        
        exit();
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

