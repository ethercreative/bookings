<?php
/**
 * Bookings for Craft CMS
 *
 * @link      https://ethercreative.co.uk
 * @copyright Copyright (c) 2019 Ether Creative
 */

namespace ether\bookings\common;

use craft\elements\db\ElementQueryInterface;
use ether\bookings\Bookings;
use ether\bookings\elements\Event;

/**
 * Class Availability
 *
 * @author  Ether Creative
 * @package ether\bookings\common
 */
class Availability
{

	// Properties
	// =========================================================================

	/** @var Event */
	private $_event;

	// TODO: Add $_ticket / $_resource

	/** @var \DateTime */
	private $_start;

	/** @var \DateTime */
	private $_end;

	/** @var int */
	private $_count;

	/** @var string string */
	private $_group = 'minute';

	// Constructor
	// =========================================================================

	/**
	 * Availability constructor.
	 *
	 * @param string|int|ElementQueryInterface|Event $event
	 */
	public function __construct ($event)
	{
		if (is_numeric($event))
			$this->_event = Bookings::$i->events->getEventById($event);
		elseif ($event instanceof ElementQueryInterface)
			$this->_event = $event->one();
		else
			$this->_event = $event;

		//
	}

}
