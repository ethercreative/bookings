<?php
/**
 * Bookings plugin for Craft CMS 3.x
 *
 * @link      https://ethercreative.co.uk
 * @copyright Copyright (c) Ether Creative
 */

namespace ether\bookings\services;

use craft\base\Component;
use craft\helpers\Db;
use ether\bookings\models\Event;
use ether\bookings\records\EventRecord;


/**
 * Class EventsService
 *
 * @author  Ether Creative
 * @package ether\bookings\services
 * @since   1.0.0
 */
class EventsService extends Component
{

	/**
	 * Get an event by its ID
	 *
	 * @param $eventId
	 *
	 * @return Event|null
	 */
	public function getEventById ($eventId)
	{
		$record = EventRecord::findOne([
			'id' => $eventId,
		]);

		if (!$record)
			return null;

		return Event::fromRecord($record);
	}

	/**
	 * Refreshes the cached next available slot DB column
	 *
	 * @param bool $includeNull
	 */
	public function refreshNextAvailableSlot (bool $includeNull = false)
	{
		$now = Db::prepareDateForDb(new \DateTime('now'));

		$where = [
			'or',
			['<', 'nextSlot', $now],
		];

		if ($includeNull)
		{
			$where[] = [
				'nextSlot' => null,
			];
		}

		$outOfDateEvents = EventRecord::find()->where([
			'and',
			['>=', 'lastSlot', $now],
			$where,
		])->all();

		if (empty($outOfDateEvents))
			return;

		/** @var EventRecord $record */
		foreach ($outOfDateEvents as $record)
		{
			$event = Event::fromRecord($record);

			$record->nextSlot = $event->getNextAvailableSlot();
			$record->save();
		}
	}

}