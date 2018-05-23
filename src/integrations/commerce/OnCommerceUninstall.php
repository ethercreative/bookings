<?php
/**
 * Bookings plugin for Craft CMS 3.x
 *
 * @link      https://ethercreative.co.uk
 * @copyright Copyright (c) Ether Creative
 */

namespace ether\bookings\integrations\commerce;

use ether\bookings\records\BookingRecord;


/**
 * Class OnCommerceUninstall
 *
 * @author  Ether Creative
 * @package ether\bookings\integrations\commerce
 * @since   1.0.0
 */
class OnCommerceUninstall
{

	/**
	 * OnCommerceUninstall constructor.
	 *
	 * @throws \yii\db\Exception
	 */
	public function __construct ()
	{
		$this->_removeCommerceBookings();
		$this->_removeForeignKeysFromBookingsTable();
	}

	private function _removeCommerceBookings ()
	{
		BookingRecord::deleteAll('orderId IS NOT NULL');
	}

	/**
	 * @throws \yii\db\Exception
	 */
	private function _removeForeignKeysFromBookingsTable ()
	{
		$db = \Craft::$app->db;

		$db->createCommand()->dropForeignKey(
			$db->getForeignKeyName(BookingRecord::$tableName, 'orderId'),
			BookingRecord::$tableName
		)->execute();

		$db->createCommand()->dropForeignKey(
			$db->getForeignKeyName(BookingRecord::$tableName, 'customerId'),
			BookingRecord::$tableName
		)->execute();
	}

}