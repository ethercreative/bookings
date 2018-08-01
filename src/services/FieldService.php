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
use craft\helpers\Json;
use ether\bookings\fields\EventField;
use ether\bookings\models\Event;
use ether\bookings\records\EventRecord;


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
				'elementId' => $elementId,
				'fieldId'   => $fieldId,
				'enabled'   => $value['enabled'],
			];

			if ($record)
				$props['id'] = $record->id;

			$model = new Event(array_merge($props, $value['settings']));
		}

		else if ($record) {
			$model = new Event();

			$model->id         = $record->id;
			$model->elementId  = $record->elementId;
			$model->fieldId    = $record->fieldId;
			$model->enabled    = $record->enabled;
			$model->type       = $record->type;
			$model->capacity   = $record->capacity;
			$model->multiplier = $record->multiplier;
			$model->baseRule   = $record->baseRule;
			$model->exceptions = $record->exceptions;
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

		$record->enabled    = $model->enabled;
		$record->type       = $model->type;
		$record->capacity   = $model->capacity;
		$record->multiplier = $model->multiplier;
		$record->baseRule   = $model->baseRule;
		$record->exceptions = $model->exceptions;

		if (!$record->save())
		{
			\Craft::error($record->getErrors(), 'bookings');
			return false;
		}

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

		if (!$value)
			return;

		$tableName = EventRecord::$tableName;
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

}