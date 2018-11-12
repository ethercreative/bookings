<?php
/**
 * Bookings plugin for Craft CMS 3.x
 *
 * @link      https://ethercreative.co.uk
 * @copyright Copyright (c) Ether Creative
 */

namespace ether\bookings\services;

use craft\base\Component;
use craft\base\Element;
use craft\base\ElementInterface;
use craft\elements\db\ElementQuery;
use craft\elements\db\ElementQueryInterface;
use craft\elements\db\MatrixBlockQuery;
use craft\fields\Matrix;
use craft\helpers\Db;
use craft\helpers\Json;
use ether\bookings\Bookings;
use ether\bookings\fields\EventField;
use ether\bookings\fields\TicketField;
use ether\bookings\models\Event;
use ether\bookings\models\Ticket;
use ether\bookings\records\EventRecord;
use ether\bookings\records\TicketRecord;
use yii\base\InvalidConfigException;
use yii\db\Expression;


/**
 * Class FieldService
 *
 * @author  Ether Creative
 * @package ether\bookings\services
 * @since   1.0.0
 */
class FieldService extends Component
{

	// Event Field
	// =========================================================================

	/**
	 * Populates the event field value
	 *
	 * @param EventField       $field
	 * @param ElementInterface $element
	 * @param                  $value
	 *
	 * @return Event
	 */
	public function getEventField (EventField $field, ElementInterface $element, $value): Event
	{
		/** @var Element $element */

		if ($value instanceof Event)
			return $value;

		$request   = \Craft::$app->request;
		$fieldId   = $field->id;
		$elementId = $element->id;

		$record = EventRecord::findOne([
			'elementId' => $elementId,
			'fieldId'   => $fieldId,
		]);

		if (!$request->isConsoleRequest && $request->isPost && $value)
		{
			if (is_string($value))
				$value = Json::decode($value, true);

			if (is_string($value['settings']))
				$value['settings'] = Json::decode($value['settings'], true);

			$props = [
				'elementId'  => $elementId,
				'fieldId'    => $fieldId,
				'enabled'    => $value['enabled'],
				'capacity'   => $value['capacity'],
				'multiplier' => $value['multiplier'],
			];

			if ($record)
				$props['id'] = $record->id;

			$model = new Event(array_merge($props, $value['settings']));
		}

		else if ($record) {
			$model = Event::fromRecord($record);
		}

		else {
			$model = new Event();
		}

		return $model;
	}

	/**
	 * Saves the given field
	 *
	 * @param EventField       $field
	 * @param ElementInterface $element
	 * @param bool             $isNew
	 *
	 * @return bool
	 */
	public function saveEventField (EventField $field, ElementInterface $element, $isNew): bool
	{
		/** @var Element $element */

		/** @var Event $model */
		$model = $element->getFieldValue($field->handle);
		$record = null;

		if (!$isNew)
		{
			$record = EventRecord::findOne([
				'elementId' => $element->id,
				'fieldId'   => $field->id,
			]);
		}

		if (!$record)
		{
			$record            = new EventRecord();
			$record->elementId = $element->id;
			$record->fieldId   = $field->id;
		}

		$record->enabled    = (bool) $model->enabled;
		$record->type       = $model->type;
		$record->capacity   = $model->capacity;
		$record->multiplier = $model->multiplier;
		$record->baseRule   = $model->baseRule;
		$record->exceptions = $model->exceptions;
		$record->isInfinite = $model->isInfinite;
		$record->nextSlot   = $model->getNextAvailableSlot();
		$record->firstSlot  = $model->firstSlot;
		$record->lastSlot   = $model->lastSlot;

		if (!$record->save())
		{
			\Craft::error($record->getErrors(), 'bookings');
			return false;
		}

		// Check on the current element
		$ticketFields = $this->_checkForTicketFieldsOnElement($element);

		// Check on variants (if this is a product)
		$ticketFields = $this->_checkForTicketsInVariants($element, $ticketFields);

		// Save all the ticket fields
		foreach ($ticketFields as $f)
			$this->setTicketFieldEventId($f['field'], $f['elementId'], $record->id);

		return true;
	}

