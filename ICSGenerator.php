<?php

require_once "vendor/autoload.php";

class ICSGenerator {
	public $calendar;

	public function __construct($name) {
		$this->calendar = new \Eluceo\iCal\Component\Calendar($name);
	}

	public function fillWithESIEvents($esical, $events) {
		foreach ($events as $e) {
			$vevent = new \Eluceo\iCal\Component\Event();
			$start = new DateTime($e->event_date);
			$vevent
				->setDescription($e->title)
				->setDtStart($start)
				->setSummary($e->title)
				->setUniqueId($e->event_id);

			if ($start->getTimestamp() > time() - 86400) {
				$e = $esical->getEvent($e);

				$vevent
					->setDescription($e->text)
					->setDuration(new DateInterval("PT" . $e->duration . "M"))
					->setOrganizer(
						new \Eluceo\iCal\Property\Event\Organizer(
							$e->owner_name
						)
					);
			}

			$this->calendar->addComponent($vevent);
		}
	}
}

?>
