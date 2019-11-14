<?php

require_once "vendor/autoload.php";

class ESICalendar {
	private $esi;
	private $auth;

	private $app_id;
	private $app_secret;
	private $char_refresh;
	private $char_id;

	public function __construct($app_id, $app_secret, $char_refresh, $char_id) {
		$this->char_id = $char_id;

		$this->auth = new \Seat\Eseye\Containers\EsiAuthentication([
			"client_id" => $app_id,
			"secret" => $app_secret,
			"refresh_token" => $char_refresh,
		]);

		$this->esi = new \Seat\Eseye\Eseye($this->auth);
	}

	public function getEvents($from) {
		$count = 1;
		$events = [];

		while ($count > 0) {
			$count = 0;

			$result = $this->esi
				->setQueryString([
					"from_event" => $from,
				])
				->invoke("get", "/characters/{character_id}/calendar/", [
					"character_id" => $this->char_id,
				]);

			if($result->getErrorCode() !== 200) {
				return [];
			}

			foreach ($result as $v) {
				$events[$v->event_id] = $v;
				$count++;
			}

			$last = end($events);
			if ($last) {
				$from = $last->event_id;
			}
		}

		return $events;
	}

	public function getEvent($event) {
		$event = $this->esi->invoke(
			"get",
			"/characters/{character_id}/calendar/{event_id}/",
			[
				"character_id" => $this->char_id,
				"event_id" => $event->event_id,
			]
		);

		return $event;
	}
}

?>
