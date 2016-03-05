# nagios-osx-desktop-notifications

Checks Nagios/Icinga status and sends Mac OS X desktop notifications

## Installation

`npm install`

## Configuration

```
$servers = array(
	'YourServerName' => array(
		'url' => 'https://icinga.yourdomain.local/icinga/status.cgi',
		// Basic Authentication
		'user' => 'bob',
		'pass' => 'runs',
	),
);

$checkInterval = 60;
```

## Run

```
node node_modules/node-osx-notifier/lib/node-osx-notifier.js &
php run.php
```