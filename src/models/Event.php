<?php
/**
 * Bookings plugin for Craft CMS 3.x
 *
 * @link      https://ethercreative.co.uk
 * @copyright Copyright (c) Ether Creative
 */

namespace ether\bookings\models;

use craft\helpers\DateTimeHelper;
use ether\bookings\base\Model;
use ether\bookings\enums\EventType;
use RRule\RRule;
use RRule\RSet;
use yii\helpers\Json;


/**
 * Class Event
 *
 * @property RecursionRule $baseRule
 * @property ExceptionRule[] $exceptions
 *
 * @author  Ether Creative
 * @package ether\bookings\models
 * @since   1.0.0
 */
class Event extends Model
{

	// Properties
	// =========================================================================

	const SLOT_HARD_LIMIT = 1000;

	/** @var int */
	public $id;

	/** @var int */
	public $elementId;

	/** @var int */
	public $fieldId;

	/** @var boolean */
	public $enabled = false;

	/**
	 * @var string - The type of bookable (fixed or flexible)
	 * @see EventType
	 */
	public $type = EventType::FIXED;

	/**
	 * @var int - The max number of tickets that can be sold per slot
	 * (or selected slot range if flexible)
	 */
	public $capacity = 1;

	/**
	 * @var int - The number of times a single slot can be sold
	 */
	public $multiplier = 1;

	// Properties: Private
	// -------------------------------------------------------------------------

	/**
	 * @var RecursionRule - The base RRule
	 */
	private $_baseRule;

	/**
	 * @var ExceptionRule[] - An array of exceptions to the base rule
	 */
	private $_exceptions = [];

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

		if (array_key_exists('baseRule', $attributes))
			$this->baseRule = $attributes['baseRule'];

		if (array_key_exists('exceptions', $attributes))
			$this->exceptions = $attributes['exceptions'];
	}

	// Methods: Getters & Setters
	// -------------------------------------------------------------------------

	/**
	 * @return RecursionRule
	 */
	public function getBaseRule (): RecursionRule
	{
		return $this->_baseRule;
	}

	/**
	 * @param $baseRule
	 */
	public function setBaseRule ($baseRule)
	{
		if (is_string($baseRule))
			$baseRule = Json::decode($baseRule);

		if (is_array($baseRule))
			$baseRule = new RecursionRule($baseRule);

		$this->_baseRule = $baseRule;
	}

	/**
	 * @return ExceptionRule[]
	 */
	public function getExceptions (): array
	{
		return $this->_exceptions;
	}

	/**
	 * @param $exceptions
	 */
	public function setExceptions ($exceptions)
	{
		if (is_string($exceptions))
			$exceptions = Json::decode($exceptions);

		$this->_exceptions = $this->_mapExceptions($exceptions);
	}

	// Methods: Public
	// -------------------------------------------------------------------------

	public function rules ()
	{
		$rules = parent::rules();

		$rules[] = [
			['type', 'multiplier', 'baseRule'],
			'required'
		];

		return $rules;
	}

	public function asArray ()
	{
		return [
			'enabled' => $this->enabled,
			'capacity' => $this->capacity,
			'multiplier' => $this->multiplier,
			'settings' => [
				'baseRule' => $this->_baseRule,
				'exceptions' => $this->_exceptions,
				'type' => $this->type,
			],
		];
	}

	/**
	 * Will tell the event to use the inverted set
	 *
	 * ```php
	 * $myEvent->invert()->getAllSlots();
	 * ```
	 *
	 * @return Event
	 */
	public function invert (): Event
	{
		$this->_useInverted = true;
		return $this;
	}

	/**
	 * Returns the slots as an iterable.
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
	 * Gets all slots as an array (to a hard max of SLOT_HARD_LIMIT)
	 *
	 * @return array
	 */
	public function getAllSlots (): array
	{
		return $this->_getSet()->getOccurrences(self::SLOT_HARD_LIMIT);
	}

	public function getSlotsInRangeAsIterable ($start, $end)
	{
		$start = DateTimeHelper::toDateTime($start);
		$end = DateTimeHelper::toDateTime($end);

		$baseStart = $this->_baseRule->start;
		$baseUntil = $this->_baseRule->until;

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
	 * Gets all the slots from te given start as an iterable
	 *
	 * @param \DateTime|string $start
	 *
	 * @return RSet|\Iterator|\ArrayAccess|\Countable
	 */
	public function getSlotsFromAsIterable ($start)
	{
		$start = DateTimeHelper::toDateTime($start);

		$baseStart = $this->_baseRule->start;
		$set       = clone $this->_getSet();

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
		return $this->getSlotsFromAsIterable($start)->getOccurrences($count);
	}

	/**
	 * Returns true if the given date occurs in the events set
	 *
	 * @param \DateTime|string $date
	 *
	 * @return bool
	 */
	public function isDateOccurrence ($date)
	{
		$date = DateTimeHelper::toDateTime($date);
		return $this->_getSet()->occursAt($date);
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

		$set->addRRule($this->_baseRule->asRRuleArray());

		foreach ($this->_exceptions as $ex)
		{
			if ($ex->bookable === false)
			{
				$set->addExRule($ex->asRRuleArray());
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

			$set->addRRule($ex->asRRuleArray());
			$lastRuleWasException = false;
		}

		if ($previousSet)
			$set->addRRule($previousSet);

		return $this->_set = $set;
	}

	/**
	 * Builds the recurrence set, but the base rule is ignored and exceptions
	 * are made bookable while bookables are make exclusions.
	 *
	 * This is used exclusively by the UI, to help visualise exceptions.
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

		foreach ($this->_exceptions as $ex)
		{
			if ($ex->bookable === true)
			{
				$set->addExRule($ex->asRRuleArray());
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

			$set->addRRule($ex->asRRuleArray());
			$lastRuleWasException = false;
		}

		if ($previousSet)
			$set->addRRule($previousSet);

		return $this->_invertedSet = $set;
	}

	/**
	 * Converts an array of exception arrays to actual exceptions
	 *
	 * @param array $exceptions
	 *
	 * @return ExceptionRule[]
	 */
	private function _mapExceptions (array $exceptions)
	{
		return array_map(function ($ex) {
			$exception = new ExceptionRule($ex);

			// Ensure these properties inherit from the base rule
			$exception->frequency = $this->_baseRule->frequency;
			$exception->duration  = $this->_baseRule->duration;

			return $exception;
		}, $exceptions);
	}

}