<?php
/**
 * Bookings plugin for Craft CMS 3.x
 *
 * @link      https://ethercreative.co.uk
 * @copyright Copyright (c) Ether Creative
 */

namespace ether\bookings\models;

use ether\bookings\enums\Frequency;


/**
 * Class Slot
 *
 * @author  Ether Creative
 * @package ether\bookings\models
 * @since   1.0.0
 */
class Slot
{

	// Properties
	// =========================================================================

	/** @var \DateTime */
	public $start;

	/** @var \DateTime */
	public $end;

	/** @var int - The available capacity on this slot */
	public $capacity;

	// Constructor
	// =========================================================================

	public function __construct (Bookable $bookable, \DateTime $start, int $bookingCount)
	{
		$baseRule = $bookable->baseRule;

		$durationModifier = $baseRule->duration;
		$durationModifier .= ' ' . Frequency::toUnit($baseRule->frequency);

		$this->start = $start;
		$this->end = $start->modify($durationModifier);
		$this->capacity = $bookable->slotMultiplier - $bookingCount;
	}

}