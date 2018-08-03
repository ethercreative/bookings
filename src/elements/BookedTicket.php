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
use ether\bookings\records\BookedTicketRecord;


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

		$record->save();

		parent::afterSave($isNew);
	}

}