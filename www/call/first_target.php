<?php
/**
 * first_target.php
 *
 * The call that tells the assassin who his first target is.
 */

/************
 * INCLUDES *
 ************/

require '../../lib/twilio-php/Services/Twilio.php';
require '../constants.php';
require '../database.php';

/************
 * RESPONSE *
 ************/

$participant = find_participant(array('phone' => $_REQUEST['To'], 'status' => PARTICIPANT_STATUS_ALIVE));
$target = find_participant(array('_id' => $participant['target_id']));
$response = new Services_Twilio_Twiml();
$response->pause(2);
$response->say(sprintf(CALL_RESPONSE_FIRST_TARGET, $participant['name'], $target['name']));
print $response;