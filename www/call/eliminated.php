<?php
/**
 * eliminated.php
 *
 * The call that tells the assassin that he has been eliminated
 */

/************
 * INCLUDES *
 ************/

require dirname(__FILE__).'/../../lib/twilio-php/Services/Twilio.php';
require dirname(__FILE__).'/../../lib/battle-royale/constants.php';
//require dirname(__FILE__).'/../../lib/battle-royale/database.php';

/************
 * RESPONSE *
 ************/

$response = new Services_Twilio_Twiml();
$response->play(SOUND_URL_GUNSHOT);
$response->say(sprintf(CALL_RESPONSE_ELIMINATED));
// TODO: tell the player his stats from the phone
print $response;