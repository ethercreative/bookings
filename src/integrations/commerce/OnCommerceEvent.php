<?php
/**
 * Bookings plugin for Craft CMS 3.x
 *
 * @link      https://ethercreative.co.uk
 * @copyright Copyright (c) Ether Creative
 */

namespace ether\bookings\integrations\commerce;

use craft\commerce\elements\Order;
use craft\commerce\events\LineItemEvent;
use craft\commerce\events\RefundTransactionEvent;
use craft\commerce\models\LineItem;
use craft\helpers\Db;
use ether\bookings\Bookings;
use ether\bookings\elements\BookedTicket;
use ether\bookings\elements\Booking;
use ether\bookings\helpers\DateHelper;
use ether\bookings\records\BookedSlotRecord;
use ether\bookings\services\TicketsService;
use yii\base\Event;


/**
 * Class OnCommerceEvent
 *
 * @author  Ether Creative
 * @package ether\bookings\integrations\commerce
 * @since   1.0.0
 */
class OnCommerceEvent
{

	// Orders
	// =========================================================================

	/**
	 * @param Event $event
	 *
	 * @throws \Exception
	 */
	public function onBeforeSaveLineItem (Event $event)
	{
		$bookings = Bookings::getInstance();

		/** @var LineItem $lineItem */
		$lineItem = $event->sender;

		/** @var Order $order */
		$order = $lineItem->order;

//		/** @var bool $isNew */
//		$isNew = !$lineItem->id;

		$options = $lineItem->getOptions();

		// Ensure this is a booking line item
		$ticketId =
			array_key_exists('ticketId', $options)
				? $options['ticketId']
				: false;

		if (!$ticketId)
			return;

		if (is_array($options['ticketDate']))
		{
			$startDate = DateHelper::parseDateFromPost($options['ticketDate']['start']);
			$endDate   = DateHelper::parseDateFromPost($options['ticketDate']['end']);
		}
		else
		{
			$startDate = DateHelper::parseDateFromPost($options['ticketDate']);
			$endDate   = null;
		}

		$ticket = $bookings->tickets->getTicketById($ticketId);

		// Does the ticket exist?
		if ($ticket === null)
		{
			$err = \Craft::t('bookings', 'Unable to find ticket for the given ID.');

			$order->addError('ticket', $err);
			$lineItem->addError('ticket', $err);

			return;
		}

		$event = $ticket->getEvent();

		// Is time valid?
		if ($event->isRangeOccurrence($startDate, $endDate) === false)
		{
			$err = \Craft::t('bookings', 'Selected Date / Time is invalid.');

			$order->addError('ticket', $err);
			$lineItem->addError($ticket->getField()->handle, $err);

			return;
		}

		// Do we have an existing booking?
		$booking = $bookings->bookings->getBookingByOrderEventAndSlot(
			$order->id,
			$event->id,
			$startDate
		);

		// Is time available?
		$qty = $lineItem->qty;

		if ($qty > 0 && $bookings->availability->isRangeAvailable($booking, $ticket, $startDate, $endDate, $qty) === false)
		{
			$err = \Craft::t(
				'bookings',
				$qty > 1
					? 'Selected Date / Time is unavailable at that quantity.'
					: 'Selected Date / Time is unavailable.'
			);

			$order->addError('ticket', $err);
			$lineItem->addError($ticket->getField()->handle, $err);

			return;
		}
	}

