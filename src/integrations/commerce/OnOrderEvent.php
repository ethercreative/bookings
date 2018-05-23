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

	public function onAddLineItem (LineItemEvent $event)
	{
		/** @var LineItem $lineItem */
		$lineItem = $event->lineItem;

		/** @var Order $order */
		$order = $event->sender;

		/** @var bool $isNew */
		$isNew = $event->isNew;

		// TODO: If new and has BookingVariant field create a new Booking and set the reservationExpiry
	}

	public function onComplete (Event $event)
	{
		/** @var Order $order */
		$order = $event->sender;

		// TODO: Mark all bookings as booked
	}

}