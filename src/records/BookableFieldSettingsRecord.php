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
use yii\db\ActiveQueryInterface;


/**
 * Class BookableFieldSettingsRecord
 *
 * @property int $fieldId
 * @property int $fieldLayoutId
 *
 * @author  Ether Creative
 * @package ether\bookings\records
 * @since   1.0.0
 */
class BookableFieldSettingsRecord extends ActiveRecord
{

	// Props
	// =========================================================================

	// Props: Public Static
	// -------------------------------------------------------------------------

	/** @var string */
	public static $tableName = '{{%bookings_bookablefieldsettings}}';

	// Public Methods
	// =========================================================================

	// Public Methods: Static
	// -------------------------------------------------------------------------

	/**
	 * @inheritdoc
	 *
	 * @return string
	 */
	public static function tableName (): string
	{
		return self::$tableName;
	}

	// Public Methods: Instance
	// -------------------------------------------------------------------------

	/**
	 * Returns the bookable field these settings belong to
	 *
	 * @return ActiveQueryInterface - The relational query object
	 */
	public function getField (): ActiveQueryInterface
	{
		return $this->hasOne(Field::class, ['id' => 'fieldId']);
	}

	/**
	 * Returns the bookable's field layout
	 *
	 * @return ActiveQueryInterface
	 */
	public function getFieldLayout (): ActiveQueryInterface
	{
		return $this->hasOne(FieldLayout::class, ['id' => 'fieldLayoutId']);
	}

}