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
use RRule\RRule;
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

	/** @var RSet|null */
	private $_invertedSet;

	/** @var bool */
	private $_useInverted = false;

	// Methods
	// =========================================================================

	public function __construct (array $attributes = [], array $config = [])
	{
		parent::__construct($attributes, $config);

		if (array_key_exists('exceptions', $attributes))
		{
			$this->exRules = array_map(
				function ($ex) {
					return new ExRule($ex);
				},
				$attributes['exceptions']
			);
		}
	}

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
	 * Returns the bookable as an array for use in the field type
	 *
	 * @return array
	 */
	public function asArray ()
	{
		return [
			'baseRule' => $this->baseRule,
			'exceptions' => $this->exRules,
			'bookableType' => $this->bookableType,
		];
	}

	/**
	 * Will tell the bookable to use the inverted set
	 *
	 * ```php
	 * $myBookable->invert()->getAllSlots();
	 * ```
	 *
	 * @return Bookable
	 */
	public function invert (): Bookable {
		$this->_useInverted = true;
		return $this;
	}

	/**
	 * Gets all slots as an array (to a hard max of 1000)
	 *
	 * @return array
	 */
	public function getAllSlots (): array
	{
		return $this->_getSet()->getOccurrences(1000);
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
		return $this->_getSet();
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

		$set = clone $this->_getSet();

		// If the start time is after the base start time, exclude all slots
		// between those two times
		if ($start->getTimestamp() > $baseStart->getTimestamp())
		{
			$set->addExRule([
                'FREQ'    => RRule::SECONDLY,
                'DTSTART' => $baseStart,
                'UNTIL'   => $start,
			]);
		}

		// If the end time is before the base until time, exclude all slots
		// between those two times
		if ($end->getTimestamp() < $baseUntil->getTimestamp())
		{
			$set->addExRule([
				'FREQ'    => RRule::SECONDLY,
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

		$set = clone $this->_getSet();

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
	private function _getSet (): RSet
	{
		if ($this->_useInverted)
			return $this->_getInvertedSet();

		if ($this->_set)
			return $this->_set;

		$set = new RSet();
		$previousSet = null;
		$lastRuleWasException = false;

		$set->addRRule($this->baseRule->asRRuleArray());

		foreach ($this->exRules as $exRule)
		{
			$exRule->duration = $this->baseRule->duration;

			if (!$exRule->bookable) {
				$set->addExRule($exRule->asRRuleArray());
				$lastRuleWasException = true;
				continue;
			}

			if ($lastRuleWasException)
			{
				if ($previousSet)
					$set->addRRule($previousSet);

				$previousSet = clone $set;
				$set = new RSet();
			}

			$set->addRRule($exRule->asRRuleArray());
			$lastRuleWasException = false;
		}

		if ($previousSet)
			$set->addRRule($previousSet);

		return $this->_set = $set;
	}

	/**
	 * Builds the recurrence set, but the base rule is ignored and exRules are
	 * made bookable while rRules are made exclusions.
	 *
	 * This is used exclusively for the UI, to help visualise exceptions.
	 *
	 * @return RSet
	 */
	private function _getInvertedSet (): RSet
	{
		if ($this->_invertedSet)
			return $this->_invertedSet;

		$set = new RSet();
		$previousSet = null;
		$lastRuleWasException = false;

		foreach ($this->exRules as $exRule)
		{
			$exRule->duration = $this->baseRule->duration;

			if ($exRule->bookable) {
				$set->addExRule($exRule->asRRuleArray());
				$lastRuleWasException = true;
				continue;
			}

			if ($lastRuleWasException)
			{
				if ($previousSet)
					$set->addRRule($previousSet);

				$previousSet = clone $set;
				$set = new RSet();
			}

			$set->addRRule($exRule->asRRuleArray());
			$lastRuleWasException = false;
		}

		if ($previousSet)
			$set->addRRule($previousSet);

		return $this->_invertedSet = $set;
	}

}
