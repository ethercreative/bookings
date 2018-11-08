<?php
/**
 * Bookings plugin for Craft CMS 3.x
 *
 * @link      https://ethercreative.co.uk
 * @copyright Copyright (c) Ether Creative
 */

namespace ether\bookings\services;

use craft\base\Component;
use craft\db\Query;
use craft\helpers\DateTimeHelper;
use craft\helpers\Db;
use ether\bookings\elements\BookedTicket;
use ether\bookings\elements\Booking;
use ether\bookings\helpers\DateHelper;
use ether\bookings\models\Event;
use ether\bookings\models\Ticket;
use ether\bookings\records\BookingRecord;
use ether\bookings\records\TicketRecord;


/**
 * Class TicketsService
 *
 * @author  Ether Creative
 * @package ether\bookings\services
 * @since   1.0.0
 */
class TicketsService extends Component
{

	// Properties
	// =========================================================================

	public static $previousTicketDate = null;

	// Methods
	// =========================================================================

	/**
	 * Finds the ticket with the given ID
	 *
	 * @param $ticketId
	 *
	 * @return Ticket|null
	 */
	public function getTicketById ($ticketId)
	{
		$record = TicketRecord::findOne([
			'id' => $ticketId,
		]);

		if (!$record)
			return null;

		return Ticket::fromRecord($record);
	}

	/**
	 * Finds the booked ticket with the given ID
	 *
	 * @param $bookedTicketId
	 *
	 * @return \craft\base\ElementInterface|null|BookedTicket
	 */
	public function getBookedTicketById ($bookedTicketId)
	{
		return BookedTicket::find()->id($bookedTicketId)->one();
	}

	/**
	 * Updates the slot of the given BookedTicket
	 *
	 * @param BookedTicket $ticket
	 * @param \DateTime    $newSlot
	 *
	 * @return array
	 * @throws \Throwable
	 */
	public function updateTicketSlot (BookedTicket $ticket, \DateTime $newSlot)
	{
		$lineItem = $ticket->getLineItem();
		$opts = $lineItem->getOptions();

		self::$previousTicketDate = $opts['ticketDate'];

		// TODO: All tickets on a booking need to be updated at once, and share at datetime
		// 0. Move this into BookingsService
		// 1. Update the booking slot to the new time
		// 2. For each ticket update its line item (skipping duplicates)

		$opts['ticketDate'] = $newSlot->format('c');
		$lineItem->setOptions($opts);

		if (!\craft\commerce\Plugin::getInstance()->lineItems->saveLineItem($lineItem))
			return $lineItem->getErrors();

		self::$previousTicketDate = null;

		return [];
	}

}