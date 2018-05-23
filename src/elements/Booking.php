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
		// TODO: If using commerce, remove the line item this was bound to
		// Or will deleting the element do that?

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

		$record->isCompleted = $this->isCompleted;
		$record->number = $this->number;
		$record->fieldId = $this->fieldId;
		$record->elementId = $this->elementId;
		$record->userId = $this->userId;
		$record->lineItemId = $this->lineItemId;
		$record->orderId = $this->orderId;
		$record->customerId = $this->customerId;
		$record->customerEmail = $this->customerEmail;
		$record->slotEnd = $this->slotEnd;
		$record->dateBooked = $this->dateBooked;

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

	// Helpers
	// =========================================================================

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

}