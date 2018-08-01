<?php
/**
 * Bookings plugin for Craft CMS 3.x
 *
 * @link      https://ethercreative.co.uk
 * @copyright Copyright (c) Ether Creative
 */

namespace ether\bookings\records;

use craft\db\ActiveRecord;


/**
 * Class BookedEventRecord
 *
 * @property int $id
 * @property int $bookingId
 * @property int $eventId
 *
 * @author  Ether Creative
 * @package ether\bookings\records
 * @since   1.0.0
 */
class BookedEventRecord extends ActiveRecord
{

	// Properties
	// =========================================================================

	public static $tableName = '{{%bookings_booked_events}}';

	// Methods
	// =========================================================================

	/**
	 * @return string
	 */
	public static function tableName (): string
	{
		return self::$tableName;
	}

	public function getBooking ()
	{
		return $this->hasOne(BookingRecord::class, ['id' => 'bookingId']);
	}

	public function getEvent ()
	{
		return $this->hasOne(EventRecord::class, ['id' => 'eventId']);
	}

}