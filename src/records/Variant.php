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
use craft\records\Element;
use yii\db\ActiveQueryInterface;

/**
 * Class Variant
 *
 * @property int $id
 * @property int $productId
 * @property ActiveQueryInterface $element
 * @property ActiveQueryInterface $bookable
 *
 * @author    Ether Creative
 * @package   ether\bookings\records
 * @since     1.0.0-alpha.1
 */
class Variant extends ActiveRecord
{

	// Consts
	// =========================================================================

	const TABLE_NAME = '{{%bookings_variants}}';
	const TABLE_NAME_CLEAN = 'bookings_variants';

	// Methods
	// =========================================================================

	public static function tableName (): string
	{
		return self::TABLE_NAME;
	}

	public function rules ()
	{
		return [
			[['sku'], 'unique'],
		];
	}

	public function getElement (): ActiveQueryInterface
	{
		return $this->hasOne(Element::class, ['id', 'id']);
	}

	public function getBookable (): ActiveQueryInterface
	{
		return $this->hasOne(Bookable::class, ['id', 'bookableId']);
	}

}