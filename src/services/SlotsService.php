<?php
/**
 * Bookings plugin for Craft CMS 3.x
 *
 * @link      https://ethercreative.co.uk
 * @copyright Copyright (c) Ether Creative
 */

namespace ether\bookings\services;

use craft\base\Component;
use ether\bookings\models\Event;


/**
 * Class SlotsService
 *
 * @author  Ether Creative
 * @package ether\bookings\services
 * @since   1.0.0
 */
class SlotsService extends Component
{

	/**
	 * @param Event          $event
	 * @param \DateTime      $start
	 * @param \DateTime|null $end
	 *
	 * @return \DateTime[]|\RRule\RSet
	 */
	public function generateSlotsForGivenTimes (Event $event, \DateTime $start, \DateTime $end = null)
	{
		if ($end === null)
		{
			return $event->getSlotsFrom($start, 1);
		}

		return $event->getSlotsInRangeAsIterable($start, $end);
	}

}