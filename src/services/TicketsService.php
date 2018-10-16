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
use ether\bookings\elements\BookedTicket;
use ether\bookings\elements\Booking;
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
		$events = (new Query())
			->select(['booking.id'])
			->from([BookingRecord::$tableName . ' booking'])
			->where([
				'booking.eventId' => $eventId,
				'booking.status' => Booking::STATUS_COMPLETED,
			]);

		$ids = array_reduce($events->all(), function ($a, $b) {
			$a[] = $b['id'];
			return $a;
		}, []);

		$query = BookedTicket::find();
		\Craft::configure($query, [
			'bookingId' => $ids,
			'startDate' => $slot,
			'limit'     => null,
		]);

		return BookedTicket::indexHtml(
			$query,
			[],
			[
				'mode' => 'table',
				'order'=> 'startDate',
				'sort' => 'asc',
			],
			'',
			null,
			true,
			false
		);
	}

}