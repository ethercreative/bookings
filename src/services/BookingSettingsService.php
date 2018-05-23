<?php
/**
 * Bookings plugin for Craft CMS 3.x
 *
 * @link      https://ethercreative.co.uk
 * @copyright Copyright (c) Ether Creative
 */

namespace ether\bookings\services;

use craft\base\Component;
use craft\db\Query;
use ether\bookings\models\BookingSettings;
use ether\bookings\records\BookingSettingsRecord;


/**
 * Class BookingSettingsService
 *
 * @author  Ether Creative
 * @package ether\bookings\services
 * @since   1.0.0
 */
class BookingSettingsService extends Component
{

	// TODO: Down the line, each field will have its own booking settings (that will extend from the default?)

	// Properties
	// =========================================================================

	private $_bookingSettingsById;

	// Public Methods
	// =========================================================================

	/**
	 * Get booking settings by their ID.
	 *
	 * @param int $bookingSettingsId
	 * @return BookingSettings|null
	 */
	public function getOrderSettingById ($bookingSettingsId)
	{
		if (
			null === $this->_bookingSettingsById
			|| !array_key_exists($bookingSettingsId, $this->_bookingSettingsById)
		) {
			$result = $this->_createBookingSettingsQuery()
			               ->where(['id' => $bookingSettingsId])
			               ->one();

			$bookingSetting = $result ? new BookingSettings($result) : null;

			$this->_bookingSettingsById[$bookingSettingsId] = $bookingSetting;
		}

		if (!isset($this->_bookingSettingsById[$bookingSettingsId]))
			return null;

		return $this->_bookingSettingsById[$bookingSettingsId];
	}

	/**
	 * Get booking settings by their handle
	 *
	 * @param $handle
	 *
	 * @return BookingSettings
	 */
	public function getBookingSettingsByHandle ($handle)
	{
		$result = $this->_createBookingSettingsQuery()
			->where(compact('handle'))
			->one();

		if (!$result)
			return new BookingSettings();

		$bookingSettings = new BookingSettings($result);
		$this->_bookingSettingsById[$bookingSettings->id] = $bookingSettings;

		return $bookingSettings;
	}

	/**
	 * Saves the Booking Settings
	 *
	 * @param BookingSettings $bookingSettings
	 * @param bool            $runValidation
	 *
	 * @return bool
	 * @throws \yii\db\Exception
	 * @throws \Exception
	 */
	public function saveBookingSettings (
		BookingSettings $bookingSettings,
		bool $runValidation = true
	): bool {
		if ($bookingSettings->id)
		{
			$bookingSettingsRecord = BookingSettingsRecord::findOne(
				$bookingSettings->id
			);

			if (!$bookingSettingsRecord)
				throw new \Exception(\Craft::t(
					'bookings',
					'No booking settings exists with the ID "{id}"',
					['id' => $bookingSettings->id]
				));
		}
		else
		{
			$bookingSettingsRecord = new BookingSettingsRecord();
		}

		if ($runValidation && !$bookingSettings->validate())
		{
			\Craft::info(
				'Booking Settings not saved due to validation error.',
				__METHOD__
			);

			return false;
		}

		$bookingSettingsRecord->name = $bookingSettings->name;
		$bookingSettingsRecord->handle = $bookingSettings->handle;

		$db = \Craft::$app->db;
		$transaction = $db->beginTransaction();

		try {
			// Save the layout
			$fieldLayout = $bookingSettings->getFieldLayout();
			\Craft::$app->fields->saveLayout($fieldLayout);

			// Update layout ID on settings
			$bookingSettings->fieldLayoutId = $fieldLayout->id;
			$bookingSettingsRecord->fieldLayoutId = $fieldLayout->id;

			// Save
			$bookingSettingsRecord->save(false);

			// Save the record ID to the model
			if (!$bookingSettings->id)
				$bookingSettings->id = $bookingSettingsRecord->id;

			// Update the cache
			$this->_bookingSettingsById[$bookingSettings->id] = $bookingSettings;

			$transaction->commit();
		} catch (\Exception $e) {
			$transaction->rollBack();
			throw $e;
		}

		return true;
	}

	// Helpers
	// =========================================================================

	/**
	 * Returns a Query object prepped for retrieving booking settings.
	 *
	 * @return Query
	 */
	private function _createBookingSettingsQuery (): Query
	{
		return (new Query())
			->select([
				'id',
				'name',
				'handle',
				'fieldLayoutId',
			])
			->from([BookingSettingsRecord::$tableName]);
	}

}