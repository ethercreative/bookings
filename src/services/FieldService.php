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
use ether\bookings\fields\BookableField;
use ether\bookings\models\Bookable;
use ether\bookings\records\BookableRecord;


/**
 * Class FieldService
 *
 * @author  Ether Creative
 * @package ether\bookings\services
 * @since   1.0.0
 */
class FieldService extends Component
{

	// Field
	// =========================================================================

	/**
	 * Gets the field
	 *
	 * @param BookableField    $field
	 * @param ElementInterface $owner
	 * @param                  $value
	 *
	 * @return Bookable
	 */
	public function getField (BookableField $field, ElementInterface $owner, $value): Bookable
	{
		/** @var Element $owner */
		$record = BookableRecord::findOne(
			[
				'ownerId'     => $owner->id,
				'fieldId'     => $field->id,
			]
		);

		if ($value instanceof Bookable)
			return $value;

		$id = $field->id;
		$ownerId = $owner->id;

		if (
			!\Craft::$app->request->isConsoleRequest
			&& \Craft::$app->request->isPost
			&& $value
		) {
			if (is_string($value))
				$value = json_decode($value, true);

			if (is_string($value['settings']))
				$value['settings'] = json_decode($value['settings'], true);

			$model = new Bookable(array_merge(
				[
					'id' => $id,
					'ownerId' => $ownerId,
					'enabled' => $value['enabled']
				],
				$value['settings']
			));
		} else if ($record) {
			$enabled = $record->getAttributes()['enabled'];
			$settings = $record->getAttributes()['settings'];

			try {
				$settings = json_decode($settings, true);
			} catch (\Exception $e) {
				$settings = [];
			}

			$model = new Bookable(
				array_merge(
					compact('id', 'ownerId', 'enabled'),
					$settings
				)
			);
		} else {
			$model = new Bookable();
		}

		return $model;
	}

	/**
	 * Saves the given field
	 *
	 * @param BookableField    $field
	 * @param ElementInterface $owner
	 *
	 * @return bool
	 */
	public function saveField (BookableField $field, ElementInterface $owner): bool
	{
		/** @var Element $owner */

		/** @var Bookable $value */
		$value = $owner->getFieldValue($field->handle);

		$record = BookableRecord::findOne(
			[
				'ownerId'     => $owner->id,
				'fieldId'     => $field->id,
			]
		);

		if (!$record) {
			$record              = new BookableRecord();
			$record->ownerId     = $owner->id;
			$record->fieldId     = $field->id;
		}

		$record->enabled = $value->enabled;
		$record->settings = json_encode($value->asArray()['settings']);

		$save = $record->save();

		if (!$save) {
			\Craft::getLogger()->log(
				$record->getErrors(),
				LOG_ERR,
				'bookings'
			);
		}

		return $save;
	}

	/**
	 * Modifies the query to inject the field data
	 *
	 * @param ElementQueryInterface $query
	 * @param                       $value
	 */
	public function modifyElementsQuery (ElementQueryInterface $query, $value)
	{
		if (!$value) return;
		/** @var ElementQuery $query */

		$tableName = BookableRecord::$tableName;
		$tableAlias = 'bookables' . bin2hex(openssl_random_pseudo_bytes(5));

		$on = [
			'and',
			'[[elements.id]] = [['.$tableAlias.'.ownerId]]',
		];

		$query->query->join(
			'JOIN',
			"{$tableName} {$tableAlias}",
			$on
		);

		$query->subQuery->join(
			'JOIN',
			"{$tableName} {$tableAlias}",
			$on
		);
	}

}