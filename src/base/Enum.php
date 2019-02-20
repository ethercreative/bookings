<?php
/**
 * Bookings for Craft CMS
 *
 * @link      https://ethercreative.co.uk
 * @copyright Copyright (c) 2019 Ether Creative
 */

namespace ether\bookings\base;

/**
 * Class Enum
 *
 * @author  Ether Creative
 * @package ether\bookings\base
 */
class Enum
{

	/**
	 * @return array The constants of this enum as an array
	 */
	public static function asArray (): array
	{
		try {
			return (new \ReflectionClass(get_called_class()))->getConstants();
		} catch (\ReflectionException $exception) {
			\Craft::error($exception->getMessage(), 'Bookings Enum');
		}
	}

}
