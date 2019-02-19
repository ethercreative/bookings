<?php
/**
 * Bookings for Craft CMS
 *
 * @link      https://ethercreative.co.uk
 * @copyright Copyright (c) 2019 Ether Creative
 */

namespace ether\bookings\services;

use craft\base\Component;
use craft\base\Field;
use craft\db\Query;
use craft\db\Table;
use craft\events\ConfigEvent;
use craft\events\DeleteSiteEvent;
use craft\events\FieldEvent;
use craft\events\SiteEvent;
use craft\helpers\Db;
use craft\helpers\ProjectConfig;
use craft\helpers\StringHelper;
use craft\models\FieldLayout;
use craft\queue\jobs\ResaveElements;
use ether\bookings\Bookings;
use ether\bookings\elements\Event;
use ether\bookings\events\EventTypeEvent;
use ether\bookings\models\EventType;
use ether\bookings\records\EventType as EventTypeRecord;
use ether\bookings\models\EventTypeSite;
use ether\bookings\records\EventTypeSite as EventTypeSiteRecord;
use yii\base\Exception;

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

	const EVENT_BEFORE_DELETE_EVENTTYPE = 'beforeDeleteEventType';
	const EVENT_BEFORE_APPLY_DELETE_EVENTTYPE = 'beforeApplyDeleteEventType';
	const EVENT_AFTER_DELETE_EVENTTYPE = 'afterDeleteEventType';

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

	// Getters
	// -------------------------------------------------------------------------

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

		$this->_allEventTypes = array_values($this->_eventTypesById);

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

	// Saving
	// -------------------------------------------------------------------------

	/**
	 * @param EventType $eventType
	 * @param bool      $runValidation
	 *
	 * @return bool
	 * @throws Exception
	 * @throws \yii\base\ErrorException
	 * @throws \yii\base\InvalidConfigException
	 * @throws \yii\base\NotSupportedException
	 * @throws \yii\web\ServerErrorHttpException
	 */
	public function saveEventType (EventType $eventType, bool $runValidation = true): bool
	{
		$isNewEventType = !$eventType->id;

		// Trigger before save event
		if ($this->hasEventHandlers(self::EVENT_BEFORE_SAVE_EVENTTYPE))
		{
			$this->trigger(
				self::EVENT_BEFORE_SAVE_EVENTTYPE,
				new EventTypeEvent([
					'eventType' => $eventType,
					'isNew' => $isNewEventType,
				])
			);
		}

		// Validate
		if ($runValidation && !$eventType->validate())
		{
			\Craft::info(
				'Event type not saved due to validation error.',
				__METHOD__
			);

			return false;
		}

		// Set the UUID
		if ($isNewEventType)
			$eventType->uid = StringHelper::UUID();

		else if (!$eventType->uid)
			$eventType->uid = Db::uidById(EventTypeRecord::TableName, $eventType->id);

		// Project Config
		// ---------------------------------------------------------------------

		$projectConfig = \Craft::$app->getProjectConfig();

		$configData = [
			'name' => $eventType->name,
			'handle' => $eventType->handle,
			'enableVersioning' => $eventType->enableVersioning,
			'hasTitleField' => $eventType->hasTitleField,
			'titleLabel' => $eventType->titleLabel,
			'titleFormat' => $eventType->titleFormat,
			'propagateEvents' => $eventType->propagateEvents,
			'siteSettings' => [],
		];

		$allSiteSettings = $eventType->getSiteSettings();

		if (empty($allSiteSettings))
			throw new Exception('Tried to save event type without any site settings');

		foreach ($allSiteSettings as $siteId => $settings)
		{
			$siteUid = Db::uidById(Table::SITES, $siteId);
			$configData['siteSettings'][$siteUid] = [
				'enabledByDefault' => $settings['enabledByDefault'],
				'hasUrls'          => $settings['hasUrls'],
				'uriFormat'        => $settings['uriFormat'],
				'template'         => $settings['template'],
			];
		}

		$fieldLayout = $eventType->getFieldLayout();
		$fieldLayoutConfig = $fieldLayout->getConfig();

		if ($fieldLayoutConfig)
		{
			if (empty($fieldLayout->id))
			{
				$layoutUid = StringHelper::UUID();
				$fieldLayout->uid = $layoutUid;
			}
			else
			{
				$layoutUid = Db::uidById(Table::FIELDLAYOUTS, $fieldLayout->id);
			}

			$configData['fieldLayouts'] = [
				$layoutUid => $fieldLayoutConfig,
			];
		}

		$configPath = self::CONFIG_EVENTTYPES_KEY . '.' . $eventType->uid;
		$projectConfig->set($configPath, $configData);

		// The actual saving is handled by the project config event listeners...

		if ($isNewEventType)
			$eventType->id = Db::idByUid(EventTypeRecord::TableName, $eventType->uid);

		return true;
	}

	/**
	 * @param ConfigEvent $event
	 *
	 * @throws \Throwable
	 * @throws \yii\db\Exception
	 */
	public function handleChangedEventType (ConfigEvent $event)
	{
		$eventTypeUid = $event->tokenMatches[0];
		$data = $event->newValue;

		ProjectConfig::ensureAllSitesProcessed();
		ProjectConfig::ensureAllFieldsProcessed();

		$db = \Craft::$app->getDb();
		$fields = \Craft::$app->getFields();
		$transaction = $db->beginTransaction();

		try
		{
			$siteData = $data['siteSettings'];

			// Event Type Record
			// -----------------------------------------------------------------

			$oldRecord = $this->_getEventTypeRecord($eventTypeUid, true);
			$eventTypeRecord = clone $oldRecord;
			$isNewEventType = $eventTypeRecord->getIsNewRecord();

			$eventTypeRecord->uid = $eventTypeUid;
			$eventTypeRecord->name = $data['name'];
			$eventTypeRecord->handle = $data['handle'];
			$eventTypeRecord->enableVersioning = (bool) $data['enableVersioning'];
			$eventTypeRecord->hasTitleField = (bool) $data['hasTitleField'];
			$eventTypeRecord->titleLabel = $data['titleLabel'];
			$eventTypeRecord->titleFormat = $data['titleFormat'];
			$eventTypeRecord->propagateEvents = (bool) $data['propagateEvents'];

			if (
				!empty($data['fieldLayouts']) &&
				!empty($config = reset($data['fieldLayouts']))
			) {
				$layout = FieldLayout::createFromConfig($config);
				$layout->id = $eventTypeRecord->fieldLayoutId;
				$layout->type = Event::class;
				$layout->uid = key($data['fieldLayouts']);
				$fields->saveLayout($layout);
				$eventTypeRecord->fieldLayoutId = $layout->id;
			}
			else
			{
				$fields->deleteLayoutById($eventTypeRecord->fieldLayoutId);
				$eventTypeRecord->fieldLayoutId = null;
			}

			if ($wasTrashed = (bool) $eventTypeRecord->dateDeleted)
				$eventTypeRecord->restore();
			else
				$eventTypeRecord->save(false);

			// Sites
			// -----------------------------------------------------------------

			if ($isNewEventType)
			{
				$allOldSiteSettingsRecords = [];
			}
			else
			{
				$allOldSiteSettingsRecords = EventTypeSiteRecord::find()
					->where(['eventTypeId' => $eventTypeRecord->id])
					->indexBy('siteId')
					->all();
			}

			$siteIdMap = Db::idsByUids(Table::SITES, array_keys($siteData));

			foreach ($siteData as $siteUid => $siteSettings)
			{
				$siteId = $siteIdMap[$siteUid];

				if (!$isNewEventType && isset($allOldSiteSettingsRecords[$siteId])) {
					$siteSettingsRecord = $allOldSiteSettingsRecords[$siteId];
				} else {
					$siteSettingsRecord = new EventTypeSiteRecord();
					$siteSettingsRecord->eventTypeId = $eventTypeRecord->id;
					$siteSettingsRecord->siteId = $siteId;
				}

				$siteSettingsRecord->enabledByDefault = (bool) $siteSettings['enabledByDefault'];

				if ($siteSettingsRecord->hasUrls = $siteSettings['hasUrls']) {
					$siteSettingsRecord->uriFormat = $siteSettings['uriFormat'];
					$siteSettingsRecord->template = $siteSettings['template'];
				} else {
					$siteSettingsRecord->uriFormat = $siteSettings['uriFormat'] = null;
					$siteSettingsRecord->template = $siteSettings['template'] = null;
				}

				$siteSettingsRecord->save(false);
			}

			if (!$isNewEventType)
			{
				// Drop any sites that are no longer being used, as well as the
				// associated event/element site rows.

				$affectedSiteUids = array_keys($siteData);

				foreach ($allOldSiteSettingsRecords as $siteId => $siteSettingsRecord)
				{
					$siteUid = array_search($siteId, $siteIdMap, false);

					if (!in_array($siteUid, $affectedSiteUids, false))
						$siteSettingsRecord->delete();
				}
			}

			// Existing Events
			// -----------------------------------------------------------------

			if ($isNewEventType)
				goto commit; // goto or indent... goto... or... indent...

			if ($oldRecord->propagateEvents)
			{
				// Find sites that the event type was already enabled in, and
				// still is.
				$oldSiteIds = array_keys($allOldSiteSettingsRecords);
				$newSiteIds = $siteIdMap;
				$persistentSiteIds = array_values(
					array_intersect($newSiteIds, $oldSiteIds)
				);

				// Find the primary site (or first available)
				$siteId = \Craft::$app->getSites()->getPrimarySite()->id;
				if (!in_array($siteId, $persistentSiteIds, false))
					$siteId = $persistentSiteIds[0];

				\Craft::$app->getQueue()->push(new ResaveElements([
					'description' => Bookings::t(
						'Resaving {type} events',
						['type' => $eventTypeRecord->name]
					),
					'elementType' => Event::class,
					'criteria'    => [
						'siteId'         => $siteId,
						'eventTypeId'    => $eventTypeRecord->id,
						'status'         => null,
						'enabledForSite' => false,
					],
				]));
			}
			else
			{
				// Resave events for each site
				$sites = \Craft::$app->getSites();

				foreach ($siteData as $siteUid => $siteSettings)
				{
					/** @noinspection PhpParamsInspection */
					\Craft::$app->getQueue()->push(new ResaveElements([
						'description' => Bookings::t(
							'Resaving {type} events ({site})', [
							'type' => $eventTypeRecord->name,
							'site' => $sites->getSiteByUid($siteUid)->name,
						]),
						'elementType' => Event::class,
						'criteria'    => [
							'siteId'         => $siteIdMap[$siteUid],
							'eventTypeId'    => $eventTypeRecord->id,
							'status'         => null,
							'enabledForSite' => false,
						],
					]));
				}
			}

			commit: // Beware of raptors
			$transaction->commit();
		}
		catch (\Throwable $e)
		{
			$transaction->rollBack();
			throw $e;
		}

		// Trigger after save event
		if ($this->hasEventHandlers(self::EVENT_AFTER_SAVE_EVENTTYPE))
		{
			$this->trigger(
				self::EVENT_BEFORE_SAVE_EVENTTYPE,
				new EventTypeEvent([
					'eventType' => $this->getEventTypeById($eventTypeRecord->id),
					'isNew'     => $isNewEventType,
				])
			);
		}
	}

	/**
	 * @param SiteEvent $event
	 *
	 * @throws Exception
	 * @throws \yii\base\ErrorException
	 * @throws \yii\base\NotSupportedException
	 * @throws \yii\web\ServerErrorHttpException
	 */
	public function afterSaveSiteHandler (SiteEvent $event)
	{
		if (!$event->isNew)
			return;

		// Adds a new event type site setting row when a site is added to Craft
		$primarySiteSettings = (new Query())
			->select([
				'eventTypes.uid eventTypeUid',
				'eventTypes_sites.enabledByDefault',
				'eventTypes_sites.hasUrls',
				'eventTypes_sites.uriFormat',
				'eventTypes_sites.template',
			])
			->from([EventTypeSiteRecord::TableName . ' eventTypes_sites'])
			->innerJoin(
				[EventTypeRecord::TableName . 'eventTypes'],
				'[[eventTypes_sites.eventTypeId]] = [[eventTypes.id]]'
			)
			->where(['siteId' => $event->oldPrimarySiteId])
			->one();

		if (!$primarySiteSettings)
			return;

		$newSiteSettings = [
			'enabledByDefault' => $primarySiteSettings['enabledByDefault'],
			'hasUrls'          => $primarySiteSettings['hasUrls'],
			'uriFormat'        => $primarySiteSettings['uriFormat'],
			'template'         => $primarySiteSettings['template'],
		];

		$key = self::CONFIG_EVENTTYPES_KEY . '.' .
		       $primarySiteSettings['eventTypeUid'] . '.siteSettings.' .
		       $event->site->uid;

		\Craft::$app->getProjectConfig()->set($key, $newSiteSettings);
	}

	// Deleting
	// -------------------------------------------------------------------------

	/**
	 * @param int $id
	 *
	 * @return bool
	 */
	public function deleteEventTypeById (int $id): bool
	{
		$eventType = $this->getEventTypeById($id);

		// Trigger before delete event
		if ($this->hasEventHandlers(self::EVENT_BEFORE_DELETE_EVENTTYPE))
		{
			$this->trigger(
				self::EVENT_BEFORE_DELETE_EVENTTYPE,
				new EventTypeEvent(compact('eventType'))
			);
		}

		\Craft::$app->getProjectConfig()->remove(
			self::CONFIG_EVENTTYPES_KEY . '.' . $eventType->uid
		);

		// Deletion is handled by the project config event listeners...

		return true;
	}

	/**
	 * @param ConfigEvent $event
	 *
	 * @throws \Throwable
	 * @throws \yii\db\Exception
	 */
	public function handleDeletedEventType (ConfigEvent $event)
	{
		$uid = $event->tokenMatches[0];
		$eventTypeRecord = $this->_getEventTypeRecord($uid);

		if (!$eventTypeRecord->id)
			return;

		$eventType = $this->getEventTypeById($eventTypeRecord->id);

		// Trigger before apply delete event
		if ($this->hasEventHandlers(self::EVENT_BEFORE_APPLY_DELETE_EVENTTYPE))
		{
			$this->trigger(
				self::EVENT_BEFORE_APPLY_DELETE_EVENTTYPE,
				new EventTypeEvent(compact('eventType'))
			);
		}

		$db = \Craft::$app->getDb();
		$elements = \Craft::$app->getElements();
		$transaction = $db->beginTransaction();

		try
		{
			// Delete the events
			$eventQuery = Event::find()
				->anyStatus()
				->typeId($eventTypeRecord->id);

			foreach (\Craft::$app->getSites()->getAllSiteIds() as $siteId)
			{
				foreach ($eventQuery->siteId($siteId)->each() as $event)
				{
					/** @var Event $event */
					$event->deletedWithEventType = true;
					$elements->deleteElement($event);
				}
			}

			// Delete the field layout
			if ($eventTypeRecord->fieldLayoutId)
				\Craft::$app->getFields()->deleteLayoutById($eventTypeRecord->fieldLayoutId);

			// Delete the event type
			\Craft::$app->getDb()->createCommand()
				->softDelete(
					EventTypeRecord::TableName,
					['id' => $eventTypeRecord->id]
				)
				->execute();

			$transaction->commit();
		}
		catch (\Throwable $e)
		{
			$transaction->rollBack();
			throw $e;
		}

		// Trigger after delete event
		if ($this->hasEventHandlers(self::EVENT_AFTER_DELETE_EVENTTYPE))
		{
			$this->trigger(
				self::EVENT_AFTER_DELETE_EVENTTYPE,
				new EventTypeEvent(compact('eventType'))
			);
		}
	}

	/**
	 * @param FieldEvent $event
	 */
	public function pruneDeletedField (FieldEvent $event)
	{
		/** @var Field $field */
		$field = $event->field;
		$fieldUid = $field->uid;

		$projectConfig = \Craft::$app->getProjectConfig();
		$eventTypes = $projectConfig->get(self::CONFIG_EVENTTYPES_KEY);

		if (!is_array($eventTypes))
			return;

		// Loop through the event types and prune the UID from field layouts
		foreach ($eventTypes as $eventTypeUid => $eventType)
		{
			if (empty($eventType['fieldLayouts']))
				continue;

			foreach ($eventType['fieldLayouts'] as $layoutUid => $layout)
			{
				if (empty($layout['tabs']))
					continue;

				foreach ($layout['tabs'] as $tabUid => $tab)
					$projectConfig->remove(
						self::CONFIG_EVENTTYPES_KEY .
						'.' . $eventTypeUid .
						'.fieldLayouts.' . $layoutUid .
						'.tabs.' . $tabUid .
						'.fields.' . $fieldUid
					);
			}
		}
	}

	/**
	 * @param DeleteSiteEvent $event
	 */
	public function pruneDeletedSite (DeleteSiteEvent $event)
	{
		$siteUid = $event->site->uid;

		$projectConfig = \Craft::$app->getProjectConfig();
		$eventTypes = $projectConfig->get(self::CONFIG_EVENTTYPES_KEY);

		if (!is_array($eventTypes))
			return;

		foreach ($eventTypes as $eventTypeUid => $eventType)
			$projectConfig->remove(
				self::CONFIG_EVENTTYPES_KEY .
				'.' . $eventTypeUid .
				'.siteSettings.' . $siteUid
			);
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

	/**
	 * Get an Event Type record by its UUID
	 *
	 * @param string $uid
	 * @param bool   $withTrashed
	 *
	 * @return EventTypeRecord
	 */
	private function _getEventTypeRecord (string $uid, bool $withTrashed = false): EventTypeRecord
	{
		$query = $withTrashed ? EventTypeRecord::findWithTrashed() : EventTypeRecord::find();
		$query->andWhere(compact('uid'));

		/** @noinspection PhpIncompatibleReturnTypeInspection */
		return $query->one() ?? new EventTypeRecord();
	}

}
