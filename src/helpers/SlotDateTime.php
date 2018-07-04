<?php
/**
 * Bookings plugin for Craft CMS 3.x
 *
 * @link      https://ethercreative.co.uk
 * @copyright Copyright (c) Ether Creative
 */

namespace ether\bookings\helpers;


/**
 * Class SlotDateTime
 *
 * Simplifies the JSON serialized date output to a W3C date string
 *
 * @author  Ether Creative
 * @package ether\bookings\helpers
 * @since   1.0.0
 */
class SlotDateTime extends \DateTime implements \JsonSerializable
{

	public function jsonSerialize ()
	{
		return $this->format(\DateTime::W3C);
	}

}