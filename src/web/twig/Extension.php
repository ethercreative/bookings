<?php
/**
 * Bookings plugin for Craft CMS 3.x
 *
 * @link      https://ethercreative.co.uk
 * @copyright Copyright (c) Ether Creative
 */

namespace ether\bookings\web\twig;

use craft\commerce\models\LineItem;
use ether\bookings\elements\BookedTicket;
use ether\bookings\elements\Booking;
use ether\bookings\enums\EventType;


/**
 * Class Extension
 *
 * @author  Ether Creative
 * @package ether\bookings\web\twig
 * @since   1.0.0
 */
class Extension extends \Twig_Extension implements \Twig_Extension_GlobalsInterface
{

	// Twig
	// =========================================================================

	public function getGlobals ()
	{
		return [
			'BOOKING_RESERVED'  => Booking::STATUS_RESERVED,
			'BOOKING_COMPLETED' => Booking::STATUS_COMPLETED,
			'BOOKING_EXPIRED'   => Booking::STATUS_EXPIRED,

			'BOOKABLE_FIXED'    => EventType::FIXED,
			'BOOKABLE_FLEXIBLE' => EventType::FLEXIBLE,
		];
	}

	public function getFunctions ()
	{
		return [
			new \Twig_Function('getTickets', [$this, 'getTickets']),
//			new \Twig_Function('placeBookingInput', [$this, 'placeBookingInput']),
//			new \Twig_Function('confirmBookingInput', [$this, 'confirmBookingInput']),
		];
	}

	// Functions
	// =========================================================================

	/**
	 * Get's booked tickets for the given line item
	 *
	 * @param LineItem $lineItem
	 *
	 * @return array
	 */
	public function getTickets (LineItem $lineItem)
	{
		if (!$lineItem->id)
			return [];

		return BookedTicket::findAll([
			'lineItemId' => $lineItem->id,
		]);
	}

	/**
	 * {{ placeBookingInput(entry.bookableField) }}
	 *
	 * @param BookableEvent $bookable
	 *
	 * @return string|\Twig_Markup
	 * @throws \yii\base\Exception
	 * @throws \yii\base\InvalidConfigException
	 */
//	public function placeBookingInput (BookableEvent $bookable)
//	{
//		if (!$bookable->enabled)
//			return "";
//
//		$value = $bookable->ownerId;
//		$value .= '_' . $bookable->id;
//		$value = \Craft::$app->security->hashData($value);
//
//		return Template::raw('<input type="hidden" name="book" value="' . $value . '" />');
//	}

	/**
	 * {{ confirmBookingInput(booking) }}
	 *
	 * @param Booking $booking
	 *
	 * @return string|\Twig_Markup
	 * @throws \yii\base\Exception
	 * @throws \yii\base\InvalidConfigException
	 */
//	public function confirmBookingInput (Booking $booking)
//	{
//		if (!$booking->bookable->enabled)
//			return "";
//
//		$value = \Craft::$app->security->hashData($booking->id);
//
//		return Template::raw('<input type="hidden" name="booking" value="' . $value . '" />');
//	}

}