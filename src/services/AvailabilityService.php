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
		$event = $ticket->getEvent();

		$bookedSlots = BookedSlotRecord::find()->andWhere([
			'eventId' => $event->id,
			'date'    => $time->format(\DateTime::W3C),
		])->select('ticketId, bookingId')->all();

		$bookedByBooking = ArrayHelper::groupBy(
			$bookedSlots,
			'bookingId'
		);

		// Check the event multiplier
		$uniqueBookings = count($bookedByBooking);
		if (
			$booking !== null &&
			array_key_exists($booking->id, $bookedByBooking)
		) $uniqueBookings--; // Exclude current booking
		if ($event->multiplier && $event->multiplier <= $uniqueBookings)
			return false;

		// Check the ticket capacity
		if (
			$booking !== null
			&& array_key_exists($booking->id, $bookedByBooking)
		) {
			if (
				($ticket->capacity && $ticket->capacity < $qty)
			    || ($event->capacity && $event->capacity < $qty)
			) return false;
		}

		// Check the event capacity
		if ($event->capacity && $event->capacity * ($event->multiplier ?: 1) < count($bookedSlots) + $qty)
			return false;

		return true;
	}

}