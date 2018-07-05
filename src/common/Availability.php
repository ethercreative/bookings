<?php
/**
 * Bookings plugin for Craft CMS 3.x
 *
 * @link      https://ethercreative.co.uk
 * @copyright Copyright (c) Ether Creative
 */

namespace ether\bookings\common;

use craft\db\Query;
use craft\helpers\Db;
use ether\bookings\elements\Booking;
use ether\bookings\enums\Frequency;
use ether\bookings\models\Bookable;
use ether\bookings\models\RecursionRule;
use ether\bookings\models\Slot;
use ether\bookings\records\BookingRecord;


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

		if ($baseRule->until && $baseRule->repeats === RecursionRule::REPEATS_UNTIL)
			$this->_end = $baseRule->until->format(\DateTime::W3C);

		if ($baseRule->count && $baseRule->count < 1000 && $baseRule->repeats === RecursionRule::REPEATS_UNTIL)
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
		$this->_count = $value;

		return $this;
	}
	
	// Methods: Execution
	// -------------------------------------------------------------------------

	/**
	 * @return array
	 * @throws \yii\base\Exception
	 */
	public function all (): array
	{
		$slots = [];
		$bookings = $this->_bookings();

		// TODO: Booking count needs to take flexible bookings into consideration. Currently only looks at start Date :(

		/** @var \DateTime $slot */
		foreach ($this->_slots() as $i => $slot)
		{
			if (!$this->_end && $this->_count && $i > $this->_count - 1)
				break;

			$dbDate = $slot->format('Y-m-d H:i:s');

			// TODO: Get all bookings by timestamp

			$slots[] = new Slot(
				$this->_field,
				$slot,
				array_key_exists($dbDate, $bookings) ? $bookings[$dbDate] : 0
			);
		}

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

	/**
	 * @return array
	 * @throws \yii\base\Exception
	 * @throws \Exception
	 */
	private function _bookings ()
	{
		$fieldId = $this->_field->fieldId;
		$elementId = $this->_field->ownerId;

		// TODO: Have different queries / logic for fixed vs flexible?

		$results = (new Query())
			->select('slotStart, count(*)')
			->from(BookingRecord::$tableName)
			->where([
				'fieldId' => $fieldId,
				'elementId' => $elementId,
				'status' => [Booking::STATUS_RESERVED, Booking::STATUS_COMPLETED],
			])
			->andWhere(['>=', 'slotStart', $this->_start]);

		if ($this->_end)
			$results->andWhere(['<=', 'slotEnd', $this->_end]);
		else if ($this->_count)
			$results->andWhere(['<=', 'slotEnd', $this->_endDateFromCount()]);

		$results = $results->groupBy('slotStart')->pairs();

		// TODO: Refactor to get all bookings and use PHP to count them by slotStart and slotEnd
		// (We don't need to know if a date is a start or end since they can overlap)
		// Key using a timestamp rather than date
		// Duplicate for each slot within the bookings start - end range?

		return $results;
	}

	/**
	 * @return string
	 * @throws \Exception
	 */
	private function _endDateFromCount ()
	{
		$baseRule = $this->_field->baseRule;
		$mod = '+' . (($baseRule->duration + $baseRule->interval) * $this->_count) . ' ' . Frequency::toUnit($baseRule->frequency);
		return (new \DateTime($this->_start))->modify($mod)->format(\DateTime::W3C);
	}

}