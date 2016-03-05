<?php

$servers = array(
	'YourServerName' => array(
		'url' => 'https://icinga.yourdomain.local/icinga/status.cgi',
		// Basic Authentication
		'user' => 'bob',
		'pass' => 'runs',
	),
);

$checkInterval = 60; // Seconds