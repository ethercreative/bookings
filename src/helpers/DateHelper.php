<?php
/**
 * Bookings for Craft CMS
 *
 * @link      https://ethercreative.co.uk
 * @copyright Copyright (c) 2019 Ether Creative
 */

namespace ether\bookings\helpers;

use craft\helpers\DateTimeHelper;

/**
 * Class DateHelper
 *
 * @author  Ether Creative
 * @package ether\bookings\helpers
 */
class DateHelper
{

	/**
	 * Parses date from post
	 *
	 * @param $param
	 *
	 * @return \DateTime|false
	 * @throws \Exception
	 */
	public static function parseDateFromPost ($param)
	{
		if (!is_array($param))
			return DateHelper::toUTCDateTime($param);

		if (array_key_exists('time', $param))
		{
			$timeLower = strtolower($param['time']);
			if (
				strpos($timeLower, 'am') === false
				|| strpos($timeLower, 'pm') === false
			)
			{
				$param['time'] = date('g:i a', strtotime($timeLower));
			}
		}

		return DateHelper::toUTCDateTime($param);
	}

	/**
	 * Converts the given date to UTC
	 *
	 * @param $param
	 *
	 * @return \DateTime|false
	 * @throws \Exception
	 */
	public static function toUTCDateTime ($param)
	{
		if (is_array($param) && array_key_exists('date', $param))
			$param = $param['date'];

		$date = DateTimeHelper::toDateTime($param);

		if ($date instanceof \DateTime)
			$date->setTimezone(new \DateTimeZone('UTC'));

		return $date;
	}

}
