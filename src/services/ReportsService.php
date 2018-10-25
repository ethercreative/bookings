<?php
/**
 * Bookings plugin for Craft CMS 3.x
 *
 * @link      https://ethercreative.co.uk
 * @copyright Copyright (c) 2018 Ether Creative
 */

namespace ether\bookings\services;

use craft\base\Component;
use craft\db\Query;
use ether\bookings\elements\Booking;
use ether\bookings\records\BookedSlotRecord;
use ether\bookings\records\BookingRecord;

/**
 * Class ReportsService
 *
 * @author  Ether Creative
 * @package ether\bookings\services
 */
class ReportsService extends Component
{

	public function allSlotsForEvent ($eventId)
	{
		$slots = (new Query())
			->select([
				'slots.[[bookingId]]',
				'slots.[[date]] as slot',
				'bookings.[[orderId]]',
				'bookings.[[dateBooked]]',
				'orders.[[email]]',
				'CONCAT(addresses.[[firstName]], \' \', addresses.[[lastName]]) as name',
			])
			->from(BookedSlotRecord::$tableName . ' slots')
			->where([
				'slots.[[eventId]]' => $eventId,
				'bookings.[[status]]' => Booking::STATUS_COMPLETED,
			])
			->leftJoin(
				BookingRecord::$tableName . ' bookings',
				'slots.[[bookingId]] = bookings.[[id]]'
			)
			->leftJoin(
				'{{%commerce_orders}} orders',
				'bookings.[[orderId]] = orders.[[id]]'
			)
			->leftJoin(
				'{{%commerce_addresses}} addresses',
				'orders.[[billingAddressId]] = addresses.[[id]]'
			)
			->orderBy('slots.[[date]] ASC, orders.[[email]] ASC')
			->all();

		return $slots;
	}

}