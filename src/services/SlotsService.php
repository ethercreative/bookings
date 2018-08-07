<?php
/**
 * Bookings plugin for Craft CMS 3.x
 *
 * @link      https://ethercreative.co.uk
 * @copyright Copyright (c) Ether Creative
 */

namespace ether\bookings\services;

use craft\base\Component;
use ether\bookings\elements\BookedTicket;
use ether\bookings\models\Event;
use ether\bookings\records\BookedSlotRecord;


/**
 * Class SlotsService
 *
 * @author  Ether Creative
 * @package ether\bookings\services
 * @since   1.0.0
 */
class SlotsService extends Component
{

	/**
	 * @param Event          $event
	 * @param \DateTime      $start
	 * @param \DateTime|null $end
	 *
	 * @return \DateTime[]|\RRule\RSet
	 */
	public function generateSlotsForGivenTimes (Event $event, \DateTime $start, \DateTime $end = null)
	{
		if ($end === null)
			return [$start];

		return $event->getSlotsInRangeAsIterable($start, $end);
	}

	/**
	 * Deletes all slots on the given ticket
	 *
	 * @param BookedTicket $ticket
	 */
	public function clearSlotsFromTicket (BookedTicket $ticket)
	{
		BookedSlotRecord::deleteAll([
			'bookedTicketId' => $ticket->id,
		]);
	}

}