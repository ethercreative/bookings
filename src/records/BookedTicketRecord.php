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
 * Class BookedTicketRecord
 *
 * @property int $id
 * @property int $ticketId
 * @property int $bookingId
 * @property int $lineItemId
 *
 * @author  Ether Creative
 * @package ether\bookings\records
 * @since   1.0.0
 */
class BookedTicketRecord extends ActiveRecord
{

	// Properties
	// =========================================================================

	public static $tableNameUnprefixed = 'bookings_booked_tickets';
	public static $tableName = '{{%bookings_booked_tickets}}';

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

	public function getTicket ()
	{
		return $this->hasOne(TicketRecord::class, ['id' => 'ticketId']);
	}

}