	/**
	 * Modifies the element query to inject the field data
	 *
	 * @param ElementQueryInterface $query
	 * @param                       $value
	 */
	public function modifyEventFieldQuery (ElementQueryInterface $query, $value)
	{
		/** @var ElementQuery $query */

		$tableName = EventRecord::$tableName;
		$tableAlias = 'events' . bin2hex(openssl_random_pseudo_bytes(5));

		$shouldJoin = false;

		if ($value && array_key_exists('before', $value))
		{
			$shouldJoin = true;

			$before = Db::prepareDateForDb($value['before']);
			$query->subQuery->andWhere(
				'[[' . $tableAlias . '.firstSlot]] <= \'' . $before . '\''
			);
		}

		if ($value && array_key_exists('after', $value))
		{
			$shouldJoin = true;

			$after = Db::prepareDateForDb($value['after']);
			$query->subQuery->andWhere([
				'or',
				'[[' . $tableAlias . '.firstSlot]] >= \'' . $after . '\'',
				'[[' . $tableAlias . '.lastSlot]] >= \'' . $after . '\'',
				[
					'and',
					'[[' . $tableAlias . '.firstSlot]] <= \'' . $after . '\'',
					'[[' . $tableAlias . '.isInfinite]] = true',
				]
			]);
		}

		if (is_array($query->orderBy) && array_key_exists('bookings:nextSlot', $query->orderBy))
		{
			$shouldJoin = true;

			$query->orderBy['bookings_nextSlot'] = $query->orderBy['bookings:nextSlot'];
			unset($query->orderBy['bookings:nextSlot']);

			$query->subQuery->addSelect(
				'[[' . $tableAlias . '.nextSlot]] as [[bookings_nextSlot]]'
			);
		}

		if ($shouldJoin)
		{
			$on = [
				'and',
				'[[elements.id]] = [[' . $tableAlias . '.elementId]]',
			];

			$query->subQuery->join(
				'JOIN',
				$tableName . ' ' . $tableAlias,
				$on
			);
		}
	}

	// Ticket Field
	// =========================================================================

	/**
	 * @param TicketField      $field
	 * @param ElementInterface $element
	 * @param                  $value
	 *
	 * @return Ticket
	 */
	public function getTicketField (TicketField $field, ElementInterface $element, $value): Ticket
	{
		/** @var Element $element */

		if ($value instanceof Ticket)
			return $value;

		$request = \Craft::$app->request;
		$fieldId = $field->id;
		$elementId = $element->id;

		$record = TicketRecord::findOne([
			'elementId' => $elementId,
			'fieldId' => $fieldId,
		]);

		$model = new Ticket();

		if (!$request->isConsoleRequest && $request->isPost && $value)
		{
			$model->elementId = $elementId;
			$model->fieldId   = $fieldId;
			$model->capacity  = $value['capacity'];
		}

		else if ($record) {
			$model->id        = $record->id;
			$model->elementId = $record->elementId;
			$model->fieldId   = $record->fieldId;
			$model->capacity  = $record->capacity;
		}

		return $model;
	}

	/**
	 * @param TicketField      $field
	 * @param ElementInterface $element
	 * @param                  $isNew
	 *
	 * @return bool
	 */
	public function saveTicketField (TicketField $field, ElementInterface $element, $isNew)
	{
		/** @var Element $element */

		/** @var Ticket $model */
		$model = $element->getFieldValue($field->handle);
		$record = null;

		if (!$isNew)
		{
			$record = TicketRecord::findOne([
				'elementId' => $element->id,
				'fieldId'   => $field->id,
			]);
		}

		if (!$record)
		{
			$record            = new TicketRecord();
			$record->elementId = $element->id;
			$record->fieldId   = $field->id;
			$record->eventId   = $this->_findEventIdFromTicket($element);
		}

		$record->capacity = $model->capacity;

		if (!$record->save())
		{
			\Craft::error($record->getErrors(), 'bookings');
			return false;
		}

		return true;
	}

