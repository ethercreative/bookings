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
use ether\bookings\models\BookableFieldSettings;
use ether\bookings\records\BookableFieldSettingsRecord;
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

		if (
			!\Craft::$app->request->isConsoleRequest
			&& \Craft::$app->request->isPost
			&& $value
		) {
			if (is_string($value))
				$value = json_decode($value, true);

			$model = new Bookable($value);
		} else if ($record) {
			$settings = $record->getAttributes()['settings'];

			try {
				$settings = json_decode($settings, true);
			} catch (\Exception $e) {
				$settings = [];
			}

			$model = new Bookable($settings);
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

		$record->settings = json_encode($value->asArray());

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

	// Settings
	// =========================================================================

	public function getSettings (BookableField $field)
	{
		$record = BookableFieldSettingsRecord::findOne([
			'fieldId' => $field->id,
		]);

		return $record
			? new BookableFieldSettings($record)
			: new BookableFieldSettings();
	}

	/**
	 * @param BookableField         $field
	 * @param BookableFieldSettings $settings
	 *
	 * @throws \yii\db\Exception
	 * @throws \Exception
	 */
	public function saveSettings (BookableField $field, BookableFieldSettings $settings)
	{
		if ($settings->id) {
			$record = BookableFieldSettingsRecord::findOne([
				'id' => $settings->id,
			]);
		} else {
			$record = new BookableFieldSettingsRecord();
			$record->fieldId = $field->id;
		}

		// TODO: Validate settings model

		$db = \Craft::$app->getDb();
		$transaction = $db->beginTransaction();

		try {
			// Save the new one
			$fieldLayout = $settings->getFieldLayout();
			\Craft::$app->getFields()->saveLayout($fieldLayout);

			// Update the Order record/model with the new layout ID
			$settings->fieldLayoutId = $fieldLayout->id;
			$record->fieldLayoutId = $fieldLayout->id;

			// Save it!
			$record->save(false);

			// Now that we have a calendar ID, save it on the model
			if (!$settings->id)
				$settings->id = $record->id;

			$transaction->commit();
		} catch (\Exception $e) {
			$transaction->rollBack();
			throw $e;
		}
	}

}