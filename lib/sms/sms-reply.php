<?php

	// make an associative array of senders we know, indexed by phone number
	$people = array(
		"+14158675309"=>"Curious George",
		"+14158675310"=>"Boots",
		"+14158675311"=>"Virgil",
		"+13057736239"=>"Rafi",
		"+16094238157"=>"Emily",
	);

	// if the sender is known, then greet them by name
	// otherwise, consider them just another monkey
	if(!$name = $people[$_REQUEST['From']])
		$name = "Monkey";

	// now greet the sender
	header("content-type: text/xml");
	echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
?>
<Response>
	<Sms><?php echo $name ?>, thanks for the message!</Sms>
</Response>
