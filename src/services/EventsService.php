<?php
/**
 * Bookings plugin for Craft CMS 3.x
 *
 * @link      https://ethercreative.co.uk
 * @copyright Copyright (c) Ether Creative
 */

namespace ether\bookings\services;

use craft\base\Component;
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

}