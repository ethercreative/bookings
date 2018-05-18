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
	public function getOrderSettingById($bookingSettingsId)
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

	// TODO: https://github.com/craftcms/commerce/blob/develop/src/services/OrderSettings.php
	// TODO: Finish making bookingsettings
	// TODO: Down the line, each field will have its own booking settings, that will extend from the default

	// Helpers
	// =========================================================================

	/**
	 * Returns a Query object prepped for retrieving booking settings.
	 *
	 * @return Query
	 */
	private function _createBookingSettingsQuery(): Query
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