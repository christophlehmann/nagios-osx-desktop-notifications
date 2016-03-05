<?php

require('Configuration.php');
require('NagiosChecker.php');

$nagiosChecker = new \NagiosChecker($servers);

while (1) {
	$nagiosChecker->check();
	sleep($checkInterval);
}