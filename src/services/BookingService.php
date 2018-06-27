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
use ether\bookings\records\BookingRecord;


/**
 * Class BookingService
 *
 * @author  Ether Creative
 * @package ether\bookings\services
 * @since   1.0.0
 */
class BookingService extends Component
{

	// Public
	// =========================================================================

	/**
	 * @param array $properties - The properties to set on the booking
	 *
	 * @return Booking
	 * @throws \Throwable
	 * @throws \craft\errors\ElementNotFoundException
	 * @throws \yii\base\Exception
	 */
	public function create ($properties)
	{
		$booking = new Booking();

		$booking->number = $this->_generateBookingNumber();

		foreach ($properties as $key => $val)
			if (property_exists($booking, $key))
				$booking->{$key} = $val;

		\Craft::$app->elements->saveElement($booking);

		return $booking;
	}

	/**
	 * Ensures the given slot times do not conflict with existing slots
	 *
	 * @param \DateTime|string      $start
	 * @param \DateTime|string|null $end
	 *
	 * @return bool
	 * @throws \yii\db\Exception
	 */
	public function validateSlot ($start, $end = null)
	{
		if (!$start instanceof \DateTime)
			$start = new \DateTime($start);

		if ($end && !$end instanceof \DateTime)
			$end = new \DateTime($end);

		$start = $start->getTimestamp();

		if ($end)
			$end = $end->getTimestamp();

		$db = \Craft::$app->db;
		$bookingsTable = BookingRecord::$tableName;

		// 1. Check start date
		$startQuery = <<<SQL
SELECT count(*) FROM "$bookingsTable"
WHERE "slotStart" = '$start'
LIMIT 1
SQL;
		$startConflicting = (bool) $db->createCommand($startQuery)->queryScalar();

		if ($startConflicting)
			return \Craft::t('bookings', 'Slot Start is unavailable.');

		if (!$end)
			return true;

		// 2. Ensure end date comes after start
		if ($end < $start)
			return \Craft::t('bookings', 'Slot End must occur after Slot Start.');

		// 3. Check end date
		$endQuery = <<<SQL
SELECT count(*) FROM "$bookingsTable"
WHERE "slotEnd" = '$end'
LIMIT 1
SQL;
		$endConflicting = (bool) $db->createCommand($endQuery)->queryScalar();

		if ($endConflicting)
			return \Craft::t('bookings', 'Slot End is unavailable.');

		// 4. Check for overlaps
		$overlayQuery = <<<SQL
SELECT count(*) FROM $bookingsTable
WHERE "slotEnd" > '$start' 
OR "slotStart" < '$end'
LIMIT 1
SQL;
		$overlapping = (bool) $db->createCommand($overlayQuery)->queryScalar();

		if ($overlapping)
			return \Craft::t('bookings', 'The selected slot range is unavailable.');

		return true;
	}

	// Private
	// =========================================================================

	/**
	 * Generates a unique booking number
	 *
	 * @return string
	 */
	private function _generateBookingNumber ()
	{
		return md5(uniqid(mt_rand(), true));
	}

}