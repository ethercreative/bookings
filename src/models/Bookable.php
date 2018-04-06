<?php
/**
 * Bookings plugin for Craft CMS 3.x
 *
 * @link      https://ethercreative.co.uk
 * @copyright Copyright (c) Ether Creative
 */

namespace ether\bookings\models;

use craft\base\Model;
use ether\bookings\enums\BookableType;
use RRule\RRule;

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
	 * @var RRule The base RRule
	 */
	public $rrule;

	/**
	 * @var ExRule[] Exceptions to the base RRule
	 */
	public $exRules;

	// Methods
	// =========================================================================

	// Methods: Public
	// -------------------------------------------------------------------------

	public function __construct (array $attributes = [], array $config = [])
	{
		foreach ($attributes as $key => $value)
			if (property_exists($this, $key))
				$this[$key] = $value;

		parent::__construct($config);
	}

	public function rules ()
	{
		$rules = parent::rules();

		$rules[] = [
			['bookableType', 'slotMultiplier', 'rrule'],
			'required',
		];

		return $rules;
	}

}