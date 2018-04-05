<?php
/**
 * Bookings plugin for Craft CMS 3.x
 *
 * An advanced booking plugin for Craft CMS and Craft Commerce
 *
 * @link      https://ethercreative.co.uk
 * @copyright Copyright (c) 2018 Ether Creative
 */

namespace ether\bookings\records;

use craft\commerce\records\TaxCategory;
use craft\db\ActiveRecord;
use craft\records\Element;
use yii\db\ActiveQueryInterface;

/**
 * Bookable Record
 *
 * @property int $typeId
 * @property int $taxCategoryId
 * @property TaxCategory $taxCategory
 * @property Variant[] $variants
 *
 * @property ActiveQueryInterface $element
 * @property ActiveQueryInterface $type
 *
 * @author    Ether Creative
 * @package   ether\bookings\records
 * @since     1.0.0-alpha.1
 */
class Bookable extends ActiveRecord
{

	// Consts
	// =========================================================================

	const TABLE_NAME = '{{%bookings_bookables}}';
	const TABLE_NAME_CLEAN = 'bookings_bookables';

	// Methods
	// =========================================================================

	public static function tableName (): string
	{
		return self::TABLE_NAME;
	}

	public function getElement (): ActiveQueryInterface
	{
		return $this->hasOne(Element::class, ['id' => 'id']);
	}

	public function getType (): ActiveQueryInterface
	{
		return $this->hasOne(BookableType::class, ['id' => 'bookableTypeId']);
	}

	public function getTaxCategory (): ActiveQueryInterface
	{
		return $this->hasOne(TaxCategory::class, ['id' => 'taxCategoryId']);
	}

	public function getVariants (): ActiveQueryInterface
	{
		return $this->hasMany(Variant::class, ['bookableId' => 'id']);
	}

}