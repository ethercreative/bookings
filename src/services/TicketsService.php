<?php
/**
 * Bookings plugin for Craft CMS 3.x
 *
 * @link      https://ethercreative.co.uk
 * @copyright Copyright (c) Ether Creative
 */

namespace ether\bookings\services;

use craft\base\Component;
use craft\db\Query;
use craft\helpers\DateTimeHelper;
use craft\helpers\Db;
use craft\helpers\Json;
use ether\bookings\elements\BookedTicket;
use ether\bookings\elements\Booking;
use ether\bookings\helpers\DateHelper;
use ether\bookings\models\Event;
use ether\bookings\models\Ticket;
use ether\bookings\records\BookingRecord;
use ether\bookings\records\TicketRecord;


/**
 * Class TicketsService
 *
 * @author  Ether Creative
 * @package ether\bookings\services
 * @since   1.0.0
 */
class TicketsService extends Component
{

	// Properties
	// =========================================================================

	public static $previousTicketDate = null;

	// Methods
	// =========================================================================

	// Ticket
	// -------------------------------------------------------------------------

	/**
	 * Finds the ticket with the given ID
	 *
	 * @param $ticketId
	 *
	 * @return Ticket|null
	 */
	public function getTicketById ($ticketId)
	{
		$record = TicketRecord::findOne([
			'id' => $ticketId,
		]);

		if (!$record)
			return null;

		return Ticket::fromRecord($record);
	}

	// Booked Ticket
	// -------------------------------------------------------------------------

	/**
	 * Finds the booked ticket with the given ID
	 *
	 * @param $bookedTicketId
	 *
	 * @return \craft\base\ElementInterface|null|BookedTicket
	 */
	public function getBookedTicketById ($bookedTicketId)
	{
		return BookedTicket::find()->id($bookedTicketId)->one();
	}

	/**
	 * @param BookedTicket $ticket
	 * @param bool         $runValidation
	 * @param bool         $propagate
	 *
	 * @return bool
	 * @throws \Throwable
	 * @throws \craft\errors\ElementNotFoundException
	 * @throws \yii\base\Exception
	 */
	public function saveBookedTicket (BookedTicket $ticket, bool $runValidation = true, bool $propagate = true)
	{
		return \Craft::$app->elements->saveElement($ticket, $runValidation, $propagate);
	}

}