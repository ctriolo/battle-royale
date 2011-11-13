<?php

	// include the PHP TwilioRest library
	require "../twilio-php/Services/Twilio.php";

	// set our AccountSid and AuthToken
	$AccountSid = "ACXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX";
	$AuthToken = "YYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYY";

	// instantiate a new Twilio Rest Client
	$client = new Services_Twilio($AccountSid, $AuthToken);

	// make an associative array of people we know, indexed by phone number
	$people = array(
		"+13057736239"=>"Curious George",
	);

	// iterate over all our friends
	foreach ($people as $number => $name) {

		// Send a new outgoinging SMS by POSTing to the SMS resource */
		$sms = $client->account->sms_messages->create(
			"305-773-6239",
			$number,
			"Hey $name, Monkey Party at 6PM. Bring Bananas!"
		);

		echo "Sent message to $name";
    }
?>
