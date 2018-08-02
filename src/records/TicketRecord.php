<?php
/**
 * Bookings plugin for Craft CMS 3.x
 *
 * @link      https://ethercreative.co.uk
 * @copyright Copyright (c) Ether Creative
 */

namespace ether\bookings\records;

use craft\db\ActiveRecord;
use craft\records\Element;
use craft\records\Field;


/**
 * Class TicketRecord
 *
 * @property int $id
 * @property int $eventId
 * @property int $elementId
 * @property int $fieldId
 * @property int $capacity
 * @property int $maxQty
 *
 * @author  Ether Creative
 * @package ether\bookings\records
 * @since   1.0.0
 */
class TicketRecord extends ActiveRecord
{

	// Properties
	// =========================================================================

	public static $tableName = '{{%bookings_tickets}}';

	// Methods
	// =========================================================================

	/**
	 * @return string
	 */
	public static function tableName (): string
	{
		return self::$tableName;
	}

	public function getEvent ()
	{
		return $this->hasOne(EventRecord::class, ['id' => 'eventId']);
	}

	public function getElement ()
	{
		return $this->hasOne(Element::class, ['id' => 'elementId']);
	}

	public function getField ()
	{
		return $this->hasOne(Field::class, ['id' => 'fieldId']);
	}

}