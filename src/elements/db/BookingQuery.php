<?php
/**
 * Bookings plugin for Craft CMS 3.x
 *
 * @link      https://ethercreative.co.uk
 * @copyright Copyright (c) Ether Creative
 */

namespace ether\bookings\elements\db;

use craft\base\ElementInterface;
use craft\elements\db\ElementQuery;
use craft\elements\User;
use craft\helpers\DateTimeHelper;
use craft\helpers\Db;
use ether\bookings\models\Event;
use ether\bookings\records\BookingRecord;

/**
 * Class BookingQuery
 *
 * @author  Ether Creative
 * @package ether\bookings\elements\db
 * @since   1.0.0
 */
class BookingQuery extends ElementQuery
{

	// Properties
	// =========================================================================

	/** @var int */
	public $status;

	/** @var string */
	public $number;

	/** @var int */
	public $eventId;

	/** @var int */
	public $userId;

	/** @var int */
	public $orderId;

	/** @var int */
	public $customerId;

	/** @var string */
	public $customerEmail;

	/** @var \DateTime */
	public $slot;

	// TODO: Date Booked / Reservation Expiry?

	// Methods
	// =========================================================================

	// Methods: Setters
	// -------------------------------------------------------------------------

	public function status ($value)
	{
		$this->status = $value;
		return $this;
	}

	public function number ($value)
	{
		$this->number = $value;
		return $this;
	}

	public function event ($value)
	{
		if ($value instanceof Event)
			$this->eventId = $value->id;
		else
			$this->eventId = $value;

		return $this;
	}

	public function user ($value)
	{
		if ($value instanceof User)
			$this->userId = $value->id;
		else
			$this->userId = $value;

		return $this;
	}

	// This can't be `order` because the function arguments are different :(
	public function commerceOrder ($value)
	{
		if (
			class_exists(\craft\commerce\elements\Order::class)
			&& $value instanceof \craft\commerce\elements\Order
		)
			$this->orderId = $value->id;
		else
			$this->orderId = $value;

		return $this;
	}

	public function customer ($value)
	{
		if (
			class_exists(\craft\commerce\models\Customer::class)
			&& $value instanceof \craft\commerce\models\Customer
		)
			$this->customerId = $value->id;
		else
			$this->customerId = $value;

		return $this;
	}

	public function slot ($value)
	{
		if (!($value instanceof \DateTime))
			$this->slot = DateTimeHelper::toDateTime($value);
		else
			$this->slot = $value;

		return $this;
	}

	// Methods: Protected
	// -------------------------------------------------------------------------

	protected function beforePrepare (): bool
	{
		$table = BookingRecord::$tableNameUnprefixed;

		$this->joinElementTable($table);

		$this->query->select([
			$table . '.status',
			$table . '.number',
			$table . '.eventId',
			$table . '.userId',
			$table . '.orderId',
			$table . '.customerId',
			$table . '.customerEmail',
			$table . '.dateBooked',
			$table . '.reservationExpiry',
			$table . '.slot',
		]);

		if ($this->status)
			$this->subQuery->andWhere(
				Db::parseParam(
					$table . '.status',
					$this->status
				)
			);

		if ($this->number)
			$this->subQuery->andWhere(
				Db::parseParam(
					$table . '.number',
					$this->number
				)
			);

		if ($this->eventId)
			$this->subQuery->andWhere(
				Db::parseParam(
					$table . '.eventId',
					$this->eventId
				)
			);

		if ($this->userId)
			$this->subQuery->andWhere(
				Db::parseParam(
					$table . '.userId',
					$this->userId
				)
			);

		if ($this->customerId)
			$this->subQuery->andWhere(
				Db::parseParam(
					$table . '.customerId',
					$this->customerId
				)
			);

		if ($this->customerEmail)
			$this->subQuery->andWhere(
				Db::parseParam(
					$table . '.customerEmail',
					$this->customerEmail
				)
			);

		if ($this->slot)
			$this->subQuery->andWhere(
				Db::parseParam(
					$table . '.slot',
					Db::prepareDateForDb($this->slug)
				)
			);

		return parent::beforePrepare();
	}

}