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


/**
 * Class BookingService
 *
 * @author  Ether Creative
 * @package ether\bookings\services
 * @since   1.0.0
 */
class BookingService extends Component
{

	/**
	 * @param array $properties - The properties to set on the booking
	 *
	 * @return Booking|null
	 * @throws \Throwable
	 * @throws \craft\errors\ElementNotFoundException
	 * @throws \yii\base\Exception
	 */
	public function create ($properties)
	{
		$booking = new Booking();

		$booking->number = $this->generateBookingNumber();

		// TODO: Set reservationExpiry

		foreach ($properties as $key => $val)
			if (property_exists($booking, $key))
				$booking->{$key} = $val;

		if (\Craft::$app->elements->saveElement($booking))
			return $booking;

		return null;
	}

	public function generateBookingNumber ()
	{
		return md5(uniqid(mt_rand(), true));
	}

}