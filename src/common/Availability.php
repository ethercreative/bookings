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
use ether\bookings\enums\BookableType;
use ether\bookings\enums\Frequency;
use ether\bookings\models\Bookable;
use ether\bookings\models\GroupedSlot;
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

	/** @var string|null */
	private $_group;

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
	 * @param \DateTime|string|null $value
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
	 * FIXME: Not working for some reason
	 *
	 * @param \DateTime|string|null $value
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
	 * @param int|null $value
	 *
	 * @return static
	 */
	public function count ($value)
	{
		$this->_count = $value;

		return $this;
	}

	/**
	 * Allows grouping of the results by frequency. If your frequency is smaller
	 * or equal to your Bookable base rule frequency, the results will not be grouped.
	 *
	 * Accepts:
	 *   - `hour`
	 *   - `day`
	 *   - `week`
	 *   - `month`
	 *   - `year`
	 *
	 * @param string|null $value
	 *
	 * @return $this
	 * @throws \Exception
	 */
	public function groupBy ($value)
	{
		if (Frequency::isUnitLowerOrEqualToFrequency(
			$value . 's',
			$this->_field->baseRule->frequency
		)) return $this;

		$this->_group = strtolower($value);

		return $this;
	}
	
	// Methods: Execution
	// -------------------------------------------------------------------------

	/**
	 * @return array
	 * @throws \yii\base\Exception
	 * @throws \Exception
	 */
	public function all (): array
	{
		$slots = [];
		$bookings = $this->_bookings();

		if ($this->_group)
		{
			foreach ($bookings as $date => $count)
			{
				$slots[] = new GroupedSlot(
					$this->_field,
					$this->_group,
					$date,
					$count
				);
			}
		}
		else
		{
			/** @var \DateTime $slot */
			foreach ($this->_slots() as $i => $slot)
			{
				if (!$this->_end && $this->_count && $i > $this->_count - 1)
					break;

				$dbDate = $slot->format('Y-m-d H:i:s');

				$bookingCount =
					array_key_exists($dbDate, $bookings)
						? $bookings[$dbDate]
						: 0;

				$slots[] = new Slot(
					$this->_field,
					$slot,
					$bookingCount
				);
			}
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
		if ($this->_field->bookableType === BookableType::FIXED)
			return $this->_bookingsFixed();

		return $this->_bookingsFlexible();
	}

	/**
	 * @return array
	 * @throws \yii\base\Exception
	 * @throws \Exception
	 */
	private function _bookingsFixed ()
	{
		$field = $this->_field;
		$fieldId = $field->fieldId;
		$elementId = $field->ownerId;

		$group = $this->_groupBy('slotStart') . ' as slot';

		$results = (new Query())
			->select([$group, 'count(id)'])
			->from(BookingRecord::$tableName)
			->where([
				'fieldId' => $fieldId,
				'elementId' => $elementId,
				'status' => [Booking::STATUS_RESERVED, Booking::STATUS_COMPLETED],
			])
			->andWhere(['>=', 'slotStart', $this->_start]);

		if ($this->_end)
			$results->andWhere(['<=', 'slotStart', $this->_end]);
		else if ($this->_count)
			$results->andWhere(['<=', 'slotStart', $this->_endDateFromCount()]);

		$results = $results->groupBy('slot');

		return $results->pairs();
	}

	/**
	 * @return array
	 */
	private function _bookingsFlexible ()
	{
		// TODO

		// TODO: Refactor to get all bookings and use PHP to count them by slotStart and slotEnd
		// (We don't need to know if a date is a start or end since they can overlap)
		// Key using a timestamp rather than date
		// Duplicate for each slot within the bookings start - end range?

		// TODO: We need each slot that a flexible booking covers
		// TODO: We need a count of each booking that flexible slots cover (can overlap)

//		$results = (new Query())
//			->select('slotStart, count(*)')
//			->from(BookingRecord::$tableName)
//			->where([
//				'fieldId' => $fieldId,
//				'elementId' => $elementId,
//				'status' => [Booking::STATUS_RESERVED, Booking::STATUS_COMPLETED],
//			])
//			->andWhere(['>=', 'slotStart', $this->_start]);
//
//		if ($this->_end)
//			$results->andWhere(['<=', 'slotEnd', $this->_end]);
//		else if ($this->_count)
//			$results->andWhere(['<=', 'slotEnd', $this->_endDateFromCount()]);
//
//		$results = $results->groupBy('slotStart')->pairs();

		return [];
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

	/**
	 * @param string $column - The column to group by
	 *
	 * @return string|array
	 */
	private function _groupBy ($column)
	{
		if (!$this->_group)
			return $column;

		$format = null;

		if (\Craft::$app->db->getIsPgsql())
		{
			$function = 'to_char';

			switch ($this->_group)
			{
				case 'hour':
					 $format = "YYYY-MM-DD HH24:00:00"; break;
				case 'day':
					 $format = "YYYY-MM-DD 00:00:00"; break;
				case 'week':
					 $format = "YYYY-WW 00:00:00"; break;
				case 'month':
					 $format = "YYYY-MM-01 00:00:00"; break;
				case 'year':
					 $format = "YYYY-01-01 00:00:00"; break;
			}
		}
		else
		{
			$function = 'DATE_FORMAT';

			switch ($this->_group)
			{
				case 'hour':
					$format = "%Y-%m-%d %H:00:00"; break;
				case 'day':
					$format = "%Y-%m-%d 00:00:00"; break;
				case 'week':
					$format = "%Y-%u 00:00:00"; break;
				case 'month':
					$format = "%Y-%m-01 00:00:00"; break;
				case 'year':
					$format = "%Y-01-01 00:00:00"; break;
			}
		}

		if ($format)
			return "$function({{%$column}}, '$format')";

		return $column;
	}

}