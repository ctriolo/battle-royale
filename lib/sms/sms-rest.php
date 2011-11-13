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
		"+14158675309"=>"Curious George",
		"+14158675310"=>"Boots",
		"+14158675311"=>"Virgil",
	);

	// iterate over all our friends
	foreach ($people as $number => $name) {

		// Send a new outgoinging SMS by POSTing to the SMS resource */
		$sms = $client->account->sms_messages->create(
			"YYY-YYY-YYYY",
			$number,
			"Hey $name, Monkey Party at 6PM. Bring Bananas!"
		);

		echo "Sent message to $name";
    }
?>
