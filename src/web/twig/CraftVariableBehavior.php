<?php
/**
 * Bookings plugin for Craft CMS 3.x
 *
 * @link      https://ethercreative.co.uk
 * @copyright Copyright (c) Ether Creative
 */

namespace ether\bookings\web\twig;

use craft\elements\db\ElementQueryInterface;
//use ether\bookings\common\Availability;
//use ether\bookings\elements\Booking;
//use ether\bookings\elements\db\BookingQuery;
use ether\bookings\models\BookableEvent;
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
	 * @return BookingQuery|ElementQueryInterface
	 */
//	public function bookings ($criteria = null): BookingQuery
//	{
//		$query = Booking::find();
//
//		if ($criteria)
//			\Craft::configure($query, $criteria);
//
//		return $query;
//	}

	/**
	 * {{ craft.availability(myBookableField) }}
	 *
	 * @param BookableEvent $field
	 *
	 * @return Availability
	 */
//	public function availability (BookableEvent $field): Availability
//	{
//		return new Availability($field);
//	}

}