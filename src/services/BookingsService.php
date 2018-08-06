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
 * Class BookingsService
 *
 * @author  Ether Creative
 * @package ether\bookings\services
 * @since   1.0.0
 */
class BookingsService extends Component
{

	/**
	 * @param $orderId
	 * @param $eventId
	 *
	 * @return array|\craft\base\ElementInterface|null|Booking
	 */
	public function getBookingByOrderAndEventIds ($orderId, $eventId)
	{
		return Booking::find()->andWhere([
			'orderId' => $orderId,
			'eventId' => $eventId,
		])->one();
	}

	/**
	 * @param $orderId
	 *
	 * @return \craft\base\ElementInterface[]
	 */
	public function getBookingsByOrderId ($orderId)
	{
		return Booking::find()->andWhere([
			'orderId' => $orderId,
		])->all();
	}

	public function getBookingById ($bookingId)
	{
		return Booking::findOne($bookingId);
	}

}