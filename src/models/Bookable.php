<?php
/**
 * Bookings plugin for Craft CMS 3.x
 *
 * @link      https://ethercreative.co.uk
 * @copyright Copyright (c) Ether Creative
 */

namespace ether\bookings\models;

use ether\bookings\base\Model;
use ether\bookings\enums\BookableType;
use RRule\RSet;

/**
 * Class Bookable
 *
 * @author  Ether Creative
 * @package ether\bookings\models
 * @since   1.0.0
 */
class Bookable extends Model
{

	// Properties
	// =========================================================================

	// Properties: Public
	// -------------------------------------------------------------------------

	/** @var int */
	public $id;

	/** @var int */
	public $ownerId;

	/** @var int */
	public $ownerSiteId;

	/** @var int */
	public $fieldId;

	/** @var \DateTime */
	public $dateCreated;

	/** @var \DateTime */
	public $dateUpdated;

	// ---

	/**
	 * @var string The type of bookable
	 * @see BookableType
	 */
	public $bookableType;

	/**
	 * @var bool If true, bookable will accept a range of slots
	 */
	public $acceptsRange = false;

	/**
	 * @var int|null The maximum capacity per-slot for this bookable
	 */
	public $maxCapacity;

	/**
	 * @var int The number of times each slot is available
	 */
	public $slotMultiplier = 1;

	/**
	 * @var int The duration of each slot in minutes
	 */
	public $slotDuration;

	/**
	 * @var RecursionRule The base RRule
	 */
	public $baseRule;

	/**
	 * @var ExRule[] An array of exceptions to the base rule
	 */
	public $exRules = [];

	// Properties: Private
	// -------------------------------------------------------------------------

	/** @var RSet|null */
	private $_set;

	// Methods
	// =========================================================================

	// Methods: Public
	// -------------------------------------------------------------------------

	public function rules ()
	{
		$rules = parent::rules();

		$rules[] = [
			['bookableType', 'slotMultiplier', 'rrule'],
			'required',
		];

		return $rules;
	}

	public function getSlotsInRange (\DateTime $start, \DateTime $end)
	{
		// TODO: Return slots between $start and $end
	}

	public function getSlotsFrom (\DateTime $start, $count = 100)
	{
		// TODO: Return $count number of slots from $start
	}

	// Methods: Private
	// -------------------------------------------------------------------------

	private function getSet (): RSet
	{
		if ($this->_set)
			return $this->_set;

		$set = new RSet();

		// HMMMM

		return $this->_set = $set;
	}

}