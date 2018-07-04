<?php
/**
 * Bookings plugin for Craft CMS 3.x
 *
 * @link      https://ethercreative.co.uk
 * @copyright Copyright (c) Ether Creative
 */

namespace ether\bookings\elements\db;

use craft\base\Element;
use craft\base\Field;
use craft\base\Model;
use craft\elements\db\ElementQuery;
use craft\elements\User;
use craft\helpers\ArrayHelper;
use craft\helpers\Db;
use ether\bookings\elements\Booking;
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

	/** @var int - The current status of the booking */
	public $status;

	/** @var string - A unique identifier for this booking */
	public $number;

	/** @var int - The **Bookable Variant** field this booking is bound to */
	public $fieldId;

	/** @var int - The element this booking is bound to (i.e. entry, product, etc.) */
	public $elementId;

	/** @var int - The user this booking is bound to (if the user was logged in at the time of booking) */
	public $userId;

	/** @var int - The line item this booking belongs to (if Commerce is used) */
	public $lineItemId;

	/** @var int - The order this booking belongs to (if Commerce is used) */
	public $orderId;

	/** @var int - The customer this booking belongs to (if Commerce is used) */
	public $customerId;

	/** @var string - The customers email (always required) */
	public $customerEmail;

	/** @var \DateTime - The slot that was booked */
	public $slotStart;

	/** @var \DateTime - The end slot that was booked (if booking is flexible) */
	public $slotEnd;

	/** @var \DateTime - The time the booking was placed */
	public $dateBooked;

	/** @var \DateTime|null - The time this booking reservation will expire (if null, this is a complete booking) */
	public $reservationExpiry = null;

	/** @var bool - If true, this booking has expired */
	public $expired = false;

	// Methods
	// =========================================================================

	public function __construct (string $elementType, array $config = [])
	{
		if (!isset($config['orderBy']))
			$config['orderBy'] = BookingRecord::$tableNameRaw . '.dateCreated';

		parent::__construct($elementType, $config);
	}

	public function __set($name, $value)
	{
		switch ($name) {
			case 'updatedAfter':
				$this->updatedAfter($value);
				break;
			case 'updatedBefore':
				$this->updatedBefore($value);
				break;
			case 'order':
				$this->commerceOrder($value);
				break;
			default:
				parent::__set($name, $value);
		}
	}

	// Setters
	// =========================================================================

	// Setters: General Properties
	// -------------------------------------------------------------------------

	/**
	 * @param string $value
	 *
	 * @return static
	 */
	public function number (string $value)
	{
		$this->number = $value;

		return $this;
	}

	/**
	 * @param Field|int $value
	 *
	 * @return static
	 */
	public function field ($value)
	{
		if ($value instanceof Field) {
			$this->fieldId = $value->id;
		} else if ($value !== null) {
			$this->fieldId = $value;
		} else {
			$this->fieldId = null;
		}

		return $this;
	}

	/**
	 * @param Element|int $value
	 *
	 * @return static
	 */
	public function element ($value)
	{
		if ($value instanceof Element) {
			$this->elementId = $value->id;
		} else if ($value !== null) {
			$this->elementId = $value;
		} else {
			$this->elementId = null;
		}

		return $this;
	}

	/**
	 * @param User|int $value
	 *
	 * @return static
	 */
	public function user ($value)
	{
		if ($value instanceof User) {
			$this->userId = $value->id;
		} else if ($value !== null) {
			$this->userId = $value;
		} else {
			$this->userId = null;
		}

		return $this;
	}

	/**
	 * @param string $value
	 *
	 * @return static
	 */
	public function email (string $value)
	{
		$this->customerEmail = $value;

		return $this;
	}

	// Setters: Commerce
	// -------------------------------------------------------------------------

	/**
	 * @param Model|int $value
	 *
	 * @return static
	 */
	public function lineItem ($value)
	{
		if ($value instanceof Model) {
			$this->lineItemId = $value->id;
		} else if ($value !== null) {
			$this->lineItemId = $value;
		} else {
			$this->lineItemId = null;
		}

		return $this;
	}

	/**
	 * @param Element|int $value
	 *
	 * @return static
	 */
	public function commerceOrder ($value)
	{
		if ($value instanceof Element) {
			$this->orderId = $value->id;
		} else if ($value !== null) {
			$this->orderId = $value;
		} else {
			$this->orderId = null;
		}

		return $this;
	}

	/**
	 * @param Model|int $value
	 *
	 * @return static
	 */
	public function customer ($value)
	{
		if ($value instanceof Model) {
			$this->customerId = $value->id;
		} else if ($value !== null) {
			$this->customerId = $value;
		} else {
			$this->customerId = null;
		}

		return $this;
	}

	// Setters: Dates
	// -------------------------------------------------------------------------

	/**
	 * @param mixed $value
	 *
	 * @return static
	 */
	public function start ($value)
	{
		if ($value instanceof \DateTime)
			$value = $value->format(\DateTime::W3C);

		$this->slotStart = $value;

		return $this;
	}

	/**
	 * @param mixed $value
	 *
	 * @return static
	 */
	public function startBefore ($value)
	{
		if ($value instanceof \DateTime)
			$value = $value->format(\DateTime::W3C);

		$this->slotStart = ArrayHelper::toArray($this->slotStart);
		$this->slotStart[] = '< ' . $value;

		return $this;
	}

	/**
	 * @param mixed $value
	 *
	 * @return static
	 */
	public function startAfter ($value)
	{
		if ($value instanceof \DateTime)
			$value = $value->format(\DateTime::W3C);

		$this->slotStart = ArrayHelper::toArray($this->slotStart);
		$this->slotStart[] = '>= ' . $value;

		return $this;
	}

	/**
	 * @param mixed $value
	 *
	 * @return static
	 */
	public function end ($value)
	{
		if ($value instanceof \DateTime)
			$value = $value->format(\DateTime::W3C);

		$this->slotEnd = $value;

		return $this;
	}

	/**
	 * @param mixed $value
	 *
	 * @return static
	 */
	public function endBefore ($value)
	{
		if ($value instanceof \DateTime)
			$value = $value->format(\DateTime::W3C);

		$this->slotEnd = ArrayHelper::toArray($this->slotEnd);
		$this->slotEnd[] = '< ' . $value;

		return $this;
	}

	/**
	 * @param mixed $value
	 *
	 * @return static
	 */
	public function endAfter ($value)
	{
		if ($value instanceof \DateTime)
			$value = $value->format(\DateTime::W3C);

		$this->slotEnd = ArrayHelper::toArray($this->slotEnd);
		$this->slotEnd[] = '>= ' . $value;

		return $this;
	}

	/**
	 * @param mixed $value
	 *
	 * @return static
	 */
	public function booked ($value)
	{
		if ($value instanceof \DateTime)
			$value = $value->format(\DateTime::W3C);

		$this->dateBooked = $value;

		return $this;
	}

	/**
	 * @param mixed $value
	 *
	 * @return static
	 */
	public function bookedBefore ($value)
	{
		if ($value instanceof \DateTime)
			$value = $value->format(\DateTime::W3C);

		$this->dateBooked = ArrayHelper::toArray($this->dateBooked);
		$this->dateBooked[] = '< ' . $value;

		return $this;
	}

	/**
	 * @param mixed $value
	 *
	 * @return static
	 */
	public function bookedAfter ($value)
	{
		if ($value instanceof \DateTime)
			$value = $value->format(\DateTime::W3C);

		$this->dateBooked = ArrayHelper::toArray($this->dateBooked);
		$this->dateBooked[] = '>= ' . $value;

		return $this;
	}

	/**
	 * @param mixed $value
	 *
	 * @return static
	 */
	public function updatedBefore ($value)
	{
		if ($value instanceof \DateTime)
			$value = $value->format(\DateTime::W3C);

		$this->dateUpdated = ArrayHelper::toArray($this->dateUpdated);
		$this->dateUpdated[] = '< ' . $value;

		return $this;
	}

	/**
	 * @param mixed $value
	 *
	 * @return static
	 */
	public function updatedAfter ($value)
	{
		if ($value instanceof \DateTime)
			$value = $value->format(\DateTime::W3C);

		$this->dateUpdated = ArrayHelper::toArray($this->dateUpdated);
		$this->dateUpdated[] = '>= ' . $value;

		return $this;
	}

	// Protected
	// =========================================================================

	protected function beforePrepare (): bool
	{
		$table = BookingRecord::$tableNameRaw;

		$this->joinElementTable($table);

		\Craft::info(
			print_r($this->status, true),
			'bookings'
		);

		$this->query->select([
			$table . '.id',
			$table . '.status',
			$table . '.number',
			$table . '.fieldId',
			$table . '.elementId',
			$table . '.userId',
			$table . '.lineItemId',
			$table . '.orderId',
			$table . '.customerId',
			$table . '.customerEmail',
			$table . '.slotStart',
			$table . '.slotEnd',
			$table . '.dateBooked',
			$table . '.reservationExpiry',
			$table . '.dateUpdated',
		]);

		if ($this->status)
			$this->subQuery->andWhere(Db::parseParam($table . '.status', $this->status));

		if ($this->number)
			$this->subQuery->andWhere(Db::parseParam($table . '.number', $this->number));

		if ($this->fieldId)
			$this->subQuery->andWhere(Db::parseParam($table . '.fieldId', $this->fieldId));

		if ($this->elementId)
			$this->subQuery->andWhere(Db::parseParam($table . '.elementId', $this->elementId));

		if ($this->userId)
			$this->subQuery->andWhere(Db::parseParam($table . '.userId', $this->userId));

		if ($this->lineItemId)
			$this->subQuery->andWhere(Db::parseParam($table . '.lineItemId', $this->lineItemId));

		if ($this->orderId)
			$this->subQuery->andWhere(Db::parseParam($table . '.orderId', $this->orderId));

		if ($this->customerId)
			$this->subQuery->andWhere(Db::parseParam($table . '.customerId', $this->customerId));

		if ($this->customerEmail)
			$this->subQuery->andWhere(Db::parseParam($table . '.customerEmail', $this->customerEmail));

		if ($this->slotStart)
			$this->subQuery->andWhere(Db::parseParam($table . '.slotStart', $this->slotStart));

		if ($this->slotEnd)
			$this->subQuery->andWhere(Db::parseParam($table . '.slotEnd', $this->slotEnd));

		if ($this->dateBooked)
			$this->subQuery->andWhere(Db::parseParam($table . '.dateBooked', $this->dateBooked));

		if ($this->reservationExpiry)
			$this->subQuery->andWhere(Db::parseParam($table . '.reservationExpiry', $this->reservationExpiry));

		if ($this->dateUpdated)
			$this->subQuery->andWhere(Db::parseParam($table . '.dateUpdated', $this->dateUpdated));

		return parent::beforePrepare();
	}

	protected function statusCondition (string $status)
	{
		$col = BookingRecord::$tableName . '.status';

		switch ($status) {
			case Booking::STATUS_RESERVED:
			case Booking::STATUS_COMPLETED:
			case Booking::STATUS_EXPIRED:
				return [$col => $status];
			default:
				return parent::statusCondition($status);
		}
	}

}