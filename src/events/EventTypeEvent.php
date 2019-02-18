<?php
/**
 * Bookings for Craft CMS
 *
 * @link      https://ethercreative.co.uk
 * @copyright Copyright (c) 2019 Ether Creative
 */

namespace ether\bookings\events;

use ether\bookings\models\EventType;
use yii\base\Event;

/**
 * Class EventTypeEvent
 *
 * @author  Ether Creative
 * @package ether\bookings\events
 */
class EventTypeEvent extends Event
{

	// Properties
	// =========================================================================

	/**
	 * @var EventType|null The event type model associated with the event.
	 */
	public $eventType;

	/**
	 * @var bool Whether the event type is new
	 */
	public $isNew = false;

}
