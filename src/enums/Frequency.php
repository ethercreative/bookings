<?php
/**
 * Bookings plugin for Craft CMS 3.x
 *
 * @link      https://ethercreative.co.uk
 * @copyright Copyright (c) Ether Creative
 */

namespace ether\bookings\enums;

use ether\bookings\base\Enum;

/**
 * Class Frequency
 *
 * @author  Ether Creative
 * @package ether\bookings\enums
 * @since   1.0.0
 */
abstract class Frequency extends Enum
{

	// Constants
	// =========================================================================

	const Yearly = 'YEARLY';
	const Monthly = 'MONTHLY';
	const Weekly = 'WEEKLY';
	const Daily = 'DAILY';
	const Hourly = 'HOURLY';
	const Minutely = 'MINUTELY';

	// Methods
	// =========================================================================

	/**
	 * @param string $frequency
	 *
	 * @return string
	 * @throws \Exception
	 */
	public static function toUnit ($frequency)
	{
		switch ($frequency)
		{
			case self::Yearly:
				return 'years';
			case self::Monthly:
				return 'months';
			case self::Weekly:
				return 'weeks';
			case self::Daily:
				return 'days';
			case self::Hourly:
				return 'hours';
			case self::Minutely:
				return 'minutes';
			default:
				throw new \Exception('Unknown frequency: ' . $frequency);
		}
	}

	/**
	 * @param $unit
	 * @param $frequency
	 *
	 * @return bool
	 * @throws \Exception
	 */
	public static function isUnitLowerOrEqualToFrequency ($unit, $frequency)
	{
		$units = ['minutes', 'hours', 'days', 'weeks', 'months', 'years'];

		return array_search($unit, $units) <= array_search(self::toUnit($frequency), $units);
	}

}