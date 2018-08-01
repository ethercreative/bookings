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
 * Class EventRecord
 *
 * @property int $id
 * @property int $elementId
 * @property int $fieldId
 * @property bool $enabled
 * @property array $settings
 *
 * @author  Ether Creative
 * @package ether\bookings\records
 * @since   1.0.0
 */
class EventRecord extends ActiveRecord
{

	// Properties
	// =========================================================================

	public static $tableName = '{{%bookings_events}}';

	// Methods
	// =========================================================================

	/**
	 * @return string
	 */
	public static function getTableName (): string
	{
		return self::$tableName;
	}

	public function getElement ()
	{
		return $this->hasOne(Element::class, ['id' => 'elementId']);
	}

	public function getField ()
	{
		return $this->hasOne(Field::class, ['id' => 'fieldId']);
	}

	public function getTickets ()
	{
		return $this->hasMany(TicketRecord::class, ['eventId' => 'id']);
	}

}