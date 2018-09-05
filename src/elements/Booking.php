<?php
/**
 * Bookings plugin for Craft CMS 3.x
 *
 * @link      https://ethercreative.co.uk
 * @copyright Copyright (c) Ether Creative
 */

namespace ether\bookings\elements;

use craft\base\Element;
use craft\db\Query;
use craft\elements\db\ElementQueryInterface;
use craft\helpers\Db;
use craft\helpers\UrlHelper;
use ether\bookings\Bookings;
use ether\bookings\elements\db\BookingQuery;
use ether\bookings\helpers\DateHelper;
use ether\bookings\integrations\commerce\CommerceGetters;
use ether\bookings\records\BookingRecord;
use ether\bookings\records\EventRecord;


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

	/** @var \DateTime */
	public $slot;

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

		if ($this->dateBooked)
			$this->dateBooked = DateHelper::toUTCDateTime($this->dateBooked);

		if ($this->reservationExpiry)
			$this->reservationExpiry = DateHelper::toUTCDateTime($this->reservationExpiry);
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

		$attrs[] = 'slot';
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

		$tickets = $this->getBookedTickets();

		if ($this->orderId)
		{
			$order = $this->getOrder();

			/** @var BookedTicket $ticket */
			foreach ($tickets as $ticket)
			{
				$order->removeLineItem($ticket->getLineItem());
				Bookings::getInstance()->slots->clearSlotsFromTicket($ticket);
			}
		}
		else
			foreach ($tickets as $ticket) /** @var BookedTicket $ticket */
				Bookings::getInstance()->slots->clearSlotsFromTicket($ticket);

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
		$record->slot = $this->slot;
		$record->dateBooked = $this->dateBooked;
		$record->reservationExpiry = $this->reservationExpiry;

		$record->save();

		return parent::afterSave($isNew);
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

	/**
	 * @return string
	 */
	public function getCpEditUrl (): string
	{
		return UrlHelper::cpUrl('bookings/' . $this->id);
	}

	// Elements Index
	// =========================================================================

	protected static function defineTableAttributes (): array
	{
		return [
			'number' => ['label' => \Craft::t('bookings', 'Number')],
			'id' => ['label' => \Craft::t('bookings', 'ID')],
			'customerEmail' => ['label' => \Craft::t('bookings', 'Customer Email')],
			'dateBooked' => ['label' => \Craft::t('bookings', 'Date Booked')],
			'dateCreated' => ['label' => \Craft::t('app', 'Date Created')],
			'dateUpdated' => ['label' => \Craft::t('app', 'Date Updated')],
		];
	}

	protected static function defineDefaultTableAttributes (string $source): array
	{
		$attrs = parent::defineDefaultTableAttributes($source);

		$attrs[] = 'number';
		$attrs[] = 'customerEmail';
		$attrs[] = 'dateBooked';
		$attrs[] = 'dateUpdated';

		return $attrs;
	}

	public static function sortOptions (): array
	{
		return [
			'number' => \Craft::t('bookings', 'Number'),
			'id' => \Craft::t('bookings', 'ID'),
			'dateBooked' => \Craft::t('bookings', 'Date Booked'),
			[
				'label' => \Craft::t('app', 'Date Updated'),
				'orderBy' => BookingRecord::$tableNameUnprefixed . '.dateUpdated',
				'attribute' => 'dateUpdated',
			]
		];
	}

	protected static function defineSources (string $context = null): array
	{
		// TODO: Make default sorting the first slot date?
		// TODO: This could be improved w/ better grouping of elements (i.e. by Product / Entry Type)

		$sources = [
			'*' => [
				'key' => '*',
				'label' => \Craft::t('bookings', 'All Bookings'),
				'criteria' => ['status' => self::STATUS_COMPLETED],
				'defaultSort' => ['dateBooked', 'desc']
			],
		];

		$enabledBookables = (new Query())
			->select(['events.elementId', 'events.id'])
			->from([EventRecord::$tableName . ' events'])
			->where(['events.enabled' => true]);

		$enabledBookableElementToEvent = $enabledBookables->pairs();
		$enabledBookableElementIds = $enabledBookables->column();

		$elements = (new Query())
			->select(['content.title', 'elements.id', 'elements.type'])
			->from(['{{%elements}} elements'])
			->where([
				'elements.id'       => $enabledBookableElementIds,
				'elements.enabled'  => true,
				'elements.archived' => false,
			])
			->innerJoin(
				'{{%content}} content',
				'content.elementId = elements.id AND content.siteId = ' . \Craft::$app->sites->primarySite->id
			)
			->orderBy('content.title asc')
			->all();

		$byType = [];

		foreach ($elements as $element)
		{
			$type = explode('\\', $element['type']);
			$type = end($type);

			if (!array_key_exists($type, $byType))
				$byType[$type] = [];

			$byType[$type][] = $element;
		}

		ksort($byType);

		foreach ($byType as $type => $elements)
		{
			$sources[] = ['heading' => $type];

			foreach ($elements as $element)
			{
				$key           = 'element:' . $element['id'];

				$sources[$key] = [
					'key'         => $key,
					'label'       => $element['title'],
					'criteria'    => [
						'eventId' => $enabledBookableElementToEvent[$element['id']],
						'status'  => self::STATUS_COMPLETED,
					],
					'defaultSort' => ['slotStart', 'desc']
				];
			}
		}

		return $sources;
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