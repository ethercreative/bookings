<?php
/**
 * Bookings plugin for Craft CMS 3.x
 *
 * @link      https://ethercreative.co.uk
 * @copyright Copyright (c) Ether Creative
 */

namespace ether\bookings\integrations\commerce;

use craft\commerce\elements\Product;
use craft\commerce\Plugin;


/**
 * Class CommerceGetters
 *
 * @author  Ether Creative
 * @package ether\bookings\integrations\commerce
 * @since   1.0.0
 */
class CommerceGetters
{

	/**
	 * @param $id
	 *
	 * @return \craft\commerce\models\LineItem|null
	 */
	public static function getLineItemById ($id)
	{
		return Plugin::getInstance()->getLineItems()->getLineItemById($id);
	}

	/**
	 * @param $id
	 *
	 * @return \craft\commerce\elements\Order|null
	 */
	public static function getOrderById ($id)
	{
		return Plugin::getInstance()->getOrders()->getOrderById($id);
	}

	/**
	 * @param $id
	 *
	 * @return \craft\commerce\models\Customer|null
	 */
	public static function getCustomerById ($id)
	{
		return Plugin::getInstance()->getCustomers()->getCustomerById($id);
	}

	/**
	 * @param $ids
	 *
	 * @return \craft\commerce\elements\Product[]
	 */
	public static function getProductsByIds ($ids)
	{
		return Product::findAll([
			'id' => $ids,
		]);
	}

}