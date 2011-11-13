<?php

    session_start();

    function send_reply($str) {
        header("content-type: text/xml");
        echo "<?xml version=\"1.0\" encoding=\"UTF-8\"" . chr(63) . ">\n";
        echo "<Response>\n<Sms>";
        echo $str;
        echo "\n</Sms>\n</Response>\n";
        exit();
    }
    
    $keywords = array("CREATE", "JOIN", "START", "KILL", "YES", "NO");
    
    $from = $_REQUEST['From'];
    $body = $_REQUEST['Body'];
    
    //$from = "+13057736239";
    //$body = "CREATE";
    
    /* * * * * * * * * *
     *                 *
     *   CREATE GAME   *
     *                 *
     * * * * * * * * * */
    if ( substr($body, 0, 6) == "CREATE") {
        // IF ENTRY EXISTS AND IS ACTIVE
        // ARE YOU SURE?
        
        // IF NO ENTRY FOR THAT PERSON
        // GET NAME
        
        $reply = "Welcome to Battle Royale! What is your name?";
        $_SESSION['awaiting_person_name'] = true;
        
        send_reply($reply);
    }
    
    
    $apn = $_SESSION['awaiting_person_name'];
    if ( $apn ) {
        $person_name = $body;
        
        $reply = "Welcome, " . $person_name . "! ";
        $reply .= "What is the name of your game? ";
        $reply .= "e.g. PtonStartupWeekend2011 or psw2011";
        
        $_SESSION['awaiting_person_name'] = false;
        $_SESSION['awaiting_game_name'] = true;
        
        send_reply($reply);
    }
    
    $agn = $_SESSION['awaiting_game_name'];
    if ( strlen($agn) && $agn ) {
        $game_name = $body;
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

