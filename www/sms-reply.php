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
    
    
    // CREATE GAME
    if ($body == "CREATE") {
        // IF ENTRY EXISTS AND IS ACTIVE
        // ARE YOU SURE?
        
        // IF NO ENTRY FOR THAT PERSON
        // GET NAME
        
        $reply = "Welcome to Battle Royale! What is the name of your game? ";
        $reply .= "e.g. PrincetonStartupWeekend2011 or psw2011";
                
        $_SESSION['awaiting_game_name'] = 1;

	send_reply($reply);
    }
    
    $agn = $_SESSION['awaiting_game_name'];
    if ( strlen($agn) && $agn ) {
        $game_name = $body;
        $reply = "Your game has been created with name " . $game_name . ". ";
        $reply .= "Please tell players to text JOIN " . $game_name . " to enter game.";
        
        $_SESSION['awaiting_game_name'] = 0;

	send_reply($reply);
    }
    
    // JOIN GAME
    // START GAME
    // REPORT KILL
    // ANNOUNCE
    
    send_reply($name .= ", thanks for the message!");
    
?>

