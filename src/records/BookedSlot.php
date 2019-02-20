<?php
/**
 * Bookings for Craft CMS
 *
 * @link      https://ethercreative.co.uk
 * @copyright Copyright (c) 2019 Ether Creative
 */

namespace ether\bookings\records;

use craft\db\ActiveRecord;
use yii\db\ActiveQueryInterface;

/**
 * Class BookedSlot
 *
 * @property int       $id
 * @property bool      $start
 * @property bool      $end
 * @property int       $eventId
 * // TODO: Add bookingId / ticketId / resourceId
 * @property \DateTime $date
 * @author  Ether Creative
 * @package ether\bookings\records
 */
class BookedSlot extends ActiveRecord
{

	// Consts
	// =========================================================================

	const TableName = '{{%bookings_booked_slots}}';
	const TableNameClean = 'bookings_booked_slots';

	// Methods
	// =========================================================================

	public static function tableName ()
	{
		return self::TableName;
	}

	public function getEvent (): ActiveQueryInterface
	{
		return $this->hasOne(
			Event::class,
			['id' => 'eventId']
		);
	}

	// TODO: Relate to Booking / Ticket / Resource

}
