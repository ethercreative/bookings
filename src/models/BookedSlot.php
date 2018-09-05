<?php
/**
 * Bookings plugin for Craft CMS 3.x
 *
 * @link      https://ethercreative.co.uk
 * @copyright Copyright (c) Ether Creative
 */

namespace ether\bookings\models;

use craft\helpers\DateTimeHelper;
use ether\bookings\base\Model;
use ether\bookings\records\BookedSlotRecord;


/**
 * Class BookedSlot
 *
 * @author  Ether Creative
 * @package ether\bookings\models
 * @since   1.0.0
 */
class BookedSlot extends Model
{

	// Properties
	// =========================================================================

	/** @var int */
	public $id;

	/** @var bool */
	public $start;

	/** @var bool */
	public $end = false;

	/** @var int */
	public $ticketId;

	/** @var int */
	public $bookingId;

	/** @var int */
	public $bookedTicketId;

	/** @var \DateTime */
	public $date;

	// Methods
	// =========================================================================

	public static function fromRecord (BookedSlotRecord $record)
	{
		$slot = new self();

		$slot->id = $record->id;
		$slot->start = $record->start;
		$slot->end = $record->end;
		$slot->ticketId = $record->ticketId;
		$slot->bookingId = $record->bookingId;
		$slot->bookedTicketId = $record->bookedTicketId;
		$slot->date = DateTimeHelper::toDateTime($record->date);
		$slot->date->setTimezone(new \DateTimeZone('UTC'));

		return $slot;
	}

}