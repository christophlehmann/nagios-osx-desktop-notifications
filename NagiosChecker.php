<?php

class NagiosChecker {

	var $servers;

	function __construct($servers) {
		$this->servers = $servers;
	}

	function check() {
		foreach ($this->servers as $server => $config) {
			$status = $this->getStatus($config);
			if (!is_array($status)) {
				echo $server . ": No status received" . PHP_EOL;
				continue;
			} else {
				echo $server . ": Received status" . PHP_EOL;
			}
			$services = $this->getServiceListFromStatus($status);
			if (!is_array($services) || count($services) == 0) {
				echo $server . ": no services found" . PHP_EOL;
				continue;
			} else {
				echo $server . ": Found " . count($services) . ' services' . PHP_EOL;
			}

			if (empty($this->servers[$server]['status'])) {
				$this->servers[$server]['status'] = $services;
				continue;
			}

			foreach ($services as $serviceName => $serviceData) {
				if (isset($this->servers[$server]['status'][$serviceName]['status']) &&
					$serviceData['status'] !== $this->servers[$server]['status'][$serviceName]['status']
				) {
					$this->report($serviceData, $server);
				}
			}

			$this->servers[$server]['status'] = $services;
		}
	}

	function getStatus($server) {
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $server['url'] . '?style=servicedetail&jsonoutput');
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
		curl_setopt($ch, CURLOPT_USERPWD, $server['user'] . ":" . $server['pass']);
		$result = curl_exec($ch);
		curl_close($ch);

		if (isset($result) && !empty($result)) {
			$json = json_decode($result, TRUE);
			return $json['status'];
		}
	}

	function getServiceListFromStatus($status) {
		$retval = array();

		if (is_array($status['service_status'])) {
			foreach ($status['service_status'] as $service) {
				$retval[$service['host'] . '-' . $service['service']] = $service;
			}
		}

		return $retval;
	}

	function report($service, $server) {
		if (empty($service['in_scheduled_downtime']) && $service['notifications_enabled'] == 1) {
			echo $server . ': ' . $service['host'] . '/' . $service['service'] . ' ' . $service['status'] . PHP_EOL;
		}

		$service['status'] === 'OK' ? $messageType = 'pass' : $messageType = 'fail';

		$data = array(
			'title' => $service['status'] . ': ' . $service['service'] . ' on ' . $service['host'],
			'subtitle' => 'Reported by ' . $server,
			'message' => 'Output: ' . $service['status_information'],
		);
		$data_string = json_encode($data);

		$ch = curl_init('http://localhost:1337/' . $messageType);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
		curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array(
				'Content-Type: application/json',
				'Content-Length: ' . strlen($data_string))
		);

		$result = curl_exec($ch);
		curl_close($ch);
		echo $result . PHP_EOL;
	}
}