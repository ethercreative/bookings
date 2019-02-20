<?php
/**
 * Bookings plugin for Craft CMS 3.x
 *
 * @link      https://ethercreative.co.uk
 * @copyright Copyright (c) Ether Creative
 */

namespace ether\bookings\services;

use craft\base\Component;
use ether\bookings\elements\Booking;
use ether\bookings\helpers\ArrayHelper;
use ether\bookings\models\Ticket;
use ether\bookings\records\BookedSlotRecord;


/**
 * Class AvailabilityService
 *
 * @author  Ether Creative
 * @package ether\bookings\services
 * @since   1.0.0
 */
class AvailabilityService extends Component
{

	/**
	 * Returns true if the given time is available for the given ticket
	 *
	 * @param Booking|null $booking
	 * @param Ticket       $ticket
	 * @param \DateTime    $time
	 * @param int          $qty
	 *
	 * @return bool
	 */
	public function isTimeAvailable (
		$booking, Ticket $ticket, \DateTime $time, int $qty = 1
	): bool {
		// Check the ticket capacity
		if (!!$ticket->capacity && $ticket->capacity < $qty)
		{
			\Craft::info('No ticket capacity', 'bookings');
			return false;
		}

		// Get the Event
		$event = $ticket->getEvent();

		// Get all currently booked slots for this event & slot time
		$bookedSlots = BookedSlotRecord::find()
			->select('ticketId, bookingId')
			->where([
				'eventId' => $event->id,
				'date'    => $time->format(\DateTime::W3C),
			]);

		if ($booking !== null && $booking->id)
			$bookedSlots->andWhere(['!=', 'bookingId', $booking->id]);

		$bookedSlots = $bookedSlots->all();

		// Check the event capacity
		if (!!$event->capacity && $event->capacity < count($bookedSlots) + $qty)
		{
			\Craft::info('No event capacity', 'bookings');
			return false;
		}

		// Check the multiplier
		$bookedByBookingCount = count(ArrayHelper::groupBy(
			$bookedSlots,
			'bookingId'
		));

		if (!!$event->multiplier && $event->multiplier <= $bookedByBookingCount)
		{
			\Craft::info('No multiplier capacity', 'bookings');
			return false;
		}

		return true;
	}

	public function isRangeAvailable (
		$booking,
		Ticket $ticket,
		\DateTime $start,
		\DateTime $end = null,
		int $qty = 1
	) {
		if ($end === null)
			return $this->isTimeAvailable($booking, $ticket, $start, $qty);

		// Check the qty
		if ($qty > 1)
		{
			\Craft::info('Quantity is greater than 1', 'bookings');
			return false;
		}

		$event = $ticket->getEvent();

		// Check the event capacity
		if (!!$event->capacity && $event->capacity < $qty)
		{
			\Craft::info('No event capacity', 'bookings');
			return false;
		}

		// Get all booked slots for this event and ticket in this range
		// (excluding the current booking)
		$bookedSlots = BookedSlotRecord::find()
			->select(['date', 'count(*) as count'])
			->where([
				'eventId' => $event->id,
				'ticketId' => $ticket->id,
			])
			->andWhere(['>=', 'date', $start->format(\DateTime::W3C)])
			->andWhere(['<=', 'date', $end->format(\DateTime::W3C)])
			->groupBy('date');

		if ($booking !== null && $booking->id)
			$bookedSlots->andWhere(['!=', 'bookingId', $booking->id]);

		$bookedSlots = ArrayHelper::map(
			$bookedSlots->createCommand()->queryAll(),
			'date',
			'count'
		);

		// If there aren't any other bookings, the range is available
		if (empty($bookedSlots))
			return true;

		// Ensure we have available capacity
		// NOTE: Capacity should always be 1 on both ticket and event when
		// using flexible bookings
		$requiredCapacity = $event->multiplier - $qty;

		if ($requiredCapacity <= 0)
			return false;

		foreach ($bookedSlots as $bookedCapacity)
			if ($bookedCapacity > $requiredCapacity)
				return false;

		return true;
	}

}
