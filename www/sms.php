<?php

require_once '../lib/battle-royale/sms.php';

process_sms($_REQUEST['From'], $_REQUEST['Body']);

?>