<?php

require_once "vendor/autoload.php";

require_once "ESICalendar.php";
require_once "ICSGenerator.php";

try {
	// ======================================== MAKE SURE WE HAVE OUR INPUTS

	if (!isset($_GET['refresh_token']) || !isset($_GET['character_id'])) {
		return;
	}

	if (!isset($_SERVER['client_id']) || !isset($_SERVER['secret_key'])) {
		return;
	}

	$client_id = $_SERVER['client_id'];
	$secret_key = $_SERVER['secret_key'];

	$refresh_token = $_GET['refresh_token'];
	$character_id = $_GET['character_id'];

	// ======================================== GET CALENDAR FROM API

	$esical = new \ESICalendar(
		$client_id,
		$secret_key,
		$refresh_token,
		$character_id
	);

	$events = $esical->getEvents(195300000);

	if (!count($events)) {
		throw new \Exception("No events found.");
	}

	// ======================================== GENERATE ICS FILE

	$ics = new \ICSGenerator("ESI Calendar For " . $character_id);
	$ics->fillWithESIEvents($esical, $events);

	header('Content-Type: text/calendar; charset=utf-8');
	header('Content-Disposition: attachment; filename="cal.ics"');
	echo $ics->calendar->render();
} catch (\Seat\Eseye\Exceptions\RequestFailedException $e) {
	echo "[ERROR] " . $e->getCode() . "\n";
	echo "[ERROR] " . $e->getMessage() . "\n";

	echo "[ERROR] " . $e->getEsiResponse()->getErrorCode() . "\n";
	echo "[ERROR] " . $e->getEsiResponse()->error() . "\n";

	print_r($e);
} catch (\Exception $e) {
	echo "[ERROR] " . $e->getMessage() . "\n";
	print_r($e);
}

?>
