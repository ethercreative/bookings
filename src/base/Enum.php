<?php
/**
 * Bookings plugin for Craft CMS 3.x
 *
 * @link      https://ethercreative.co.uk
 * @copyright Copyright (c) Ether Creative
 */

namespace ether\bookings\base;


/**
 * Class Enum
 *
 * @author  Ether Creative
 * @package ether\bookings\base
 * @since   1.0.0
 */
class Enum
{

	/**
	 * @return array The constants of this enum as an array
	 */
	public static function asArray (): array
	{
		$consts = [];

		try {
			$consts = (new \ReflectionClass(get_called_class()))->getConstants();
		} catch (\ReflectionException $exception) {
			\Craft::error($exception->getMessage(), 'Bookings Enum');
		}

		return $consts;
	}

}