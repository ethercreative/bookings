<?php
/**
 * Bookings plugin for Craft CMS 3.x
 *
 * An advanced booking plugin for Craft CMS and Craft Commerce
 *
 * @link      https://ethercreative.co.uk
 * @copyright Copyright (c) 2018 Ether Creative
 */

namespace ether\bookings\web\twig;

use craft\elements\db\ElementQueryInterface;
use ether\bookings\elements\Bookable;
use ether\bookings\elements\db\BookableQuery;
use yii\base\Behavior;

/**
 * Class CraftVariableBehavior
 *
 * @author  Ether Creative
 * @package ether\bookings\web\twig
 * @since   1.0.0-alpha.1
 */
class CraftVariableBehavior extends Behavior
{

	// Methods
	// =========================================================================

	/**
	 * Adds a `craft.bookables()` function to the templates
	 *
	 * @param null $criteria
	 *
	 * @return BookableQuery|ElementQueryInterface
	 */
	public function bookables ($criteria = null) : BookableQuery
	{
		$query = Bookable::find();

		if ($criteria)
			\Craft::configure($query, $criteria);

		return $query;
	}

}