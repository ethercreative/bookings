<?php
/**
 * Bookings for Craft CMS
 *
 * @link      https://ethercreative.co.uk
 * @copyright Copyright (c) 2019 Ether Creative
 */

namespace ether\bookings\services;

use craft\base\Component;
use ether\bookings\models\EventType;
use ether\bookings\records\EventType as EventTypeRecord;
use ether\bookings\models\EventTypeSite;
use ether\bookings\records\EventTypeSite as EventTypeSiteRecord;

/**
 * Class EventTypes
 *
 * @author  Ether Creative
 * @package ether\bookings\services
 */
class EventTypes extends Component
{

	// Consts
	// =========================================================================

	const EVENT_BEFORE_SAVE_EVENTTYPE = 'beforeSaveEventType';

	const EVENT_AFTER_SAVE_EVENTTYPE = 'afterSaveEventType';

	const CONFIG_EVENTTYPES_KEY = 'bookings.eventTypes';

	// Properties
	// =========================================================================

	private $_allEventTypes;

	private $_allEventTypeIds;

	private $_eventTypesById = [];

	private $_eventTypesByHandle = [];

	private $_editableEventTypes;

	private $_editableEventTypeIds;

	private $_siteSettingsByEventTypeId = [];

	// Methods
	// =========================================================================

	/**
	 * Returns all Event Types
	 *
	 * @return EventType[]
	 */
	public function getAllEventTypes (): array
	{
		if ($this->_allEventTypes)
			return $this->_allEventTypes;

		$results = EventTypeRecord::find()->all();

		foreach ($results as $result)
			$this->_memoizeEventType(new EventType($result));

		$this->_allEventTypes = $results;

		return $this->_allEventTypes ?: [];
	}

	/**
	 * Returns the IDs of all the event types
	 *
	 * @return int[]
	 */
	public function getAllEventTypeIds (): array
	{
		if ($this->_allEventTypeIds)
			return $this->_allEventTypeIds;

		return $this->_allEventTypeIds =
			EventTypeRecord::find()->select('id')->column();
	}

	/**
	 * Returns the Event Type for the given ID
	 *
	 * @param int $id
	 *
	 * @return EventType
	 */
	public function getEventTypeById (int $id): EventType
	{
		if (isset($this->_eventTypesById[$id]))
			return $this->_eventTypesById[$id];

		if ($this->_allEventTypes)
			return null;

		$result = EventTypeRecord::findOne($id);

		if (!$result)
			return null;

		$this->_memoizeEventType(new EventType($result));

		return $this->_eventTypesById[$id];
	}

	/**
	 * Returns all Event Type the current user can access
	 *
	 * @return EventType[]
	 */
	public function getEditableEventTypes (): array
	{
		if ($this->_editableEventTypes)
			return $this->_editableEventTypes;

		$editableEventTypeIds = $this->getEditableEventTypeIds();
		$this->_editableEventTypes = [];

		foreach ($this->getAllEventTypes() as $eventType)
			if (in_array($eventType->id, $editableEventTypeIds, false))
				$this->_editableEventTypes[] = $eventType;

		return $this->_editableEventTypes;
	}

	/**
	 * Returns the IDs of all Event Types the current user can access
	 *
	 * @return int[]
	 */
	public function getEditableEventTypeIds (): array
	{
		if ($this->_editableEventTypeIds)
			return $this->_editableEventTypeIds;

		$this->_editableEventTypeIds = [];

		foreach ($this->getAllEventTypeIds() as $id)
			if (\Craft::$app->getUser()->checkPermission('bookings-manageEventType:' . $id))
				$this->_editableEventTypeIds[] = $id;

		return $this->_editableEventTypeIds;
	}

	/**
	 * Returns all Event Type Sites for the given Event Type ID
	 *
	 * @param int $eventTypeId
	 *
	 * @return EventTypeSite[]
	 */
	public function getEventTypeSites (int $eventTypeId): array
	{
		if (isset($this->_siteSettingsByEventTypeId[$eventTypeId]))
			return $this->_siteSettingsByEventTypeId[$eventTypeId];

		$results = EventTypeSiteRecord::findAll([
			'eventTypeId' => $eventTypeId,
		]);

		$this->_siteSettingsByEventTypeId[$eventTypeId] = [];

		foreach ($results as $result)
			$this->_siteSettingsByEventTypeId[$eventTypeId][] = new EventTypeSite($result);

		return $this->_siteSettingsByEventTypeId[$eventTypeId];
	}

	// Helpers
	// =========================================================================

	/**
	 * Memoize an event type
	 *
	 * @param EventType $eventType
	 */
	private function _memoizeEventType (EventType $eventType)
	{
		$this->_eventTypesById[$eventType->id] = $eventType;
		$this->_eventTypesByHandle[$eventType->handle] = $eventType;
	}

}
