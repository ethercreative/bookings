<?php
/**
 * Bookings plugin for Craft CMS 3.x
 *
 * @link      https://ethercreative.co.uk
 * @copyright Copyright (c) Ether Creative
 */

namespace ether\bookings\common;

use ether\bookings\enums\Frequency;
use ether\bookings\models\Bookable;
use ether\bookings\models\Slot;


/**
 * Class Availability
 *
 * @author  Ether Creative
 * @package ether\bookings\common
 * @since   1.0.0
 */
class Availability
{

	// Properties
	// =========================================================================

	/** @var Bookable */
	private $_field;

	/** @var \DateTime|null */
	private $_start;

	/** @var \DateTime|null */
	private $_end;

	/** @var int */
	private $_count = 1000;

	// Constructor
	// =========================================================================

	/**
	 * Availability constructor.
	 *
	 * @param Bookable $field
	 */
	public function __construct (Bookable $field)
	{
		$this->_field = $field;

		$baseRule = $field->baseRule;

		$this->_start = $baseRule->start->format(\DateTime::W3C);

		if ($baseRule->until)
			$this->_end = $baseRule->until->format(\DateTime::W3C);

		if ($baseRule->count && $baseRule->count < 1000)
			$this->_count = $baseRule->count;
	}

	// Methods
	// =========================================================================

	// Methods: Setters
	// -------------------------------------------------------------------------

	/**
	 * When to start looking from (will default to the bookable fields start
	 * date / time)
	 *
	 * @param \DateTime|string|int|null $value
	 *
	 * @return static
	 */
	public function start ($value)
	{
		if ($value instanceof \DateTime)
			$value = $value->format(\DateTime::W3C);

		$this->_start = $value;

		return $this;
	}

	/**
	 * When to end looking (will default to the bookable fields until date /
	 * time, if available)
	 *
	 * @param \DateTime|string|int|null $value
	 *
	 * @return static
	 */
	public function end ($value)
	{
		if ($value instanceof \DateTime)
			$value = $value->format(\DateTime::W3C);

		$this->_end = $value;

		return $this;
	}

	/**
	 * How many slots to return (will default to the bookable fields count or
	 * 1000 (which ever is lower))
	 *
	 * @param int $value
	 *
	 * @return static
	 */
	public function count ($value)
	{
		if ($value > 1000)
		{
			\Craft::warning(
				\Craft::t(
					'bookings',
					'Attempting to retrieve more than 1000 slots can have an impact on performance!'
				),
				'bookings'
			);
		}

		$this->_count = $value;

		return $this;
	}
	
	// Methods: Execution
	// -------------------------------------------------------------------------

	public function all (): array
	{
		$slots = [];

		// TODO: Get slot count from DB

		/** @var \DateTime $slot */
		foreach ($this->_slots() as $slot)
			$slots[] = new Slot($this->_field, $slot, 0);

		return $slots;
	}

	// Helpers
	// =========================================================================

	/**
	 * Gets the slots as an iterable
	 *
	 * @return \ArrayAccess|\Countable|\Iterator|\RRule\RSet
	 */
	private function _slots ()
	{
		if ($this->_end)
			return $this->_field->getSlotsInRangeAsIterable($this->_start, $this->_end);

		return $this->_field->getSlotsFromAsIterable($this->_start);
	}

}