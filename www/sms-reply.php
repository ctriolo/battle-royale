<?php

    $keywords = "CREATE JOIN START KILL";    
    $from = $_REQUEST['From'];
    $body = $_REQUEST['Body'];

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
    if ( substr($body, 0, 6) == "CREATE") 
    {
        if ($_SESSION['awaiting_person_name'])
            send_reply("CREATE not a valid name. Try again.");
        if ($_SESSION['awaiting_game_name'])
            send_reply("CREATE not a valid name. Try again.");
        
        $_SESSION['awaiting_create_game'] = true;

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
    if ( $_SESSION['awaiting_create_game'] ) 
    {        
        $reply  = "Welcome, " . $person_name . "! ";
        $reply .= "What is the name of your game? ";
        $reply .= "e.g. PtonStartupWeekend2011 or psw2011";
        
        $_SESSION['awaiting_create_game'] = false;
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
	$game = array('name' => $game_name, 'phone' => $from);
        $games->insert($game);

        $reply = "Your game has been created with name " . $game_name . ". ";
        $reply .= "Please tell players to text JOIN " . $game_name . " to enter game.";
        
        $_SESSION['awaiting_game_name'] = false;

        send_reply($reply);
    }
    
    // JOIN GAME
    // START GAME
    // REPORT KILL
    // ANNOUNCE
    
    send_reply("That's nice...");
    
?>

