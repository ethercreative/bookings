<?php
/**
 * Bookings for Craft CMS
 *
 * @link      https://ethercreative.co.uk
 * @copyright Copyright (c) 2018 Ether Creative
 */

namespace ether\bookings\services;

use craft\base\Component;
use craft\db\Query;
use ether\bookings\records\EventRecord;

/**
 * Class ApiService
 *
 * @author  Ether Creative
 * @package ether\bookings\services
 */
class ApiService extends Component
{

	/**
	 * Get an array of all events.
	 *
	 * TODO: Make searchable, sortable, and paginated
	 *
	 * @return array
	 */
	public function getEvents ()
	{
		return
			$this->_eventsQuery()
			     ->where(['e.enabled' => true])
			     ->all();
	}

	/**
	 * Gets the event from the given ID
	 *
	 * @param int $eventId
	 *
	 * @return array|bool
	 */
	public function getEventById (int $eventId)
	{
		return
			$this->_eventsQuery()
			     ->where(['e.id' => $eventId])
			     ->one();
	}

	// Helpers
	// =========================================================================

	/**
	 * Builds a query for querying event records
	 *
	 * @return Query
	 */
	private function _eventsQuery ()
	{
		return (new Query())
			->select(['e.[[id]]', 'c.[[title]]'])
			->from([EventRecord::$tableName . ' e'])
			->leftJoin('{{%content}} c', 'e.[[elementId]] = c.[[elementId]]');
	}

}