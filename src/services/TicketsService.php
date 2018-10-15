<?php
/**
 * Bookings plugin for Craft CMS 3.x
 *
 * @link      https://ethercreative.co.uk
 * @copyright Copyright (c) Ether Creative
 */

namespace ether\bookings\services;

use craft\base\Component;
use ether\bookings\elements\BookedTicket;
use ether\bookings\models\Ticket;
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

	public function getTicketTableElement ($eventId, $slot = null)
	{
		$query = BookedTicket::find();
		\Craft::configure(
			$query, [
			'bookingId' => $eventId,
			'startDate' => $slot,
			'limit'     => null,
		]);

		return BookedTicket::indexHtml(
			$query,
			[],
			['mode' => 'table'],
			'',
			null,
			true,
			false
		);
	}

}