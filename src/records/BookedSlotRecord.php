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
 * Class BookedSlotRecord
 *
 * @property int $id
 * @property bool $start
 * @property bool $end
 * @property int $bookingId
 * @property int $eventId
 * @property int $ticketId
 * @property \DateTime $date
 *
 * @author  Ether Creative
 * @package ether\bookings\records
 * @since   1.0.0
 */
class BookedSlotRecord extends ActiveRecord
{

	// Properties
	// =========================================================================

	public static $tableName = '{{%bookings_booked_slots}}';

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

	public function getTicket ()
	{
		return $this->hasOne(TicketRecord::class, ['id' => 'ticketId']);
	}

}