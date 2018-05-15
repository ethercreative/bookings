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
use yii\db\ActiveQueryInterface;


/**
 * Class BookableRecord
 *
 * @author  Ether Creative
 * @package ether\bookings\records
 * @since   1.0.0
 */
class BookableRecord extends ActiveRecord
{

	// Props
	// =========================================================================

	// Props: Public Static
	// -------------------------------------------------------------------------

	/** @var string */
	public static $tableName = '{{%bookings_bookables}}';

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
	 * Returns the bookable's owner
	 *
	 * @return ActiveQueryInterface - The relational query object
	 */
	public function getOwner (): ActiveQueryInterface
	{
		return $this->hasOne(Element::class, ['id' => 'ownerId']);
	}

	/**
	 * Returns the bookable's field
	 *
	 * @return ActiveQueryInterface - The relational query object
	 */
	public function getField (): ActiveQueryInterface
	{
		return $this->hasOne(Field::class, ['id' => 'fieldId']);
	}

}