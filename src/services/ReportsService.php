<?php
/**
 * Bookings plugin for Craft CMS 3.x
 *
 * @link      https://ethercreative.co.uk
 * @copyright Copyright (c) 2018 Ether Creative
 */

namespace ether\bookings\services;

use craft\base\Component;
use craft\base\Field;
use craft\db\Query;
use ether\bookings\Bookings;
use ether\bookings\elements\Booking;
use ether\bookings\models\Ticket;
use ether\bookings\records\BookedSlotRecord;
use ether\bookings\records\BookingRecord;
use ether\bookings\records\EventRecord;

/**
 * Class ReportsService
 *
 * @author  Ether Creative
 * @package ether\bookings\services
 */
class ReportsService extends Component
{

	public function allSlotsForEvent ($eventId, $slot = null)
	{
		$eventRecord = EventRecord::findOne(['id' => $eventId]);
		$tickets = Ticket::fromRecords($eventRecord->getTickets()->all());

		$select = [
			'slots.[[bookingId]]',
			'slots.[[date]] as slot',
			'bookings.[[orderId]]',
			'bookings.[[dateBooked]]',
			'orders.[[email]]',
			'CONCAT(addresses.[[firstName]], \' \', addresses.[[lastName]]) as name',
			'lineItems.[[salePrice]] as price',
			'TRIM(BOTH \'"\' FROM JSON_EXTRACT(lineItems.[[snapshot]], \'$.title\')) as ticket',
		];

		$prefix = \Craft::$app->fields->oldFieldColumnPrefix;

		foreach ($tickets as $ticket)
			foreach ($ticket->getFieldsFromLayout() as $field)
				if ($field::hasContentColumn())
					$select[] = 'content.[[' . $prefix . $field->handle . ']]';

		$slots = (new Query())
			->select($select)
			->from(BookedSlotRecord::$tableName . ' slots')
			->where([
				'slots.[[eventId]]' => $eventId,
				'bookings.[[status]]' => Booking::STATUS_COMPLETED,
			]);

		if ($slot)
			$slots = $slots->andWhere(['slots.[[date]]' => $slot]);

		$slots = $slots
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
			->leftJoin(
				'{{%content}} content',
				'content.[[elementId]] = slots.[[bookedTicketId]]'
			)
			->leftJoin(
				'{{%bookings_booked_tickets}} bookedTickets',
				'bookedTickets.[[id]] = slots.[[bookedTicketId]]'
			)
			->leftJoin(
				'{{%commerce_lineitems}} lineItems',
				'lineItems.[[id]] = bookedTickets.[[lineItemId]]'
			)
			->orderBy('slots.[[date]] ASC, orders.[[email]] ASC')
			->all();

		return $slots;
	}

}

