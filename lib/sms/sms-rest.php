<?php

	// include the PHP TwilioRest library
	require "../twilio-php/Services/Twilio.php";

	// set our AccountSid and AuthToken
	$AccountSid = "AC152c59215d81468b89c8384976f2c540";
	$AuthToken = "5cea9893f16c041e3ea66a6618af8267";

	// instantiate a new Twilio Rest Client
	$client = new Services_Twilio($AccountSid, $AuthToken);

	// make an associative array of people we know, indexed by phone number
	$people = array(
		"+13057736239"=>"Rafi",
	);

	// iterate over all our friends
	foreach ($people as $number => $name) {

		// Send a new outgoinging SMS by POSTing to the SMS resource */
		$sms = $client->account->sms_messages->create(
			"541-526-7609",
			$number,
			"Hey $name, Monkey Party at 6PM. Bring Bananas!"
		);

		echo "Sent message to $name";
    }
?>
