<?php
/**
 * Bookings plugin for Craft CMS 3.x
 *
 * @link      https://ethercreative.co.uk
 * @copyright Copyright (c) Ether Creative
 */

namespace ether\bookings\models;

use ether\bookings\enums\Frequency;
use ether\bookings\helpers\SlotDateTime;


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

	/** @var \DateTime|string */
	public $start;

	/** @var \DateTime|string */
	public $end;

	/** @var int - The available capacity on this slot */
	public $capacity;

	/** @var boolean */
	public $hasBookings;

	/** @var boolean */
	public $fullyBooked;

	// Constructor
	// =========================================================================

	public function __construct (Bookable $bookable, \DateTime $start, int $bookingCount)
	{
		$baseRule = $bookable->baseRule;

		$durationModifier = $baseRule->duration;
		$durationModifier .= ' ' . Frequency::toUnit($baseRule->frequency);

		$start = new SlotDateTime($start->format(DATE_ATOM));

		$this->start = $start;
		$this->end = $start->modify($durationModifier);
		$this->capacity = $bookable->slotMultiplier - $bookingCount;
		$this->hasBookings = $bookingCount > 0;
		$this->fullyBooked = $this->capacity === 0;
	}

}