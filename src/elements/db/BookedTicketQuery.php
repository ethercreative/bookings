<?php
/**
 * Bookings plugin for Craft CMS 3.x
 *
 * @link      https://ethercreative.co.uk
 * @copyright Copyright (c) Ether Creative
 */

namespace ether\bookings\elements\db;

use craft\elements\db\ElementQuery;
use craft\helpers\DateTimeHelper;
use craft\helpers\Db;
use ether\bookings\elements\Booking;
use ether\bookings\models\Ticket;
use ether\bookings\records\BookedTicketRecord;


/**
 * Class BookedTicketQuery
 *
 * @author  Ether Creative
 * @package ether\bookings\elements\db
 * @since   1.0.0
 */
class BookedTicketQuery extends ElementQuery
{

	// Properties
	// =========================================================================

	/** @var int */
	public $ticketId;

	/** @var int */
	public $bookingId;

	/** @var int */
	public $lineItemId;

	/** @var \DateTime */
	public $startDate;

	/** @var \DateTime|null */
	public $endDate;

	// Methods
	// =========================================================================

	// Methods: Setters
	// -------------------------------------------------------------------------

	public function ticket ($value)
	{
		if ($value instanceof Ticket)
			$this->ticketId = $value->id;
		else
			$this->ticketId = $value;

		return $this;
	}

	public function booking ($value)
	{
		if ($value instanceof Booking)
			$this->bookingId = $value->id;
		else
			$this->bookingId = $value;

		return $this;
	}

	public function lineItem ($value)
	{
		if (
			class_exists(\craft\commerce\models\LineItem::class)
			&& $value instanceof \craft\commerce\models\LineItem
		)
			$this->lineItemId = $value->id;
		else
			$this->lineItemId = $value;

		return $this;
	}

	public function startDate ($value)
	{
		if ($value instanceof \DateTime)
			$this->startDate = $value;
		else
			$this->startDate = DateTimeHelper::toDateTime($value);

		return $this;
	}

	public function endDate ($value)
	{
		if ($value instanceof \DateTime)
			$this->endDate = $value;
		else if ($value)
			$this->endDate = DateTimeHelper::toDateTime($value);
		else
			$this->endDate = $value;

		return $this;
	}

	// Methods: Protected
	// -------------------------------------------------------------------------

	protected function beforePrepare (): bool
	{
		$table = BookedTicketRecord::$tableNameUnprefixed;

		$this->joinElementTable($table);

		$this->query->select([
			$table . '.ticketId',
			$table . '.bookingId',
			$table . '.lineItemId',
			$table . '.startDate',
			$table . '.endDate',
		]);

		if ($this->ticketId)
			$this->subQuery->andWhere(
				Db::parseParam(
					$table . '.ticketId',
					$this->ticketId
				)
			);

		if ($this->bookingId)
			$this->subQuery->andWhere(
				Db::parseParam(
					$table . '.bookingId',
					$this->bookingId
				)
			);

		if ($this->lineItemId)
			$this->subQuery->andWhere(
				Db::parseParam(
					$table . '.lineItemId',
					$this->lineItemId
				)
			);

		if ($this->startDate)
			$this->subQuery->andWhere(
				Db::parseParam(
					$table . '.startDate',
					Db::prepareDateForDb($this->startDate)
				)
			);

		if ($this->endDate)
			$this->subQuery->andWhere(
				Db::parseParam(
					$table . '.endDate',
					Db::prepareDateForDb($this->endDate)
				)
			);

		return parent::beforePrepare();
	}

}