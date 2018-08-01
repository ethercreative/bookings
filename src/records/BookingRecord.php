<?php
/**
 * Bookings plugin for Craft CMS 3.x
 *
 * @link      https://ethercreative.co.uk
 * @copyright Copyright (c) Ether Creative
 */

namespace ether\bookings\records;

use craft\db\ActiveRecord;
use craft\records\User;


/**
 * Class BookedBookingRecord
 *
 * @property int $id
 * @property int $status
 * @property string $number
 * @property int|null $userId
 * @property int|null $lineItemId
 * @property int|null $orderId
 * @property int|null $customerId
 * @property string $customerEmail
 * @property \DateTime $dateBooked
 * @property \DateTime|null $reservationExpiry
 * @property \DateTime $dateCreated
 *
 * @author  Ether Creative
 * @package ether\bookings\records
 * @since   1.0.0
 */
class BookingRecord extends ActiveRecord
{

	// Properties
	// =========================================================================

	public static $tableName = '{{%bookings_booking}}';

	// Methods
	// =========================================================================

	/**
	 * @return string
	 */
	public static function getTableName (): string
	{
		return self::$tableName;
	}

	public function getUser ()
	{
		return $this->hasOne(User::class, ['id' => 'userId']);
	}

	public function getLineItem ()
	{
		if (!class_exists(\craft\commerce\records\LineItem::class))
			return null;

		return $this->hasOne(\craft\commerce\records\LineItem::class, ['id' => 'lineItemId']);
	}

	public function getOrder ()
	{
		if (!class_exists(\craft\commerce\records\Order::class))
			return null;

		return $this->hasOne(\craft\commerce\records\Order::class, ['id' => 'orderId']);
	}

	public function getCustomer ()
	{
		if (!class_exists(\craft\commerce\records\Customer::class))
			return null;

		return $this->hasOne(\craft\commerce\records\Customer::class, ['id' => 'customerId']);
	}

}