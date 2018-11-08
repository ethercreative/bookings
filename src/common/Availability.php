<?php
/**
 * Bookings plugin for Craft CMS 3.x
 *
 * @link      https://ethercreative.co.uk
 * @copyright Copyright (c) Ether Creative
 */

namespace ether\bookings\common;


use craft\db\Query;
use craft\helpers\DateTimeHelper;
use ether\bookings\elements\Booking;
use ether\bookings\enums\Frequency;
use ether\bookings\helpers\DateHelper;
use ether\bookings\models\Event;
use ether\bookings\models\RecursionRule;
use ether\bookings\models\Ticket;
use ether\bookings\records\BookedSlotRecord;

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

	/** @var Event */
	private $_event;

	/** @var Ticket */
	private $_ticket;

	/** @var \DateTime */
	private $_start;

	/** @var \DateTime */
	private $_end;

	/** @var int */
	private $_count;

	/** @var string */
	private $_group = 'minute';

	// Constructor
	// =========================================================================

	/**
	 * Availability constructor.
	 *
	 * @param Event $event
	 *
	 * @throws \Exception
	 */
	public function __construct (Event $event)
	{
		$this->_event = $event;

		$baseRule = $event->baseRule;

		$this->_start = $baseRule->start;

		$this->_group = rtrim(Frequency::toUnit($baseRule->frequency), 's');

		if ($baseRule->until && $baseRule->repeats === RecursionRule::REPEATS_UNTIL)
			$this->_end = $baseRule->until;

		if ($baseRule->count && $baseRule->count < 1000 && $baseRule->repeats === RecursionRule::REPEATS_COUNT)
			$this->_count = $baseRule->count;
	}

	// Methods
	// =========================================================================

	// Methods: Setters
	// -------------------------------------------------------------------------

	public function ticket ($value)
	{
		$this->_ticket = $value;

		return $this;
	}

	public function start ($value)
	{
		$this->_start = DateHelper::toUTCDateTime($value);

		return $this;
	}

	public function end ($value)
	{
		$this->_end = DateHelper::toUTCDateTime($value);

		return $this;
	}

	public function limit ($value)
	{
		$this->_count = $value;

		return $this;
	}

	/**
	 * Allows grouping of the results by frequency. If your frequency is
	 * smaller
	 * or equal to your Event base rue frequency, the results will not be
	 * grouped.
	 *
	 * Accepts:
	 *  - hour
	 *  - day
	 *  - week
	 *  - month
	 *  - year
	 *
	 * @param string|null $value
	 *
	 * @return $this
	 * @throws \Exception
	 */
	public function groupBy ($value)
	{
		$value = strtolower($value);

		if (Frequency::isUnitLowerOrEqualToFrequency(
			$value . 's',
			$this->_event->baseRule->frequency
		)) return $this;

		$this->_group = $value;

		return $this;
	}

	// Methods: Execution
	// -------------------------------------------------------------------------

	/**
	 * @return array
	 * @throws \yii\base\Exception
	 */
	public function all ()
	{
		$groupedSlots = $this->_groupedSlots();
		$bookedSlots = $this->_bookedSlots();

		foreach ($groupedSlots as $date => $count)
			if (array_key_exists($date, $bookedSlots))
				$groupedSlots[$date] -= $bookedSlots[$date];

		return $groupedSlots;
	}

	// Helpers
	// =========================================================================

	private function _groupedSlots ()
	{
		$slots        = $this->_slots();
		$format       = $this->_getDateFormat();
		$groupedSlots = [];

		$limit   = $this->_count === null ? PHP_INT_MAX : $this->_count;
		$slotMax = $this->_event->multiplier;

		/** @var \DateTime $slot */
		foreach ($slots as $i => $slot)
		{
			if ($i > $limit)
				break;

			$key = $slot->format($format);

			if (array_key_exists($key, $groupedSlots))
				$groupedSlots[$key] += $slotMax;
			else
				$groupedSlots[$key] = $slotMax;
		}

		return $groupedSlots;
	}

	/**
	 * @return array
	 * @throws \yii\base\Exception|\Exception
	 */
	private function _bookedSlots ()
	{
		$group = $this->_groupBy('date') . ' as slot';

		$start = $this->_start->format(\DateTime::W3C);
		$end = $this->_end ? $this->_end->format(\DateTime::W3C) : null;

		$where = ['eventId' => $this->_event->id];

		if ($this->_ticket)
			$where['ticketId'] = $this->_ticket->id;

		$subQuery = (new Query())
			->select([$group, 'bookingId'])
			->from(BookedSlotRecord::$tableName)
			->where($where)
			->andWhere(['>=', 'date', $start]);

		if ($end)
			$subQuery = $subQuery->andWhere(['<=', 'date', $end]);
		else if ($this->_count)
			$subQuery = $subQuery->andWhere(['<=', 'date', $this->_endDateFromCount()]);

		$subQuery = $subQuery->groupBy(['bookingId', 'slot']);

		$query = (new Query())
			->select(['slot', 'count(*)'])
			->from(['a' => $subQuery])
			->groupBy('slot');

		return $query->pairs();
	}

	private function _slots ()
	{
		if ($this->_end)
			return $this->_event->getSlotsInRange(
				$this->_start,
				$this->_end
			);

		return $this->_event->getSlotsFrom($this->_start, $this->_count);
	}

	private function _getDateFormat ($lang = 'php')
	{
		$format = null;

		switch ($lang)
		{
			case 'postgres':
				switch ($this->_group)
				{
					case 'minute':
						$format = "YYYY-MM-DD HH24:MI:00"; break;
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
				break;

			case 'mysql':
				switch ($this->_group)
				{
					case 'minute':
						$format = "%Y-%m-%d %H:%i:00"; break;
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
				break;

			case 'php':
			default:
				switch ($this->_group)
				{
					case 'minute':
						$format = 'Y-m-d H:i:00'; break;
					case 'hour':
						$format = 'Y-m-d H:00:00'; break;
					case 'day':
						$format = 'Y-m-d 00:00:00'; break;
					case 'week':
						$format = 'Y-W 00:00:00'; break;
					case 'month':
						$format = 'Y-m-01 00:00:00'; break;
					case 'year':
						$format = 'Y-01-01 00:00:00'; break;
				}
		}

		return $format;
	}

	private function _groupBy ($column)
	{
		if (!$this->_group)
			return $column;

		if (\Craft::$app->db->getIsPgsql())
		{
			$function = 'to_char';
			$format = $this->_getDateFormat('postgres');
		}
		else
		{
			$function = 'DATE_FORMAT';
			$format = $this->_getDateFormat('mysql');
		}

		if ($format)
			return "$function([[$column]], '$format')";

		return $column;
	}

	/**
	 * @return string
	 * @throws \Exception
	 */
	private function _endDateFromCount ()
	{
		$baseRule = $this->_event->baseRule;
		$mod = '+';
		$mod .= (($baseRule->duration + $baseRule->interval) * $this->_count);
		$mod .= ' ' . Frequency::toUnit($baseRule->frequency);

		return (clone $this->_start)
			->modify($mod)
			->format(\DateTime::W3C);
	}

}