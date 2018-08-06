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
use craft\commerce\models\LineItem;
use craft\helpers\Db;
use ether\bookings\Bookings;
use ether\bookings\elements\BookedTicket;
use ether\bookings\elements\Booking;
use \craft\commerce\records\LineItem as LineItemRecord;
use ether\bookings\helpers\DateHelper;
use ether\bookings\records\BookedSlotRecord;
use yii\base\Event;


/**
 * Class OnOrderComplete
 *
 * @author  Ether Creative
 * @package ether\bookings\integrations\commerce
 * @since   1.0.0
 */
class OnOrderEvent
{

	/**
	 * @param Event $event
	 */
	public function onBeforeSaveLineItem (Event $event)
	{
		$bookings = Bookings::getInstance();

		/** @var LineItem $lineItem */
		$lineItem = $event->sender;

		/** @var Order $order */
		$order = $lineItem->order;

		/** @var bool $isNew */
		$isNew = !$lineItem->id;

		$options = $lineItem->getOptions();

		// Ensure this is a booking line item
		$ticketId = $options['ticketId'];

		if (!$ticketId)
			return;

		$startDate = DateHelper::parseDateFromPost($options['ticketDate']);
		// TODO: Date ranges
		$endDate = null;

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
		// TODO: Date ranges
		if ($event->isDateOccurrence($startDate) === false)
		{
			$err = \Craft::t('bookings', 'Selected Date / Time is invalid.');

			$order->addError('ticket', $err);
			$lineItem->addError($ticket->getField()->handle, $err);

			return;
		}

		// Is time available?
		// TODO: Date ranges
		$qty = $lineItem->qty;

		if ($isNew === false)
		{
			// If we're updating a line item, we'll only want to check
			// availability against an increase in qty
			$qty -= LineItemRecord::findOne([
				'id' => $lineItem->id,
			])->qty;
		}

		if ($qty > 0 && $bookings->availability->isTimeAvailable($ticket, $startDate, $qty) === false)
		{
			$err = \Craft::t(
				'bookings',
				$lineItem->qty > 1
					? 'Selected Date / Time is unavailable.'
					: 'Selected Date / Time is unavailable at that quantity.'
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
		$ticketId = $options['ticketId'];

		if (!$ticketId)
			return;

		$startDate = DateHelper::parseDateFromPost($options['ticketDate']);
		// TODO: Date ranges
		$endDate = null;
//		$endDate = $craft->request->getBodyParam('ticketEndDate');

		$ticket = $bookings->tickets->getTicketById($ticketId);

		$event = $ticket->getEvent();

		// Do we have an existing booking?
		$booking = $bookings->bookings->getBookingByOrderAndEventIds(
			$order->id,
			$event->id
		);

		// Create a new booking if one doesn't exist
		if ($booking === null)
		{
			$booking = new Booking();

			$booking->status     = Booking::STATUS_RESERVED;
			$booking->eventId    = $event->id;
			$booking->orderId    = $order->id;
			$booking->customerId = $order->customerId;

			$craft->elements->saveElement($booking);
		}

		// Clear any existing booked tickets (will Cascade to slots) if we're updating
		if ($isNew === false)
		{
			$bookedTickets = BookedTicket::findAll([
				'ticketId'   => $ticket->id,
				'bookingId'  => $booking->id,
				'lineItemId' => $lineItem->id,
				'startDate'  => $startDate,
				'endDate'    => $endDate,
			]);

			foreach ($bookedTickets as $bookedTicket)
				$craft->elements->deleteElement($bookedTicket);
		}

		// Create the booked tickets
		$bookedTickets = [];

		$i = $lineItem->qty;
		while ($i--)
		{
			$bookedTicket = new BookedTicket();

			$bookedTicket->ticketId = $ticket->id;
			$bookedTicket->bookingId = $booking->id;
			$bookedTicket->lineItemId = $lineItem->id;
			$bookedTicket->startDate = $startDate;
			$bookedTicket->endDate = $endDate;

			$craft->elements->saveElement($bookedTicket);
			$bookedTickets[] = $bookedTicket;
		}

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

}