<?php
/**
 * Bookings plugin for Craft CMS 3.x
 *
 * @link      https://ethercreative.co.uk
 * @copyright Copyright (c) Ether Creative
 */

namespace ether\bookings\elements;

use craft\base\Element;
use craft\helpers\Db;
use craft\helpers\UrlHelper;
use ether\bookings\Bookings;
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

	/**
	 * @event \yii\base\Event This event is raised when a booking is reserved.
	 *
	 * Plugins can get notified after a booking is reserved:
	 *
	 * ```php
	 * use ether\bookings\elements\Booking;
	 * use yii\base\Event;
	 *
	 * Event::on(
	 *     Booking::class,
	 *     Booking::EVENT_AFTER_BOOKING_RESERVED,
	 *     function (Event $event) {
	 *         $booking = $event->sender;
	 *     }
	 * );
	 * ```
	 */
	const EVENT_AFTER_BOOKING_RESERVED = 'afterBookingReserved';

	/**
	 * @event \yii\base\Event This event is raised when a reserved booking expires.
	 *
	 * Plugins can get notified after a reserved booking expires:
	 *
	 * ```php
	 * use ether\bookings\elements\Booking;
	 * use yii\base\Event;
	 *
	 * Event::on(
	 *     Booking::class,
	 *     Booking::EVENT_AFTER_BOOKING_RESERVATION_EXPIRED,
	 *     function (Event $event) {
	 *         $booking = $event->sender;
	 *     }
	 * );
	 * ```
	 */
	const EVENT_AFTER_BOOKING_RESERVATION_EXPIRED = 'afterBookingReservationExpired';

	/**
	 * @event \yii\base\Event This event is raised before a booking is completed.
	 *
	 * Plugins can get notified before a booking is completed:
	 *
	 * ```php
	 * use ether\bookings\elements\Booking;
	 * use yii\base\Event;
	 *
	 * Event::on(
	 *     Booking::class,
	 *     Booking::EVENT_BEFORE_BOOKING_COMPLETED,
	 *     function (Event $event) {
	 *         $booking = $event->sender;
	 *     }
	 * );
	 * ```
	 */
	const EVENT_BEFORE_BOOKING_COMPLETED = 'beforeBookingCompleted';

	/**
	 * @event \yii\base\Event This event is raised after a booking is completed.
	 *
	 * Plugins can get notified after a booking is completed:
	 *
	 * ```php
	 * use ether\bookings\elements\Booking;
	 * use yii\base\Event;
	 *
	 * Event::on(
	 *     Booking::class,
	 *     Booking::EVENT_AFTER_BOOKING_COMPLETED,
	 *     function (Event $event) {
	 *         $booking = $event->sender;
	 *     }
	 * );
	 * ```
	 */
	const EVENT_AFTER_BOOKING_COMPLETED = 'afterBookingCompleted';

	// Properties
	// =========================================================================

	/** @inheritdoc */
	public $id;

	/** @var bool - Whether or not the booking is completed */
	public $isCompleted = false;

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

	// Private Properties
	// -------------------------------------------------------------------------

	private $_field;
	private $_element;
	private $_user;
	private $_lineItem;
	private $_order;
	private $_customer;

	// Public Methods
	// =========================================================================

	/**
	 * @return null|string
	 */
	public static function displayName(): string
	{
		return \Craft::t('bookings', 'Bookings');
	}

	/**
	 * @return bool
	 */
	public static function hasContent (): bool
	{
		return true;
	}

	/**
	 * @inheritdoc
	 */
	public function __toString()
	{
		return $this->getShortNumber();
	}

	// Attributes
	// -------------------------------------------------------------------------

	public function datetimeAttributes (): array
	{
		$attributes = parent::datetimeAttributes();

		$attributes[] = 'slotStart';
		$attributes[] = 'slotEnd';
		$attributes[] = 'dateBooked';
		$attributes[] = 'reservationExpiry';

		return $attributes;
	}

	public function attributes (): array
	{
		$attributes = parent::attributes();

		$attributes[] = 'shortNumber';
		$attributes[] = 'email';

		return $attributes;
	}

	// Validation
	// -------------------------------------------------------------------------

	/**
	 * @return array
	 * @throws \yii\base\InvalidConfigException
	 */
	public function rules ()
	{
		$rules = parent::rules();

		$rules[] = [
			['fieldId', 'elementId', 'userId', 'orderId', 'customerId'],
			'number',
			'integerOnly' => true
		];

		$rules[] = [['customerEmail'], 'email'];

		return $rules;
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
		if ($this->isCompleted)
			return true;

		$this->isCompleted = true;
		$this->reservationExpiry = null;
		$this->dateBooked = Db::prepareDateForDb(new \DateTime());

		if ($this->hasEventHandlers(self::EVENT_BEFORE_BOOKING_COMPLETED))
			$this->trigger(self::EVENT_BEFORE_BOOKING_COMPLETED);

		if (\Craft::$app->elements->saveElement($this, false))
		{
			$this->afterBookingComplete();
			return true;
		}

		\Craft::error(
			\Craft::t(
				'bookings',
				'Couldn\'t mark booking {number} as complete. Booking save failed during completion with errors: {errors}',
				['number' => $this->number, 'errors' => json_encode($this->errors)],
				__METHOD__
			)
		);

		return false;
	}

	/**
	 * Expires the booking and deletes itself from the database
	 *
	 * @return bool
	 * @throws \Throwable
	 */
	public function expireBooking (): bool
	{
		if ($this->orderId && $this->lineItemId)
			$this->getOrder()->removeLineItem($this->getLineItem());

		if (!\Craft::$app->elements->deleteElement($this))
		{
			\Craft::error(
				\Craft::t(
					'bookings',
					'Couldn\'t expire booking {number} as complete. Booking deletion failed during expiration with errors: {errors}',
					['number' => $this->number, 'errors' => json_encode($this->errors)],
					__METHOD__
				)
			);

			return false;
		}

		if ($this->hasEventHandlers(self::EVENT_AFTER_BOOKING_RESERVATION_EXPIRED))
			$this->trigger(self::EVENT_AFTER_BOOKING_RESERVATION_EXPIRED);

		return true;
	}

	// Events
	// -------------------------------------------------------------------------

	/**
	 * Called after the booking is saved
	 *
	 * @param bool $isNew
	 *
	 * @throws \Exception
	 */
	public function afterSave (bool $isNew)
	{
		if ($isNew) {
			$record = new BookingRecord();
			$record->id = $this->id;
		} else {
			$record = BookingRecord::findOne($this->id);

			if (!$record)
				throw new \Exception('Invalid booking ID: ' . $this->id);
		}

		$record->isCompleted   = $this->isCompleted;
		$record->number        = $this->number;
		$record->fieldId       = $this->fieldId;
		$record->elementId     = $this->elementId;
		$record->userId        = $this->userId;
		$record->lineItemId    = $this->lineItemId;
		$record->orderId       = $this->orderId;
		$record->customerId    = $this->customerId;
		$record->customerEmail = $this->customerEmail;
		$record->slotEnd       = $this->slotEnd;
		$record->dateBooked    = $this->dateBooked;

		if ($isNew && !$this->isCompleted)
			$record->reservationExpiry = Db::prepareDateForDb(new \DateTime());

		$record->save(false);

		if ($isNew && !$this->isCompleted && $this->hasEventHandlers(self::EVENT_AFTER_BOOKING_RESERVED))
			$this->trigger(self::EVENT_AFTER_BOOKING_RESERVED);

		return parent::afterSave($isNew);
	}

	/**
	 * Called after the booking is marked as complete
	 */
	public function afterBookingComplete ()
	{
		if ($this->hasEventHandlers(self::EVENT_AFTER_BOOKING_COMPLETED))
			$this->trigger(self::EVENT_AFTER_BOOKING_COMPLETED);
	}

	// Getters
	// -------------------------------------------------------------------------

	/**
	 * TODO: Allow each field to have its own Booking field layout
	 *
	 * @return \craft\models\FieldLayout|null
	 */
	public function getFieldLayout ()
	{
		$bookingSettings =
			Bookings::getInstance()->bookingSettings->getBookingSettingsByHandle('defaultBooking');

		if ($bookingSettings)
			return $bookingSettings->getFieldLayout();

		return null;
	}

	/**
	 * @return string
	 */
	public function getShortNumber(): string
	{
		return substr($this->number, 0, 7);
	}

	/**
	 * @return string
	 */
	public function getCpEditUrl(): string
	{
		return UrlHelper::cpUrl('bookings/bookings/' . $this->id);
	}

	/**
	 * @return \craft\base\FieldInterface|null
	 */
	public function getField ()
	{
		if ($this->_field)
			return $this->_field;

		$field = \Craft::$app->fields->getFieldById($this->fieldId);

		return $this->_field = $field;
	}

	/**
	 * @return \craft\base\ElementInterface|null
	 */
	public function getElement ()
	{
		if ($this->_element)
			return $this->_element;

		$element = \Craft::$app->elements->getElementById($this->elementId);

		return $this->_element = $element;
	}

	/**
	 * @return \craft\elements\User|null
	 */
	public function getUser ()
	{
		if ($this->_user)
			return $this->_user;

		$user = \Craft::$app->users->getUserById($this->userId);

		return $this->_user = $user;
	}

	/**
	 * @return \craft\commerce\models\LineItem|null
	 */
	public function getLineItem ()
	{
		if ($this->_lineItem)
			return $this->_lineItem;

		/** @noinspection PhpUnnecessaryFullyQualifiedNameInspection */
		if ($this->lineItemId && class_exists(\craft\commerce\models\LineItem::class))
			return $this->_lineItem = CommerceGetters::getLineItemById($this->lineItemId);

		return null;
	}

	/**
	 * @return \craft\commerce\elements\Order|null
	 */
	public function getOrder ()
	{
		if ($this->_order)
			return $this->_order;

		/** @noinspection PhpUnnecessaryFullyQualifiedNameInspection */
		if ($this->orderId && class_exists(\craft\commerce\elements\Order::class))
			return $this->_order = CommerceGetters::getOrderById($this->orderId);

		return null;
	}

	/**
	 * @return \craft\commerce\models\Customer|null
	 */
	public function getCustomer ()
	{
		if ($this->_customer)
			return $this->_customer;

		/** @noinspection PhpUnnecessaryFullyQualifiedNameInspection */
		if ($this->customerId && class_exists(\craft\commerce\models\Customer::class))
			return $this->_customer = CommerceGetters::getCustomerById($this->customerId);

		return null;
	}

}