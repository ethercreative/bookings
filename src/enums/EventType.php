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
 * Class BookableType
 *
 * @author  Ether Creative
 * @package ether\bookings\enums
 * @since   1.0.0
 */
abstract class EventType extends Enum
{

	// Constants
	// =========================================================================

	const FIXED    = 'fixed';
	const FLEXIBLE = 'flexible';


}