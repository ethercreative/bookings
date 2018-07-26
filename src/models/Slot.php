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

	/** @var \DateTime|string - The start time of the slot */
	public $start;

	/** @var \DateTime|string - The end time of the slot (start + duration) */
	public $end;

	/** @var int - The available capacity on this slot */
	public $capacity;

	/** @var boolean */
	public $hasBookings;

	/** @var boolean */
	public $fullyBooked;

	// Constructor
	// =========================================================================

	/**
	 * Slot constructor.
	 *
	 * @param BookableEvent $bookable
	 * @param \DateTime     $start
	 * @param int           $bookingCount
	 *
	 * @throws \Exception
	 */
	public function __construct (BookableEvent $bookable, \DateTime $start, int $bookingCount)
	{
		$baseRule = $bookable->baseRule;

		$durationModifier = $baseRule->duration;
		$durationModifier .= ' ' . Frequency::toUnit($baseRule->frequency);

		$start = new SlotDateTime(
			$start->format(\DateTime::W3C),
			$start->getTimezone()
		);

		$end = clone $start;

		$this->start = $start;
		$this->end = $end->modify($durationModifier);
		$this->capacity = $bookable->slotMultiplier - $bookingCount;
		$this->hasBookings = $bookingCount > 0;
		$this->fullyBooked = $this->capacity === 0;
	}

}