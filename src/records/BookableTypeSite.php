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

use craft\db\ActiveRecord;
use craft\records\Site;
use yii\db\ActiveQueryInterface;

/**
 * Class BookableTypeSite
 *
 * @author    Ether Creative
 * @package   ether\bookings\records
 * @since     1.0.0-alpha.1
 */
class BookableTypeSite extends ActiveRecord
{

	// Consts
	// =========================================================================

	const TABLE_NAME = '{{%bookables_bookabletypes_sites}}';
	const TABLE_NAME_CLEAN = 'bookables_bookabletypes_sites';

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

	public function getSite(): ActiveQueryInterface
	{
		return $this->hasOne(Site::class, ['id', 'siteId']);
	}

}