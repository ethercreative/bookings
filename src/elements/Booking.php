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
use craft\helpers\Db;
use ether\bookings\Bookings;
use ether\bookings\elements\db\BookingQuery;
use ether\bookings\integrations\commerce\CommerceGetters;
use ether\bookings\records\BookingRecord;


/**
 * Class Booking
 *
 * @author  Ether Creative
 * @package ether\bookings\elements
 * @since   1.0.0
 */
class Booking extends Element
{

	// Constants
	// =========================================================================

	const STATUS_RESERVED = 0;
	const STATUS_COMPLETED = 1;
	const STATUS_EXPIRED = 2;

	// Properties
	// =========================================================================

	/** @var int */
	public $status = self::STATUS_RESERVED;

	/** @var string */
	public $number;

	/** @var int */
	public $eventId;

	/** @var int|null */
	public $userId;

	/** @var int|null */
	public $orderId;

	/** @var int|null */
	public $customerId;

	/** @var string|null */
	public $customerEmail;

	/** @var \DateTime|null */
	public $dateBooked;

	/** @var \DateTime|null */
	public $reservationExpiry;

	// Properties: Private
	// -------------------------------------------------------------------------

	private $_customer;
	private $_order;
	private $_bookedTickets;

	// Methods
	// =========================================================================

	public function init ()
	{
		parent::init();

		try {
			$this->expireBooking();
		} catch (\Throwable $e) {}
	}

	public static function displayName (): string
	{
		return \Craft::t('bookings', 'Booking');
	}

	public static function hasContent (): bool
	{
		return false;
	}

	public static function hasStatuses (): bool
	{
		return false;
	}

	public function __toString ()
	{
		return $this->getShortNumber();
	}

	public static function find (): ElementQueryInterface
	{
		return new BookingQuery(self::class);
	}

	public static function statuses (): array
	{
		return [
			self::STATUS_RESERVED  => [
				'label' => \Craft::t('bookings', 'Reserved'),
				'color' => 'orange',
			],
			self::STATUS_COMPLETED => [
				'label' => \Craft::t('bookings', 'Completed'),
				'color' => 'green',
			],
			self::STATUS_EXPIRED   => [
				'label' => \Craft::t('bookings', 'Expired'),
				'color' => 'red',
			],
		];
	}

	// Attributes
	// -------------------------------------------------------------------------

	public function datetimeAttributes (): array
	{
		$attrs = parent::datetimeAttributes();

		$attrs[] = 'dateBooked';
		$attrs[] = 'reservationExpiry';

		return $attrs;
	}

	public function attributes ()
	{
		$attrs = parent::attributes();

		$attrs[] = 'shortNumber';
		$attrs[] = 'email';

		return $attrs;
	}

	// Actions
	// -------------------------------------------------------------------------

	/**
	 * Marks the booking as complete
	 *
	 * @return bool
	 * @throws \Throwable
	 * @throws \craft\errors\ElementNotFoundException
	 * @throws \yii\base\Exception
	 */
	public function markAsComplete (): bool
	{
		if ($this->status === self::STATUS_COMPLETED)
			return true;

		$this->status = self::STATUS_COMPLETED;
		$this->reservationExpiry = null;
		$this->dateBooked = Db::prepareDateForDb(new \DateTime());

		if (\Craft::$app->elements->saveElement($this, false))
		{
			return true;
		}

		\Craft::error(
			\Craft::t(
				'bookings',
				'Couldn\'t mark booking {number} as complete. Booking save failed during completion with errors: {errors}',
				[
					'number' => $this->number,
					'errors' => json_encode($this->errors),
				],
				__METHOD__
			)
		);

		return false;
	}

	/**
	 * Expires the booking
	 *
	 * @return bool
	 * @throws \Throwable
	 * @throws \craft\errors\ElementNotFoundException
	 * @throws \yii\base\Exception
	 */
	public function expireBooking (): bool
	{
		if ($this->status !== self::STATUS_RESERVED)
			return true;

		$settings = Bookings::getInstance()->settings;

		if ($this->reservationExpiry->getTimestamp() >= time() - $settings->expiryDuration)
			return true;

		if ($this->orderId)
		{
			$order = $this->getOrder();

			/** @var BookedTicket $ticket */
			foreach ($this->getBookedTickets() as $ticket)
				$order->removeLineItem($ticket->getLineItem());
		}

		$this->status = self::STATUS_EXPIRED;

		if (!\Craft::$app->elements->saveElement($this))
		{
			\Craft::error(
				\Craft::t(
					'bookings',
					'Couldn\'t expire booking {number}. Booking save failed during expiration with errors: {errors}',
					[
						'number' => $this->number,
						'errors' => json_encode($this->errors)
					],
					__METHOD__
				)
			);

			return false;
		}

		return true;
	}

	// Events
	// -------------------------------------------------------------------------

	public function beforeSave (bool $isNew): bool
	{
		if ($this->number === null)
			$this->number = $this->_generateBookingNumber();

		if ($isNew && $this->status === self::STATUS_RESERVED)
			$this->reservationExpiry = Db::prepareDateForDb(new \DateTime());

		if ($this->customerId && $this->customerEmail === null)
			$this->customerEmail = $this->getCustomer()->email;

		return parent::beforeSave($isNew);
	}

	/**
	 * @param bool $isNew
	 *
	 * @throws \Exception
	 */
	public function afterSave (bool $isNew)
	{
		if ($isNew)
		{
			$record = new BookingRecord();
			$record->id = $this->id;
		}
		else
		{
			$record = BookingRecord::findOne($this->id);

			if ($record === null)
				throw new \Exception('Invalid Booking ID: ' . $this->id);
		}

		$record->status = $this->status;
		$record->number = $this->number;
		$record->eventId = $this->eventId;
		$record->userId = $this->userId;
		$record->orderId = $this->orderId;
		$record->customerId = $this->customerId;
		$record->customerEmail = $this->customerEmail;
		$record->dateBooked = $this->dateBooked;
		$record->reservationExpiry = $this->reservationExpiry;

		$record->save();

		return parent::afterSave($isNew);
	}

	/**
	 * @throws \Throwable
	 * @throws \yii\db\StaleObjectException
	 */
	public function afterDelete ()
	{
		BookingRecord::findOne([
			'id' => $this->id,
		])->delete();

		parent::afterDelete();
	}

	// Getters
	// -------------------------------------------------------------------------

	public function getCustomer ()
	{
		if ($this->customerId === null)
			return null;

		if ($this->_customer)
			return $this->_customer;

		if (class_exists(\craft\commerce\models\Customer::class))
			return $this->_customer = CommerceGetters::getCustomerById($this->customerId);

		return null;
	}

	public function getOrder ()
	{
		if ($this->orderId === null)
			return null;

		if ($this->_order)
			return $this->_order;

		if (class_exists(\craft\commerce\elements\Order::class))
			return $this->_order = CommerceGetters::getOrderById($this->orderId);

		return null;
	}

	public function getBookedTickets ()
	{
		if ($this->_bookedTickets)
			return $this->_bookedTickets;

		return $this->_bookedTickets = BookedTicket::find()->andWhere([
			'bookingId' => $this->id,
		])->all();
	}

	// Helpers
	// =========================================================================

	public function getShortNumber (): string
	{
		return substr($this->number, 0, 7);
	}

	/**
	 * Generates a unique booking number
	 *
	 * @return string
	 */
	private function _generateBookingNumber ()
	{
		return md5(uniqid(mt_rand(), true));
	}

}