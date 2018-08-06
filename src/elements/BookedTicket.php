<?php
/**
 * Bookings plugin for Craft CMS 3.x
 *
 * @link      https://ethercreative.co.uk
 * @copyright Copyright (c) Ether Creative
 */

namespace ether\bookings\elements;

use craft\base\Element;
use craft\elements\db\ElementQueryInterface;
use ether\bookings\elements\db\BookedTicketQuery;
use ether\bookings\integrations\commerce\CommerceGetters;
use ether\bookings\models\BookedSlot;
use ether\bookings\models\Ticket;
use ether\bookings\records\BookedSlotRecord;
use ether\bookings\records\BookedTicketRecord;
use ether\bookings\records\TicketRecord;


/**
 * Class BookedTicket
 *
 * @author  Ether Creative
 * @package ether\bookings\elements
 * @since   1.0.0
 */
class BookedTicket extends Element
{

	// Properties
	// =========================================================================

	/** @var int */
	public $ticketId;

	/** @var int */
	public $bookingId;

	/** @var int|null */
	public $lineItemId;

	/** @var \DateTime */
	public $startDate;

	/** @var \DateTime|null */
	public $endDate = null;

	// Properties: Private
	// -------------------------------------------------------------------------

	private $_lineItem;

	// Methods
	// =========================================================================

	public static function find (): ElementQueryInterface
	{
		return new BookedTicketQuery(self::class);
	}

	// Events
	// -------------------------------------------------------------------------

	/**
	 * @param bool $isNew
	 *
	 * @throws \Exception
	 */
	public function afterSave (bool $isNew)
	{
		if ($isNew)
		{
			$record = new BookedTicketRecord();
			$record->id = $this->id;
		}
		else
		{
			$record = BookedTicketRecord::findOne($this->id);

			if ($record === null)
				throw new \Exception('Invalid Booked Ticket ID: ' . $this->id);
		}

		$record->ticketId = $this->ticketId;
		$record->bookingId = $this->bookingId;
		$record->lineItemId = $this->lineItemId;
		$record->startDate = $this->startDate;
		$record->endDate = $this->endDate;

		$record->save();

		parent::afterSave($isNew);
	}

	/**
	 * @throws \Throwable
	 * @throws \yii\db\StaleObjectException
	 */
	public function afterDelete ()
	{
		BookedTicketRecord::findOne([
			'id' => $this->id,
		])->delete();

		parent::afterDelete();
	}

	// Getters
	// -------------------------------------------------------------------------

	public function getBooking ()
	{
		return Booking::findOne($this->bookingId);
	}

	public function getTicket ()
	{
		return Ticket::fromRecord(TicketRecord::findOne($this->ticketId));
	}

	public function getLineItem ()
	{
		if ($this->lineItemId === null)
			return null;

		if ($this->_lineItem)
			return $this->_lineItem;

		if (class_exists(\craft\commerce\models\LineItem::class))
			return $this->_lineItem = CommerceGetters::getLineItemById($this->lineItemId);

		return null;
	}

	public function getSlots ()
	{
		return array_map(function ($r) {
			return BookedSlot::fromRecord($r);
		}, BookedSlotRecord::findAll([
			'bookedTicketId' => $this->id,
		]));
	}

}