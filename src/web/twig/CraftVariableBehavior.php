<?php
/**
 * Bookings plugin for Craft CMS 3.x
 *
 * @link      https://ethercreative.co.uk
 * @copyright Copyright (c) Ether Creative
 */

namespace ether\bookings\web\twig;

use ether\bookings\elements\Booking;
use ether\bookings\elements\db\BookingQuery;
use yii\base\Behavior;


/**
 * Class CraftVariableBehavior
 *
 * @author  Ether Creative
 * @package ether\bookings\web\twig
 * @since   1.0.0
 */
class CraftVariableBehavior extends Behavior
{

	/**
	 * {{ craft.bookings.all() }}
	 *
	 * @param mixed $criteria
	 *
	 * @return BookingQuery
	 */
	public function bookings ($criteria = null): BookingQuery
	{
		$query = Booking::find();

		if ($criteria)
			\Craft::configure($query, $criteria);

		return $query;
	}

}