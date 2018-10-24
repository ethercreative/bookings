<?php
/**
 * Bookings plugin for Craft CMS 3.x
 *
 * @link      https://ethercreative.co.uk
 * @copyright Copyright (c) Ether Creative
 */

namespace ether\bookings\services;

use craft\base\Component;
use craft\helpers\Db;
use ether\bookings\Bookings;
use ether\bookings\elements\Booking;
use ether\bookings\elements\db\BookingQuery;


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
	 * @param int       $orderId
	 * @param int       $eventId
	 * @param \DateTime $slot
	 *
	 * @return array|\craft\base\ElementInterface|null|Booking
	 */
	public function getBookingByOrderEventAndSlot ($orderId, $eventId, $slot)
	{
		return Booking::find()->andWhere([
			'orderId' => $orderId,
			'eventId' => $eventId,
			'slot'    => Db::prepareDateForDb($slot->setTimezone(new \DateTimeZone('UTC'))),
		])->one();
	}

	/**
	 * @param int            $eventId
	 * @param \DateTime|null $slot
	 * @param int|null       $offset
	 *
	 * @return \craft\base\ElementInterface[]
	 */
	public function getBookingsByEventIdAndSlot ($eventId, \DateTime $slot = null, $offset = 0)
	{
		/** @var BookingQuery $query */
		$query = Booking::find();
		$query = $query->event($eventId);

		if ($slot !== null)
		{
			$query = $query->slot(
				Db::prepareDateForDb(
					$slot->setTimezone(new \DateTimeZone('UTC'))
				)
			);
		}

		\Craft::dd($query->limit(100)->getRawSql());

		return $query->limit(100)->offset($offset)->all();
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

	/**
	 * @param $bookingId
	 *
	 * @return \craft\base\Element|Booking|Booking[]|null
	 */
	public function getBookingById ($bookingId)
	{
		return Booking::findOne($bookingId);
	}

	/**
	 * @param bool $force
	 *
	 * @throws \Throwable
	 */
	public function clearExpiredBookings (bool $force = false)
	{
		$settings = Bookings::getInstance()->settings;

		$where = [
			'and',
			'[[status]] != ' . Booking::STATUS_COMPLETED,
		];

		if (!$force)
		{
			$since = new \DateTime('now', new \DateTimeZone('UTC'));
			$since->setTimestamp(
				time() - ($settings->expiryDuration + $settings->clearExpiredDuration)
			);
			$since = '\'' . $since->format(\DateTime::W3C) . '\'';

			$where[] = '[[reservationExpiry]] < ' . $since;
		}

		$expiredBookings = Booking::find()->where($where)->all();

		foreach ($expiredBookings as $booking)
			\Craft::$app->elements->deleteElement($booking);
	}

}