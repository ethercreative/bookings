<?php
/**
 * Bookings plugin for Craft CMS 3.x
 *
 * @link      https://ethercreative.co.uk
 * @copyright Copyright (c) Ether Creative
 */

namespace ether\bookings\services;

use craft\base\Component;
use ether\bookings\Bookings;
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
	 * @param bool                            $includeExpired
	 *
	 * @return Booking|Booking[]
	 * @throws \Throwable
	 */
	public function populate ($records, $includeExpired = true)
	{
		if (is_array($records))
			return array_filter(
				array_map(
					function (BookingRecord $record) use ($includeExpired) {
						return $this->populateBookingOrExpire(
							$record,
							$includeExpired
						);
					},
					$records
				)
			);

		/** @noinspection PhpParamsInspection */
		return $this->populateBookingOrExpire($records, $includeExpired);
	}

	/**
	 * Ensures the given slot times do not conflict with existing slots
	 *
	 * @param \DateTime|string      $start
	 * @param \DateTime|string|null $end
	 * @param int|null              $id
	 *
	 * @return bool
	 * @throws \yii\db\Exception
	 */
	public function validateSlot ($start, $end = null, $id = null)
	{
		if (!$start instanceof \DateTime)
			$start = new \DateTime($start);

		if ($end && !$end instanceof \DateTime)
			$end = new \DateTime($end);

		if ($id) $id = 'AND "id" != ' . $id;
		else $id = '';

		$start = $start->format('c');

		if ($end)
			$end = $end->format('c');

		$db = \Craft::$app->db;
		$bookingsTable = BookingRecord::$tableName;

		// 1. Check start date
		$startQuery = <<<SQL
SELECT count(*) FROM $bookingsTable
WHERE "slotStart" = '$start'
AND "expired" = FALSE
$id
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
AND "expired" = FALSE
$id
LIMIT 1
SQL;

		if ($db->createCommand($endQuery)->queryScalar())
			return \Craft::t('bookings', 'Slot End is unavailable.');

		// 4. Check for overlaps
		$overlayQuery = <<<SQL
SELECT count(*) FROM $bookingsTable
WHERE "slotEnd" > '$start' 
OR "slotStart" < '$end'
AND "expired" = FALSE
$id
LIMIT 1
SQL;

		if ($db->createCommand($overlayQuery)->queryScalar())
			return \Craft::t('bookings', 'The selected slot range is unavailable.');

		return true;
	}

	/**
	 * Erases all expired bookings from the database
	 *
	 * @param bool $force If true the `clearExpiredDuration` will be ignored,
	 *                    meaning all bookings marked as EXPIRED will be deleted
	 *
	 * @throws \Throwable
	 */
	public function clearExpiredBookings (bool $force = false)
	{
		$settings = Bookings::getInstance()->settings;

		$where = [
			'and',
			'{{%expired}} = true',
		];

		if (!$force)
		{
			$since = time() - ($settings->expiryDuration + $settings->clearExpiredDuration);
			$since = "'" . date(\DateTime::W3C, $since) . "'";

			$where[] = '{{%reservationExpiry}} < ' . $since;
		}

		$expiredBookings = BookingRecord::find()->where($where)->all();

		foreach ($expiredBookings as $booking)
			\Craft::$app->elements->deleteElementById($booking->id);
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
	 * @param bool          $includeExpired
	 *
	 * @return Booking|null
	 * @throws \Throwable
	 * @private
	 */
	public function populateBookingOrExpire (BookingRecord $record, $includeExpired = true)
	{
		$booking = new Booking($record);

		$booking->expireBooking();

		if ($booking->expired && !$includeExpired)
			return null;

		return $booking;
	}

}