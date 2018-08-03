<?php
/**
 * Bookings plugin for Craft CMS 3.x
 *
 * @link      https://ethercreative.co.uk
 * @copyright Copyright (c) Ether Creative
 */

namespace ether\bookings\services;

use craft\base\Component;
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
	 * @param Ticket    $ticket
	 * @param \DateTime $time
	 * @param int       $qty
	 *
	 * @return bool
	 */
	public function isTimeAvailable (Ticket $ticket, \DateTime $time, int $qty = 1)
	{
		// TODO: Take event multiplier into consideration

		$event = $ticket->getEvent();

		// Check that this slot has availability

		$slotsTakenInGivenTime = BookedSlotRecord::find()->andWhere([
			'ticketId' => $ticket->id,
			'date'     => $time->format(\DateTime::W3C),
		])->count();

		$slotsTakenInGivenTime += $qty;

		if (
			$slotsTakenInGivenTime > $ticket->capacity
		    || $slotsTakenInGivenTime > $event->capacity
		) return false;

		if ($ticket->maxQty !== null)
		{
			// Check that the event as a whole has availability

			$slotsTakenOverall = BookedSlotRecord::find()->andWhere([
				'ticketId' => $ticket->id,
			])->count();

			if ($slotsTakenOverall + $qty > $ticket->maxQty)
				return false;
		}

		return true;
	}

}