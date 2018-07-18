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
use ether\bookings\Bookings;
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
	 * @param LineItemEvent $event
	 *
	 * @throws \Throwable
	 */
	public function onAddLineItem (LineItemEvent $event)
	{
		$craft = \Craft::$app;
		$bookingService = Bookings::getInstance()->booking;

		// Ensure this is a booking line item
		$book = $craft->request->getBodyParam('book');

		if (!$book)
			return;

		$book = $craft->security->validateData($book);

		if ($book === false)
		{
			$craft->session->setError('Book input is invalid.');
			\Craft::error(
				'Book input is invalid.',
				'bookings'
			);
			return;
		}

		$book = explode('_', $book);
		$elementId = $book[0];
		$fieldId = $book[1];

		$slotStart = $craft->request->getRequiredBodyParam('slotStart');
		$slotEnd = $craft->request->getBodyParam('slotEnd');

		/** @var LineItem $lineItem */
		$lineItem = $event->lineItem;

		/** @var Order $order */
		$order = $lineItem->order;

		/** @var bool $isNew */
		$isNew = $event->isNew;

		// Delete the previous booking (if we have one)
		if (!$isNew)
		{
			$booking = $bookingService->getBookingByOrderIdAndLineItemId(
				$order->id,
				$lineItem->id
			);

			if ($booking)
				$craft->elements->deleteElementById($booking->id);
		}

		// Create a new booking
		$bookingService->create([
			'fieldId'       => $fieldId,
			'elementId'     => $elementId,
			'subElementId'  => $lineItem->purchasableId,
			'userId'        => $order->user ? $order->user->id : null,
			'orderId'       => $order->id,
			'lineItemId'    => $lineItem->id,
			'customerId'    => $order->customerId,
			'customerEmail' => $order->email,
			'slotStart'     => $slotStart,
			'slotEnd'       => $slotEnd,
		]);
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

		$bookings = Bookings::getInstance()->booking->getBookingsByOrderId($order->id);

		foreach ($bookings as $booking)
			$booking->markAsComplete();
	}

}