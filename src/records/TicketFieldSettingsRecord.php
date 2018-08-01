<?php
/**
 * Bookings plugin for Craft CMS 3.x
 *
 * @link      https://ethercreative.co.uk
 * @copyright Copyright (c) Ether Creative
 */

namespace ether\bookings\records;

use craft\db\ActiveRecord;
use craft\records\Field;
use craft\records\FieldLayout;


/**
 * Class TicketFieldSettingsRecord
 *
 * @property int $id
 * @property int $fieldId
 * @property int $fieldLayoutId
 *
 * @author  Ether Creative
 * @package ether\bookings\records
 * @since   1.0.0
 */
class TicketFieldSettingsRecord extends ActiveRecord
{

	// Properties
	// =========================================================================

	public static $tableName = '{{%bookings_ticket_fields_settings}}';
	
	// Methods
	// =========================================================================

	/**
	 * @return string
	 */
	public static function getTableName (): string
	{
		return self::$tableName;
	}

	public function getField ()
	{
		return $this->hasOne(Field::class, ['id' => 'fieldId']);
	}

	public function getFieldLayout ()
	{
		return $this->hasOne(FieldLayout::class, ['id' => 'fieldLayoutId']);
	}

}