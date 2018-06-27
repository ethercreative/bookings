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
use ether\bookings\records\BookableRecord;
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

	// TODO: Need a way to clear expired bookings as soon as they expire...
	// Could have a cron running every minute to clear expired bookings.
	// Every time a booking is populated, we could check to see if it has
	// expired and remove it accordingly?
	// This would probably require us having a single function responsible for
	// populating Booking models from records.

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
	 * Populates Booking model(s) from the given record(s)
	 *
	 * IMPORTANT: This function MUST ALWAYS be used when populating Bookings
	 * from the DB
	 *
	 * @param BookableRecord|BookableRecord[] $records
	 *
	 * @return Booking|Booking[]
	 */
	public function populate ($records)
	{
		if (is_array($records))
			return array_filter(
				array_map([$this, 'populateBookingOrExpire'], $records)
			);

		/** @noinspection PhpParamsInspection */
		return $this->populateBookingOrExpire($records);
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

		$start = $start->format('c');

		if ($end)
			$end = $end->format('c');

		$db = \Craft::$app->db;
		$bookingsTable = BookingRecord::$tableName;

		// 1. Check start date
		$startQuery = <<<SQL
SELECT count(*) FROM $bookingsTable
WHERE "slotStart" = '$start'
LIMIT 1
SQL;

		if ($db->createCommand($startQuery)->queryScalar())
			return \Craft::t('bookings', 'Slot Start is unavailable.');

		if (!$end)
			return true;

		// 2. Ensure end date comes after start
		if ($end < $start)
			return \Craft::t('bookings', 'Slot End must occur after Slot Start.');

		// 3. Check end date
		$endQuery = <<<SQL
SELECT count(*) FROM $bookingsTable
WHERE "slotEnd" = '$end'
LIMIT 1
SQL;

		if ($db->createCommand($endQuery)->queryScalar())
			return \Craft::t('bookings', 'Slot End is unavailable.');

		// 4. Check for overlaps
		$overlayQuery = <<<SQL
SELECT count(*) FROM $bookingsTable
WHERE "slotEnd" > '$start' 
OR "slotStart" < '$end'
LIMIT 1
SQL;

		if ($db->createCommand($overlayQuery)->queryScalar())
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

	/**
	 * @param BookingRecord $record
	 *
	 * @return Booking|null
	 * @private
	 */
	public function populateBookingOrExpire (BookingRecord $record)
	{
		// TODO: Check if booking has expired, and erase it, notifying the user?
		// (Can't rely on user session here)

		return new Booking($record);
	}

}