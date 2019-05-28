<?php
include "vendor/autoload.php";

// ======================================== MAKE SURE WE HAVE OUR INPUTS

if(!isset($_GET['refresh_token']) || !isset($_GET['character_id'])) {
	return;
}

// ======================================== GET CALENDAR FROM API

$client_id = "XXX";
$secret_key = "XXX";

$refresh_token = $_GET['refresh_token'];
$character_id = $_GET['character_id'];

$authentication = new Seat\Eseye\Containers\EsiAuthentication([
	"client_id" => $client_id,
	"secret" => $secret_key,
	"refresh_token" => $refresh_token,
]);

$esi = new Seat\Eseye\Eseye($authentication);

$esi->setQueryString([
	"from_event" => 1953000,
]);

$calendar = $esi->invoke("get", "/characters/{character_id}/calendar/", [
	"character_id" => $character_id,
]);

// ======================================== SET UP ICS FILE

$vcalendar = new Eluceo\iCal\Component\Calendar(
	"ESI Calendar For " . $character_id
);

// ======================================== FILL ICS FILE

$count = 0;
foreach ($calendar as $event) {
	$vevent = new Eluceo\iCal\Component\Event();
	$start = new DateTime($event->event_date);
	$vevent
		->setDescription($event->title)
		->setDtStart($start)
		->setSummary($event->title)
		->setUniqueId($event->event_id);

	if ($count < 10) {
		$event = $esi->invoke(
			"get",
			"/characters/{character_id}/calendar/{event_id}/",
			[
				"character_id" => $character_id,
				"event_id" => $event->event_id,
			]
		);

		$vevent
			->setDescription($event->text)
			->setDuration(new DateInterval("PT" . $event->duration . "M"))
			->setOrganizer(
				new Eluceo\iCal\Property\Event\Organizer($event->owner_name)
			);
	}

	$vcalendar->addComponent($vevent);
}

// ======================================== OUTPUT ICS FILE

header('Content-Type: text/calendar; charset=utf-8');
header('Content-Disposition: attachment; filename="cal.ics"');
echo $vcalendar->render();


?>
