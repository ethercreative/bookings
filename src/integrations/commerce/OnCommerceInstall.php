<?php
/**
 * Bookings plugin for Craft CMS 3.x
 *
 * @link      https://ethercreative.co.uk
 * @copyright Copyright (c) Ether Creative
 */

namespace ether\bookings\integrations\commerce;

use ether\bookings\records\BookedTicketRecord;
use ether\bookings\records\BookingRecord;


/**
 * Class OnCommerceInstall
 *
 * @author  Ether Creative
 * @package ether\bookings
 * @since   1.0.0
 */
class OnCommerceInstall
{

	/**
	 * OnCommerceInstall constructor.
	 *
	 * @throws \yii\db\Exception
	 */
	public function __construct ()
	{
		$this->_addForeignKeysToBookingsTable();
	}

	/**
	 * Add necessary foreign keys to the bookings table
	 *
	 * @throws \yii\db\Exception
	 */
	private function _addForeignKeysToBookingsTable ()
	{
		$db = \Craft::$app->db;

		$db->createCommand()->addForeignKey(
			$db->getForeignKeyName(BookingRecord::$tableName, 'orderId'),
			BookingRecord::$tableName,
			'orderId',
			'{{%commerce_orders}}',
			'id',
			'CASCADE',
			null
		)->execute();

		$db->createCommand()->addForeignKey(
			$db->getForeignKeyName(BookingRecord::$tableName, 'customerId'),
			BookingRecord::$tableName,
			'customerId',
			'{{%commerce_customers}}',
			'id',
			'SET NULL',
			null
		)->execute();

		$db->createCommand()->addForeignKey(
			$db->getForeignKeyName(BookedTicketRecord::$tableName, 'lineItemId'),
			BookedTicketRecord::$tableName,
			'lineItemId',
			'{{%commerce_lineitems}}',
			'id',
			'CASCADE',
			null
		)->execute();
	}

}
