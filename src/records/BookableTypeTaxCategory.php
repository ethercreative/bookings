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
use yii\db\ActiveQueryInterface;

/**
 * Class BookableTypeTaxCategory
 *
 * @author    Ether Creative
 * @package   ether\bookings\records
 * @since     1.0.0-alpha.1
 */
class BookableTypeTaxCategory extends ActiveRecord
{

	// Consts
	// =========================================================================

	const TABLE_NAME = '{{%bookables_bookabletypes_taxcategories}}';
	const TABLE_NAME_CLEAN = 'bookables_bookabletypes_taxcategories';

	// Methods
	// =========================================================================

	public static function tableName ()
	{
		return self::TABLE_NAME;
	}

	public function getBookableType(): ActiveQueryInterface
	{
		return $this->hasOne(BookableType::class, ['id', 'bookableTypeId']);
	}

	public function getTaxCategory(): ActiveQueryInterface
	{
		return $this->hasOne(TaxCategory::class, ['id', 'taxCategoryId']);
	}

}