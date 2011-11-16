<?php

require_once dirname(__FILE__).'../lib/battle-royale/sms.php';

process_sms($_REQUEST['From'], $_REQUEST['Body']);

?>