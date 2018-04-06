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
	 * TODO: Is this needed, or is RRule enough?
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

	/**
	 * Gets all slots as an array (to a hard max of 1000)
	 *
	 * @return array
	 */
	public function getAllSlots (): array
	{
		return $this->getSet()->getOccurrences(1000);
	}

	/**
	 * Returns the slots as an iterable
	 *
	 * This is the recommended way of accessing the slots, especially if you can
	 * get away with not needing them all generated at once (i.e. not used in JS)
	 *
	 * @return RSet|\Iterator|\ArrayAccess|\Countable
	 */
	public function getAllSlotsAsIterable ()
	{
		return $this->getSet();
	}

	/**
	 * Gets all the slots within the given time frame as an iterable
	 *
	 * @param \DateTime|string $start
	 * @param \DateTime|string $end
	 *
	 * @return RSet|\Iterator|\ArrayAccess|\Countable
	 */
	public function getSlotsInRangeAsIterable ($start, $end)
	{
		if (!$start instanceof \DateTime)
			$start = new \DateTime($start);

		if (!$end instanceof \DateTime)
			$end   = new \DateTime($end);

		$baseStart = $this->baseRule->start;
		$baseUntil = $this->baseRule->until;

		$set = $this->getSet();

		// If the start time is after the base start time, exclude all slots
		// between those two times
		if ($start->getTimestamp() > $baseStart->getTimestamp())
		{
			$set->addExRule([
                'FREQ'    => 'SECONDLY',
                'DTSTART' => $baseStart,
                'UNTIL'   => $start,
			]);
		}

		// If the end time is after the base until time, exclude all slots
		// between those two times
		if ($end->getTimestamp() < $baseUntil->getTimestamp())
		{
			$set->addExRule([
				'FREQ'    => 'SECONDLY',
				'DTSTART' => $end,
				'UNTIL'   => $baseUntil,
			]);
		}

		return $set;
	}

	/**
	 * Gets all the slots withing the given range as an array
	 *
	 * @param \DateTime|string $start
	 * @param \DateTime|string $end
	 *
	 * @return array
	 */
	public function getSlotsInRange ($start, $end): array
	{
		return $this->getSlotsInRangeAsIterable($start, $end)->getOccurrences();
	}

	/**
	 * Gets X number of slots from the given DateTime
	 *
	 * @param \DateTime|string $start
	 * @param int              $count
	 *
	 * @return array
	 */
	public function getSlotsFrom ($start, $count = 100): array
	{
		if (!$start instanceof \DateTime)
			$start = new \DateTime($start);

		$baseStart = $this->baseRule->start;

		$set = $this->getSet();

		// If the start time is after the base start time, exclude all slots
		// between those two times
		if ($start->getTimestamp() > $baseStart->getTimestamp())
		{
			$set->addExRule([
				'FREQ'    => 'SECONDLY',
				'DTSTART' => $baseStart,
				'UNTIL'   => $start,
			]);
		}

		return $set->getOccurrences($count);
	}

	// Methods: Private
	// -------------------------------------------------------------------------

	/**
	 * Builds the recurrence set
	 *
	 * @return RSet
	 */
	private function getSet (): RSet
	{
		if ($this->_set)
			return $this->_set;

		$set = new RSet();
		$previousSet = null;

		$set->addRRule($this->baseRule->asRRuleArray());

		foreach ($this->exRules as $exRule)
		{
			if ($exRule->bookable)
			{
				if ($previousSet)
				{
					$set->addRRule($previousSet);
					$previousSet = clone $set;
					$set = new RSet();
				}

				$set->addRRule($exRule->asRRuleArray());
			}
			else
			{
				$set->addExRule($exRule->asRRuleArray());
			}
		}

		return $this->_set = $set;
	}

}