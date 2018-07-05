<?php
/**
 * Bookings plugin for Craft CMS 3.x
 *
 * @link      https://ethercreative.co.uk
 * @copyright Copyright (c) Ether Creative
 */

namespace ether\bookings\models;

use ether\bookings\helpers\SlotDateTime;


/**
 * Class GroupedSlot
 *
 * @author  Ether Creative
 * @package ether\bookings\models
 * @since   1.0.0
 */
class GroupedSlot
{

	// Properties
	// =========================================================================

	/** @var \DateTime|string - When the group starts */
	public $start;

	/** @var \DateTime|string - When the group ends (start + slotCount * duration) */
	public $end;

	/** @var int - The total available capacity for all slots in this group */
	public $capacity;

	/** @var boolean */
	public $hasBookings;

	/** @var boolean */
	public $fullyBooked;

	// Constructor
	// =========================================================================

	/**
	 * GroupedSlot constructor.
	 *
	 * @param Bookable $bookable
	 * @param string   $group
	 * @param string   $start
	 * @param int      $bookingCount
	 */
	public function __construct (Bookable $bookable, string $group, string $start, int $bookingCount)
	{
		if ($group === 'week')
		{
			preg_match(
				'/(\d{4})\-(\d{2}) (\d{2})\:(\d{2}):(\d{2})/m',
				$start,
				$matches
			);
			list(, $year, $week, $hour, $minute, $second) = $matches;
			$start = new SlotDateTime();
			$start->setISODate($year, $week);
			$start->setTime($hour, $minute, $second);

			$this->start = $start;
		}
		else $this->start = new SlotDateTime($start);

		$this->end = (clone $this->start)->modify('+1 ' . $group);
		$this->capacity = $this->_slotCount($bookable) - $bookingCount;
		$this->hasBookings = $bookingCount > 0;
		$this->fullyBooked = $this->capacity === 0;
	}

	// Helpers
	// =========================================================================

	/**
	 * Returns the number of slots in this groups range
	 *
	 * @param Bookable $bookable
	 *
	 * @return int
	 */
	private function _slotCount (Bookable $bookable)
	{
		return $bookable->getSlotsInRangeAsIterable(
			$this->start,
			$this->end
		)->count() * $bookable->slotMultiplier;
	}

}