	/**
	 * @param TicketField $field
	 * @param             $elementId
	 * @param             $eventId
	 */
	public function setTicketFieldEventId (TicketField $field, $elementId, $eventId)
	{
		$record = TicketRecord::findOne([
			'elementId' => $elementId,
			'fieldId'   => $field->id,
		]);

		// If we can't find the record then the ticket field hasn't been saved
		// yet, so it will get the event ID later (when it's saved).
		if (!$record)
			return;

		$record->eventId = $eventId;

		$record->save(false);
	}

	/**
	 * @param ElementQueryInterface $query
	 * @param                       $value
	 */
	public function modifyTicketFieldQuery (ElementQueryInterface $query, $value)
	{
		/** @var ElementQuery $query */

		if (!$value)
			return;

		$tableName  = TicketRecord::$tableName;
		$tableAlias = 'events' . bin2hex(openssl_random_pseudo_bytes(5));

		$on = [
			'and',
			'[[elements.id]] = [[' . $tableAlias . '.ownerId]]',
		];

		$query->query->join(
			'JOIN',
			$tableName . ' ' . $tableAlias,
			$on
		);

		$query->subQuery->join(
			'JOIN',
			$tableName . ' ' . $tableAlias,
			$on
		);
	}

	// Helpers
	// =========================================================================

	/**
	 * Finds Ticket fields on the given element
	 *
	 * @param ElementInterface $element
	 * @param array            $ticketFields
	 *
	 * @return array
	 */
	private function _checkForTicketFieldsOnElement (ElementInterface $element, $ticketFields = [])
	{
		/** @var Element $element */

		$fields = $element->getFieldLayout()->getFields();

		foreach ($fields as $elementField)
		{
			if ($elementField instanceof TicketField)
			{
				$ticketFields[] = [
					'field' => $elementField,
					'elementId' => $element->id,
				];
			}

			// We don't have to worry about matrix blocks since they're saved
			// after the parent element (meaning after the event field is saved)
			// I think.
//			else if ($elementField instanceof Matrix)
//			{
//				/** @var MatrixBlockQuery $matrix */
//				$matrix = $element->{$elementField->handle};
//				foreach ($matrix->all() as $block)
//					$ticketFields = $this->_checkForTicketFieldsOnElement($block, $ticketFields);
//			}
		}

		return $ticketFields;
	}

	/**
	 * Finds Ticket fields on the variants of the given product
	 *
	 * @param ElementInterface $element
	 * @param array            $ticketFields
	 *
	 * @return array
	 */
	private function _checkForTicketsInVariants (ElementInterface $element, $ticketFields = [])
	{
		if (!class_exists(\craft\commerce\elements\Product::class))
			return $ticketFields;

		if (!$element instanceof \craft\commerce\elements\Product)
			return $ticketFields;

		/** @var \craft\commerce\elements\Product $element */

		foreach ($element->variants as $variant)
			$ticketFields = $this->_checkForTicketFieldsOnElement($variant, $ticketFields);

		return $ticketFields;
	}

	/**
	 * @param ElementInterface $element
	 *
	 * @return int|null
	 */
	private function _findEventIdFromTicket (ElementInterface $element)
	{
		/** @var Element $element */

		$owner = $element;

		do {
			if (property_exists($owner, 'ownerId'))
				$owner = $owner->owner;
			else break;
		} while ($owner);

		$eventField = null;

		foreach ($owner->fieldLayout->getFields() as $field)
			if ($field instanceof EventField)
				$eventField = $field;

		if (!$eventField)
			return null;

		$event = EventRecord::findOne([
			'elementId' => $owner->id,
			'fieldId'   => $eventField->id,
		]);

		if ($event)
			return $event->id;

		return null;
	}

}