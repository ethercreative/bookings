<?php
/**
 * Bookings plugin for Craft CMS 3.x
 *
 * @link      https://ethercreative.co.uk
 * @copyright Copyright (c) Ether Creative
 */

namespace ether\bookings\helpers;

use craft\helpers\DateTimeHelper;


/**
 * Class DateHelper
 *
 * @author  Ether Creative
 * @package ether\bookings\helpers
 * @since   1.0.0
 */
class DateHelper
{

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
			) {
				$param['time'] = date('g:i a', strtotime($timeLower));
			}
		}

		return DateHelper::toUTCDateTime($param);
	}

	public static function toUTCDateTime ($param)
	{
		if (is_array($param) && array_key_exists('date', $param))
			$param = $param['date'];

		$date = DateTimeHelper::toDateTime($param);

		$date->setTimezone(new \DateTimeZone('UTC'));

		return $date;
	}

}