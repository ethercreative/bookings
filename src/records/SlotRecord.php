<?php
/**
 * Bookings plugin for Craft CMS 3.x
 *
 * @link      https://ethercreative.co.uk
 * @copyright Copyright (c) 2018 Ether Creative
 */

namespace ether\bookings\records;

use craft\db\ActiveRecord;

/**
 * Class SlotRecord
 *
 * @property int       $id
 * @property int       $eventId
 * @property \DateTime $slot
 *
 * @author  Ether Creative
 * @package ether\bookings\records
 */
class SlotRecord extends ActiveRecord
{

	// Properties
	// =========================================================================

	public static $tableName = '{{%bookings_slots}}';

	// Methods
	// =========================================================================

	public static function tableName (): string
	{
		return self::$tableName;
	}

	public function getEvent ()
	{
		return $this->hasOne(EventRecord::class, ['id' => 'eventId']);
	}

}