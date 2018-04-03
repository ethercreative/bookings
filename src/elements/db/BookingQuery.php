<?php
/**
 * Bookings plugin for Craft CMS 3.x
 *
 * An advanced booking plugin for Craft CMS and Craft Commerce
 *
 * @link      https://ethercreative.co.uk
 * @copyright Copyright (c) 2018 Ether Creative
 */

namespace ether\bookings\elements\db;

use craft\elements\db\ElementQuery;
use craft\helpers\Db;
use ether\bookings\elements\Booking;

/**
 * Class BookingQuery
 *
 * @see https://github.com/craftcms/docs/blob/v3/en/element-types.md#element-query-class
 *
 * @author    Ether Creative
 * @package   ether\bookings\elements
 * @since     1.0.0-alpha.1
 */
class BookingQuery extends ElementQuery
{

	// Properties
	// =========================================================================

//	public $price;

	// Methods
	// =========================================================================

//	public function price ($value)
//	{
//		$this->price = $value;
//		return $this;
//	}

	protected function beforePrepare (): bool
	{
		$tableName = Booking::$tableNameClean;

		$this->joinElementTable($tableName);

		$this->query->select([
//			$tableName . '.price',
		]);

//		if ($this->price)
//			$this->subQuery->andWhere(
//				Db::parseParam(
//					$tableName . '.price',
//					$this->price
//				)
//			);

		return parent::beforePrepare();
	}

}