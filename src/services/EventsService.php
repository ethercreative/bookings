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
		echo '├ Starting Next Available Refresh' . PHP_EOL;

		$now = Db::prepareDateForDb(new \DateTime('now'));
		$table = EventRecord::$tableName;

		$where = [
			'or',
			['<', $table . '.[[nextSlot]]', $now],
		];

		if ($includeNull)
		{
			$where[] = [
				"$table.[[nextSlot]]" => null,
			];
		}

		echo '├ Ready to get events' . PHP_EOL;

		$outOfDateEvents = EventRecord::find()->where([
			'and',
			['>=', $table . '.[[lastSlot]]', $now],
			$where,
		])->all();

		echo '├ Got events' . PHP_EOL;

		if (empty($outOfDateEvents))
		{
			echo '└ No events to refresh' . PHP_EOL;
			return;
		}

		echo '├ Starting refresh' . PHP_EOL;

		/** @var EventRecord $record */
		foreach ($outOfDateEvents as $record)
		{
			echo '│ ├ Refreshing #' . $record->id . PHP_EOL;

			$event = Event::fromRecord($record);

			$record->nextSlot = $event->getNextAvailableSlot();
			$record->save();
		}

		echo '└ Next Available Refresh Complete' . PHP_EOL;
	}

}