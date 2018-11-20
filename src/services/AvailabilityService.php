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

}