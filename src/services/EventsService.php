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

	// Getters
	// =========================================================================

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
	 * Returns the events, paginated
	 *
	 * TODO: Make searchable
	 * TODO: Split between current / future and past
	 *
	 * @param int $page - The page to get
	 *
	 * @return array
	 */
	public function getPaginatedEvents ($page = 0)
	{
		$limit = 20;

		$events =
			EventRecord::find()
				->orderBy('nextSlot desc')
				->limit($limit)
				->offset($limit * $page)
				->with('element')
				->all();

		$events = Event::fromRecords($events);

		$total = EventRecord::find()->count();
		$pages = ceil($total / $limit);

		return compact('events', 'pages');
	}

	// Actions
	// =========================================================================

	/**
	 * Refreshes the cached next available slot DB column
	 *
	 * @param bool $includeNull
	 *
	 * @throws \Exception
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