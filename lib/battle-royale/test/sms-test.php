<?php

require_once(dirname(__FILE__).'/../sms.php');

$DEBUG = true;

// GAME 1
process_sms('0', 'create');
process_sms('0', 'Christopher');
process_sms('0', 'abc');
process_sms('0', 'join abc');
process_sms('1', 'join abc');
process_sms('1', 'Rafi');
process_sms('0', 'begin');
process_sms('1', 'kill');
process_sms('0', 'y');

// GAME 2
process_sms('0', 'create');
process_sms('0', 'tiger');
process_sms('0', 'join tiger');
process_sms('1', 'join tiger');

?>