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
use craft\records\FieldLayout;
use yii\db\ActiveQueryInterface;

/**
 * Class BookableType
 *
 * @author    Ether Creative
 * @package   ether\bookings\records
 * @since     1.0.0-alpha.1
 */
class BookableType extends ActiveRecord
{

	// Consts
	// =========================================================================

	const TABLE_NAME = '{{%bookables_bookabletypes}}';
	const TABLE_NAME_CLEAN = 'bookables_bookabletypes';

	// Methods
	// =========================================================================

	public static function tableName ()
	{
		return self::TABLE_NAME;
	}

	public function getBookableTypeTaxCategories (): ActiveQueryInterface
	{
		return $this->hasMany(
			BookableTypeTaxCategory::class,
			['bookableTypeId' => 'id']
		);
	}

	public function getTaxCategories (): ActiveQueryInterface
	{
		return $this
			->hasMany(TaxCategory::class, ['id' => 'taxCategoryId'])
			->via('bookableTypeTaxCategories');
	}

	public function getFieldLayout (): ActiveQueryInterface
	{
		return $this->hasOne(
			FieldLayout::class,
			['id' => 'fieldLayoutId']
		);
	}

	public function getVariantFieldLayout (): ActiveQueryInterface
	{
		return $this->hasOne(
			FieldLayout::class,
			['id' => 'variantFieldLayout']
		);
	}

}