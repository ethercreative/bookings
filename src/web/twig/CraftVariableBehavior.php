<?php
/**
 * Bookings for Craft CMS
 *
 * @link      https://ethercreative.co.uk
 * @copyright Copyright (c) 2019 Ether Creative
 */

namespace ether\bookings\web\twig;

use ether\bookings\Bookings;
use ether\bookings\elements\db\EventQuery;
use ether\bookings\elements\Event;
use yii\base\Behavior;

/**
 * Class CraftVariableBehavior
 *
 * @author  Ether Creative
 * @package ether\bookings\web\twig
 */
class CraftVariableBehavior extends Behavior
{

	// Properties
	// =========================================================================

	/** @var Bookings */
	public $bookings;

	// Methods
	// =========================================================================

	public function init ()
	{
		parent::init();

		// Point `craft.bookings` to Bookings
		$this->bookings = Bookings::getInstance();
	}

	/**
	 * @param mixed $criteria
	 *
	 * @return EventQuery
	 */
	public function events ($criteria = null)//: EventQuery
	{
		$query = Event::find();

		if ($criteria)
			\Craft::configure($query, $criteria);

		return $query;
	}

	// Overriding things because the events function is already a thing but I
	// want to use it so overriding these is fine right?
	// =========================================================================

	public function attach ($owner)
	{
		$this->owner = $owner;
	}

	public function detach ()
	{
		if ($this->owner)
			$this->owner = null;
	}

}
