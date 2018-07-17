<?php
/**
 * Bookings plugin for Craft CMS 3.x
 *
 * @link      https://ethercreative.co.uk
 * @copyright Copyright (c) Ether Creative
 */

namespace ether\bookings\integrations\commerce;

use craft\base\ElementInterface;
use craft\commerce\base\Purchasable;


/**
 * Class CommerceValidators
 *
 * @author  Ether Creative
 * @package ether\bookings\integrations\commerce
 * @since   1.0.0
 */
class CommerceValidators
{

	/**
	 * @param ElementInterface $element
	 *
	 * @return bool
	 */
	public static function isElementPurchasable (ElementInterface $element)
	{
		if (!$element)
			return false;

		return $element instanceof Purchasable;
	}

}