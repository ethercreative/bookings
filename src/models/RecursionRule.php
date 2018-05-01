<?php
/**
 * Bookings plugin for Craft CMS 3.x
 *
 * @link      https://ethercreative.co.uk
 * @copyright Copyright (c) Ether Creative
 */

namespace ether\bookings\models;

use ether\bookings\base\Model;
use ether\bookings\enums\Frequency;
use RRule\RRule;

/**
 * Class Rule
 *
 * @author  Ether Creative
 * @package ether\bookings\models
 * @since   1.0.0
 */
class RecursionRule extends Model
{

	// Properties
	// =========================================================================

	// Properties: Public
	// -------------------------------------------------------------------------

	/**
	 * @var string The repetition the rule is restricted by (i.e. count, until)
	 */
	public $repeats;

	/**
	 * @var string The frequency of the rule
	 * @see Frequency
	 */
	public $frequency;

	/**
	 * @var \DateTime The rules start datetime
	 */
	public $start;

	/**
	 * @var int How long, in frequency, each slot should be. This isn't part of
	 *          the RRule spec or PHP lib. All it does is increase the interval
	 *          and by the given amount to get the offsets correct. Everything
	 *          else is front-end.
	 */
	public $duration = 1;

	/**
	 * @var int The interval between each frequency iteration.
	 *          For example, when using YEARLY, an interval of 2 means once
	 *          every two years, but with HOURLY, it means once every two hours.
	 */
	public $interval = 0;

	/**
	 * @var int|null The number of slots to generate
	 */
	public $count;

	/**
	 * @var \DateTime|null The datetime to generate up to
	 */
	public $until;

	/**
	 * @var int[]|null The months this rule should be applied to.
	 *                 If null, the rule can be applied to all months.
	 */
	public $byMonth;

	/**
	 * @var int[]|null The weeks this rule should be applied to.
	 *                 If null, the rule can be applied to all weeks.
	 */
	public $byWeekNumber;

	/**
	 * @var int[]|null The days of the year this rule should be applied to.
	 *                 If null, the rule can be applied to all days.
	 */
	public $byYearDay;

	/**
	 * @var int[]|null The days of each month this rule should be applied to.
	 *                 If null, the rule can be applied to all days.
	 */
	public $byMonthDay;

	/**
	 * @var string[]|null The days of the week this rule should be applied to.
	 *                    If null, this rule can be applied to all days.
	 *                    Each day can be preceded by a number, indicating a
	 *                    specific occurrence within the interval.
	 *                    For example: 1MO (the first Monday of the interval),
	 *                    3MO (the third Monday), -1MO (the last Monday), etc.
	 */
	public $byDay;

	/**
	 * @var int[]|null The hours this rule should be applied to.
	 *                 If null, this rule can be applied to all hours.
	 */
	public $byHour;

	/**
	 * @var int[]|null The minutes this rule should be applied to.
	 *                 If null, this rule can be applied to all minutes.
	 */
	public $byMinute;

	/**
	 * @var int[]|null The Nth occurrence(s) within the valid occurrences inside
	 *                 a frequency period. Negative values mean that the count
	 *                 starts from the set. For example, a bySetPosition of -1
	 *                 if combined with a MONTHLY frequency, and a byDay of
	 *                 'MO,TU,WE,TH FR', will result in the last work day of
	 *                 every month.
	 */
	public $bySetPosition;

	// Methods
	// =========================================================================

	// Methods: Public
	// -------------------------------------------------------------------------

	public function rules ()
	{
		$rules = parent::rules();

		$rules[] = [
			['frequency', 'dtStart', 'interval'],
			'required',
		];

		$rules[] = [
			['frequency'],
			'in', 'range' => Frequency::asArray()
		];

		$rules[] = [
			['dtStart'],
			'datetime',
		];

		$rules[] = [
			['interval'],
			'integer',
		];

		$rules[] = [
			['byMonth'],
			'in',
			'range' => range(1, 12),
			'allowArray' => true,
		];

		$rules[] = [
			['byWeekNumber'],
			'in',
			'range' => range(1, 53),
			'allowArray' => true,
		];

		$rules[] = [
			['byYearDay'],
			'in',
			'range' => range(1, 366),
			'allowArray' => true,
		];

		$rules[] = [
			['byMonthDay'],
			'in',
			'range' => range(1, 31),
			'allowArray' => true,
		];

		$rules[] = [
			['byDay'],
			'each',
			'rule' => [
				'match',
				'pattern' => '/-?\d?\w{2}/',
			]
		];

		$rules[] = [
			['byHour'],
			'in',
			'range' => range(0, 23),
			'allowArray' => true,
		];

		$rules[] = [
			['byMinute'],
			'in',
			'range' => range(0, 59),
			'allowArray' => true,
		];

		$rules[] = [
			['bySetPosition'],
			'each',
			'rule' => ['integer'],
		];

		return $rules;
	}

	/**
	 * @return array Returns this rule as an RRule compatible array
	 */
	public function asRRuleArray (): array
	{
		$rRule = [
			'FREQ'     => RRule::$frequencies[$this->frequency],
			'DTSTART'  => $this->start,
			'INTERVAL' => $this->interval + $this->duration,
		];

		if ($this->count && $this->repeats === 'count')
			$rRule['COUNT'] = (int) $this->count;

		if ($this->until && $this->repeats === 'until')
			$rRule['UNTIL'] = $this->until;

		if (!empty($this->byMonth))
			$rRule['BYMONTH'] = $this->byMonth;

		if (!empty($this->byWeekNumber))
			$rRule['BYWEEKNO'] = $this->byWeekNumber;

		if (!empty($this->byYearDay))
			$rRule['BYYEARDAY'] = $this->byYearDay;

		if (!empty($this->byMonthDay))
			$rRule['BYMONTHDAY'] = $this->byMonthDay;

		if (!empty($this->byDay))
			$rRule['BYDAY'] = $this->byDay;

		if (!empty($this->byMinute))
			$rRule['BYMINUTE']  = $this->byMinute;

		if (!empty($this->bySetPosition))
			$rRule['BYSETPOS'] = $this->bySetPosition;

		return $rRule;
	}

	/**
	 * @return RRule
	 */
	public function asRRule (): RRule
	{
		return new RRule($this->asRRuleArray());
	}

}