	/**
	 * @param LineItemEvent $lineItemEvent
	 *
	 * @throws \Throwable
	 */
	public function onAfterSaveLineItem (LineItemEvent $lineItemEvent)
	{
		$craft = \Craft::$app;
		$bookings = Bookings::getInstance();

		/** @var LineItem $lineItem */
		$lineItem = $lineItemEvent->lineItem;

		/** @var Order $order */
		$order = $lineItem->order;

		/** @var bool $isNew */
		$isNew = $lineItemEvent->isNew;

		$options = $lineItem->getOptions();

		// Ensure this is a booking line item
		$ticketId =
			array_key_exists('ticketId', $options)
				? $options['ticketId']
				: false;

		if (!$ticketId)
			return;

		if (is_array($options['ticketDate']))
		{
			$startDate = DateHelper::parseDateFromPost($options['ticketDate']['start']);
			$endDate   = DateHelper::parseDateFromPost($options['ticketDate']['end']);
		}
		else
		{
			$startDate = DateHelper::parseDateFromPost($options['ticketDate']);
			$endDate   = null;
		}

		$ticket = $bookings->tickets->getTicketById($ticketId);

		$event = $ticket->getEvent();

		// Do we have an existing booking?
		$booking = $bookings->bookings->getBookingByOrderEventAndSlot(
			$order->id,
			$event->id,
			$startDate
		);

		// Create a new booking if one doesn't exist
		if ($booking === null)
		{
			$booking = new Booking();

			$booking->status        = Booking::STATUS_RESERVED;
			$booking->eventId       = $event->id;
			$booking->orderId       = $order->id;
			$booking->customerId    = $order->customerId;
			$booking->customerEmail = $order->email;
			$booking->slot          = $startDate;

			$craft->elements->saveElement($booking);
		}

		// Create the booked tickets
		$bookedTickets = [];

		$i = (int) $lineItem->qty;

		// Find any existing tickets to update
		if ($isNew === false)
		{
			$bookedTickets = BookedTicket::findAll([
				'ticketId'   => $ticket->id,
				'bookingId'  => $booking->id,
				'lineItemId' => $lineItem->id,
				'startDate'  => $startDate,
				'endDate'    => $endDate,
			]);
		}

		$previousTicketCount = count($bookedTickets);

		if ($previousTicketCount > $i)
		{
			while ($previousTicketCount > $i)
			{
				$previousTicketCount--;
				$craft->elements->deleteElement($bookedTickets[$previousTicketCount]);
				unset($bookedTickets[$previousTicketCount]);
			}
		}

		$i -= $previousTicketCount;
		
		while ($i-- > 0)
		{
			$bookedTicket = new BookedTicket();

			$bookedTicket->ticketId      = $ticket->id;
			$bookedTicket->bookingId     = $booking->id;
			$bookedTicket->lineItemId    = $lineItem->id;
			$bookedTicket->startDate     = $startDate;
			$bookedTicket->endDate       = $endDate;
			$bookedTicket->fieldLayoutId = $ticket->fieldLayoutId;

			$bookings->tickets->saveBookedTicket($bookedTicket);
			$bookedTickets[] = $bookedTicket;
		}

		// Delete old slots
		$oldSlots = BookedSlotRecord::findAll([
			'eventId' => $event->id,
			'ticketId' => $ticket->id,
			'bookingId' => $booking->id,
		]);

		/** @var BookedSlotRecord $slot */
		foreach ($oldSlots as $slot)
			$slot->delete();

		// Create the slots for this ticket
		$slots = $bookings->slots->generateSlotsForGivenTimes($event, $startDate, $endDate);
		$slotsCount = count($slots);

		foreach ($bookedTickets as $bookedTicket)
		{
			$i = 0;

			foreach ($slots as $slot)
			{
				$bookedSlot = new BookedSlotRecord();

				$bookedSlot->start = $i === 0;
				$bookedSlot->end = ++$i === $slotsCount;
				$bookedSlot->eventId = $event->id;
				$bookedSlot->ticketId = $ticket->id;
				$bookedSlot->bookingId = $booking->id;
				$bookedSlot->bookedTicketId = $bookedTicket->id;
				$bookedSlot->date = Db::prepareDateForDb($slot);

				$bookedSlot->save();
			}
		}
	}

	/**
	 * @param Event $event
	 *
	 * @throws \Throwable
	 */
	public function onComplete (Event $event)
	{
		/** @var Order $order */
		$order = $event->sender;

		$bookings = Bookings::getInstance()->bookings->getBookingsByOrderId($order->id);

		/** @var Booking $booking */
		foreach ($bookings as $booking)
			$booking->markAsComplete();
	}

	// Payments
	// =========================================================================

	public function onRefund (RefundTransactionEvent $event)
	{
		$order = $event->transaction->order;

		$bookings = Bookings::getInstance()->bookings->getBookingsByOrderId($order->id);

		/** @var Booking $booking */
		foreach ($bookings as $booking)
			$booking->expireBooking();
	}

}
