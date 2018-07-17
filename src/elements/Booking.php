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
use ether\bookings\enums\BookableType;
use ether\bookings\integrations\commerce\CommerceGetters;
use ether\bookings\integrations\commerce\CommerceValidators;
use ether\bookings\models\Bookable;
use ether\bookings\records\BookableRecord;
use ether\bookings\records\BookingRecord;
use yii\helpers\Inflector;


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

	/** @var int - The current status of the booking */
	public $status = self::STATUS_RESERVED;

	/** @var string - A unique identifier for this booking */
	public $number;

	/** @var int - The **Bookable Variant** field this booking is bound to */
	public $fieldId;

	/** @var int - The element this booking is bound to (i.e. entry, product, etc.) */
	public $elementId;

	/** @var int - The sub-element this bookings is bound to (i.e. variant, matrix block, etc.) */
	public $subElementId;

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

	/**
	 * @var \DateTime - The end slot that was booked (if booking is flexible)
	 *
	 * NOTE:
	 * This is the START TIME of the END SLOT, not the end time of the entire
	 * booking. For that use $this->slotEnd + $this->getField()->baseRule->duration in $this->getField()->baseRule->frequency
	 * TODO: Write getter/helper that does this
	 */
	public $slotEnd;

	/** @var \DateTime - The time the booking was placed */
	public $dateBooked;

	/** @var \DateTime|null - The time this booking reservation will expire (if null, this is a complete booking) */
	public $reservationExpiry = null;

	// Private Properties
	// -------------------------------------------------------------------------

	private $_bookable;
	private $_field;
	private $_element;
	private $_subElement;
	private $_user;
	private $_lineItem;
	private $_order;
	private $_customer;

	// Public Methods
	// =========================================================================

	public function init ()
	{
		parent::init();

		try {
			$this->expireBooking();
		} catch (\Throwable $e) {}
	}

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

	public static function hasStatuses (): bool
	{
		return false;
	}

	/**
	 * @inheritdoc
	 */
	public function __toString()
	{
		return $this->getShortNumber();
	}

	/**
	 * @return ElementQueryInterface
	 */
	public static function find(): ElementQueryInterface
	{
		return new BookingQuery(static::class);
	}

	public static function statuses (): array
	{
		return [
			self::STATUS_RESERVED => [
				'label' => \Craft::t('bookings', 'Reserved'),
				'color' => 'orange',
			],
			self::STATUS_COMPLETED => [
				'label' => \Craft::t('bookings', 'Completed'),
				'color' => 'green',
			],
			self::STATUS_EXPIRED => [
				'label' => \Craft::t('bookings', 'Expired'),
				'color' => 'red',
			],
		];
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
			['fieldId', 'elementId', 'customerEmail', 'slotStart'],
			'required'
		];

		$rules[] = [
			['fieldId', 'elementId', 'userId', 'lineItemId', 'orderId', 'customerId'],
			'number',
			'integerOnly' => true
		];

		$rules[] = [['customerEmail'], 'email'];

		$rules[] = [
			['lineItemId', 'orderId', 'customerId'],
			'validateCommerceProperties',
		];

		$rules[] = [['slotStart'], 'validateSlotStart'];
		$rules[] = [['slotEnd'], 'validateSlotEnd'];

		return $rules;
	}

	public function beforeValidate ()
	{
		// If we have a customer but not an email
		if ($this->customerId && !$this->customerEmail)
			$this->customerEmail = $this->getCustomer()->email;

		// If we have a user but not an email
		if ($this->userId && !$this->customerEmail)
			$this->customerEmail = $this->getUser()->email;

		return parent::beforeValidate();
	}

	/**
	 * Validates the commerce specific properties
	 *
	 * @param string $attribute
	 */
	public function validateCommerceProperties ($attribute)
	{
		// Only allow if commerce is installed
		if (!$this->_commerceInstalled())
		{
			$this->addError(
				$attribute,
				\Craft::t(
					'bookings',
					'{attribute} must not be set when Commerce is not installed.',
					['attribute' => Inflector::camel2words($attribute)]
				)
			);

			return;
		}

		// Only allow if element is purchasable
		if (!$this->_elementIsPurchasable())
		{
			$this->addError(
				$attribute,
				\Craft::t(
					'bookings',
					'{attribute} must not be set when the element being booked is not a Commerce Purchasable.',
					['attribute' => Inflector::camel2words($attribute)]
				)
			);

			return;
		}

		// Ensure they are either all set or that none are set
		if (
			($this->lineItemId || $this->orderId || $this->customerId)
			&& !($this->lineItemId && $this->orderId && $this->customerId)
		) {
			$this->addError(
				$attribute,
				\Craft::t(
					'bookings',
					'{attribute} is required.',
					['attribute' => Inflector::camel2words($attribute)]
				)
			);
		}
	}

	/**
	 * Ensures the slot start is valid
	 *
	 * @param string $attribute
	 */
	public function validateSlotStart ($attribute)
	{
		if (!$this->getBookable()->isDateOccurrence($this->slotStart))
		{
			$this->addError(
				$attribute,
				\Craft::t(
					'bookings',
					'{attribute} is not a valid occurrence.',
					['attribute' => Inflector::camel2words($attribute)]
				)
			);
			return;
		}

		try {
			$isValid = Bookings::getInstance()->booking->validateSlot(
				$this->getBookable()->slotMultiplier,
				$this->slotStart,
				null,
				$this->id
			);

			if (is_string($isValid))
			{
				$this->addError(
					$attribute,
					$isValid
				);
				return;
			}
		} catch (\Exception $e) {
			$this->addError(
				$attribute,
				\Craft::t(
					'bookings',
					'Unable to verify {attribute} availability: ' . $e->getMessage(),
					['attribute' => Inflector::camel2words($attribute)]
				)
			);
			return;
		}
	}

	/**
	 * Ensures the slot end is valid
	 *
	 * @param string $attribute
	 */
	public function validateSlotEnd ($attribute)
	{
		$bookable = $this->getBookable();

		if (!$this->slotEnd)
		{
			if ($bookable->bookableType === BookableType::FLEXIBLE)
			{
				$this->addError(
					$attribute,
					\Craft::t(
						'bookings',
						'{attribute} is required for flexible duration bookings.',
						['attribute' => Inflector::camel2words($attribute)]
					)
				);
			}

			return;
		}

		if ($bookable->bookableType === BookableType::FIXED)
		{
			$this->addError(
				$attribute,
				\Craft::t(
					'bookings',
					'{attribute} is not allowed for fixed duration bookings.',
					['attribute' => Inflector::camel2words($attribute)]
				)
			);
			return;
		}

		if (!$this->getBookable()->isDateOccurrence($this->slotEnd))
		{
			$this->addError(
				$attribute,
				\Craft::t(
					'bookings',
					'{attribute} is not a valid occurrence.',
					['attribute' => Inflector::camel2words($attribute)]
				)
			);
			return;
		}

		try {
			$isValid = Bookings::getInstance()->booking->validateSlot(
				$this->getBookable()->slotMultiplier,
				$this->slotStart,
				$this->slotEnd,
				$this->id
			);

			if (is_string($isValid))
			{
				$this->addError(
					$attribute,
					$isValid
				);
				return;
			}
		} catch (\Exception $e) {
			$this->addError(
				$attribute,
				\Craft::t(
					'bookings',
					'Unable to verify {attribute} availability',
					['attribute' => Inflector::camel2words($attribute)]
				)
			);
			return;
		}
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
		if ($this->status === Booking::STATUS_COMPLETED)
			return true;

		$this->status = Booking::STATUS_COMPLETED;
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
	 * Expires the booking if necessary
	 *
	 * @return bool
	 * @throws \Throwable
	 */
	public function expireBooking (): bool
	{
		if ($this->status !== Booking::STATUS_RESERVED)
			return true;

		$settings = Bookings::getInstance()->settings;

		if ($this->reservationExpiry->getTimestamp() >= time() - $settings->expiryDuration)
			return true;

		\Craft::$app->session->setError('A booking has expired.');

		if ($this->orderId && $this->lineItemId)
			$this->getOrder()->removeLineItem($this->getLineItem());

		$this->status = Booking::STATUS_EXPIRED;

		if (!\Craft::$app->elements->saveElement($this))
		{
			\Craft::error(
				\Craft::t(
					'bookings',
					'Couldn\'t expire booking {number}. Booking save failed during expiration with errors: {errors}',
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

		$record->status        = $this->status;
		$record->number        = $this->number;
		$record->fieldId       = $this->fieldId;
		$record->elementId     = $this->elementId;
		$record->userId        = $this->userId;
		$record->lineItemId    = $this->lineItemId;
		$record->orderId       = $this->orderId;
		$record->customerId    = $this->customerId;
		$record->customerEmail = $this->customerEmail;
		$record->slotStart     = $this->slotStart;
		$record->slotEnd       = $this->slotEnd;
		$record->dateBooked    = $this->dateBooked;

		if ($isNew)
			$record->reservationExpiry = Db::prepareDateForDb(new \DateTime());
		else
			$record->reservationExpiry = $this->reservationExpiry;

		$record->save(false);

		if ($isNew && $this->status === Booking::STATUS_RESERVED && $this->hasEventHandlers(self::EVENT_AFTER_BOOKING_RESERVED))
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
	 * @return Bookable|null
	 */
	public function getBookable ()
	{
		if ($this->_bookable)
			return $this->_bookable;

		if ($this->fieldId && $this->elementId)
		{
			$record = BookableRecord::findOne([
				'ownerId' => $this->elementId,
				'fieldId' => $this->fieldId,
			]);

			if ($record) {
				$settings = $record->getAttributes()['settings'];

				try {
					$settings = json_decode($settings, true);
				} catch (\Exception $e) {
					$settings = [];
				}

				$model = new Bookable($settings);
			} else {
				$model = new Bookable();
			}

			return $this->_bookable = $model;
		}

		return null;
	}

	/**
	 * @return int
	 */
	public function getStatus ()
	{
		return $this->status;
	}

	/**
	 * @return string - A user friendly version of the status
	 */
	public function getReadableStatus ()
	{
		switch ($this->status) {
			case self::STATUS_RESERVED:
				return 'reserved';
			case self::STATUS_COMPLETED:
				return 'completed';
			case self::STATUS_EXPIRED:
				return 'expired';
			default:
				return null;
		}
	}

	/**
	 * TODO: Allow each field to have its own Booking field layout
	 *
	 * @return \craft\models\FieldLayout|null
	 */
	public function getFieldLayout ()
	{
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
		return UrlHelper::cpUrl('bookings/' . $this->id);
	}

	/**
	 * @return \craft\base\FieldInterface|null
	 */
	public function getField ()
	{
		if (!$this->fieldId)
			return null;

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
		if (!$this->elementId)
			return null;

		if ($this->_element)
			return $this->_element;

		$element = \Craft::$app->elements->getElementById($this->elementId);

		return $this->_element = $element;
	}

	/**
	 * @return \craft\base\ElementInterface|null
	 */
	public function getSubElement ()
	{
		if (!$this->subElementId)
			return null;

		if ($this->_subElement)
			return $this->_subElement;

		$element = \Craft::$app->elements->getElementById($this->subElementId);

		return $this->_subElement = $element;
	}

	/**
	 * @return \craft\elements\User|null
	 */
	public function getUser ()
	{
		if (!$this->userId)
			return null;

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
		if (!$this->lineItemId)
			return null;

		if ($this->_lineItem)
			return $this->_lineItem;

		/** @noinspection PhpUnnecessaryFullyQualifiedNameInspection */
		if (class_exists(\craft\commerce\models\LineItem::class))
			return $this->_lineItem = CommerceGetters::getLineItemById($this->lineItemId);

		return null;
	}

	/**
	 * @return \craft\commerce\elements\Order|null
	 */
	public function getOrder ()
	{
		if (!$this->orderId)
			return null;

		if ($this->_order)
			return $this->_order;

		/** @noinspection PhpUnnecessaryFullyQualifiedNameInspection */
		if (class_exists(\craft\commerce\elements\Order::class))
			return $this->_order = CommerceGetters::getOrderById($this->orderId);

		return null;
	}

	/**
	 * @return \craft\commerce\models\Customer|null
	 */
	public function getCustomer ()
	{
		if (!$this->customerId)
			return null;

		if ($this->_customer)
			return $this->_customer;

		/** @noinspection PhpUnnecessaryFullyQualifiedNameInspection */
		if (class_exists(\craft\commerce\models\Customer::class))
			return $this->_customer = CommerceGetters::getCustomerById($this->customerId);

		return null;
	}

	/**
	 * The date/time this booking will expire
	 *
	 * @return \DateTime|null
	 */
	public function getExpiryTime ()
	{
		if (!$this->reservationExpiry || $this->status !== self::STATUS_RESERVED)
			return null;

		$dur = Bookings::getInstance()->settings->expiryDuration;

		return $this->reservationExpiry->modify('+' . $dur . ' seconds');
	}

	// Elements Index
	// =========================================================================

	protected static function defineTableAttributes (): array
	{
		return [
			'number' => ['label' => \Craft::t('bookings', 'Number')],
			'id' => ['label' => \Craft::t('bookings', 'ID')],
			'slotStart' => ['label' => \Craft::t('bookings', 'When')],
			'slotEnd' => ['label' => \Craft::t('bookings', 'Until')],
			'customerEmail' => ['label' => \Craft::t('bookings', 'Customer Email')],
			'dateBooked' => ['label' => \Craft::t('bookings', 'Date Booked')],
			'dateCreated' => ['label' => \Craft::t('bookings', 'Date Created')],
			'dateUpdated' => ['label' => \Craft::t('bookings', 'Date Updated')],

			// TODO: Field, Element, User, Line Item, Order, Customer
		];
	}

	protected static function defineDefaultTableAttributes (string $source): array {
		$attrs = parent::defineDefaultTableAttributes($source);

		$attrs[] = 'number';
		$attrs[] = 'customerEmail';
		$attrs[] = 'slotStart';
		$attrs[] = 'dateBooked';
		$attrs[] = 'dateUpdated';

		return $attrs;
	}

	public static function sortOptions (): array
	{
		return [
			'number' => \Craft::t('bookings', 'Number'),
			'id' => \Craft::t('bookings', 'ID'),
			'slotStart' => \Craft::t('bookings', 'When'),
			'slotEnd' => \Craft::t('bookings', 'Until'),
			'dateBooked' => \Craft::t('bookings', 'Date Booked'),
			[
				'label' => \Craft::t('bookings', 'Date Updated'),
				'orderBy' => BookingRecord::$tableNameRaw . '.dateUpdated',
				'attribute' => 'dateUpdated',
			],
		];
	}

	protected static function defineSources (string $context = null): array
	{
		// TODO: This could be improved w/ better grouping of elements (i.e. by Product / Entry Type)

		$sources = [
			'*' => [
				'key' => '*',
				'label' => \Craft::t('bookings', 'All Bookings'),
				'criteria' => ['status' => self::STATUS_COMPLETED],
				'defaultSort' => ['slotStart', 'desc']
			],
		];

		$enabledBookables = (new Query())
			->select(['bookables.ownerId'])
			->from([BookableRecord::$tableName . ' bookables'])
			->where(['bookables.enabled' => true]);

		$enabledBookableElementIds = $enabledBookables->column();

		$elements = (new Query())
			->select(['content.title', 'elements.id', 'elements.type'])
			->from(['{{%elements}} elements'])
			->where([
				'elements.id' => $enabledBookableElementIds,
				'elements.enabled' => true,
				'elements.archived' => false,
			])
			->innerJoin(
				'{{%content}} content',
				'{{%content}}.{{%elementId}} = {{%elements}}.{{%id}} AND {{%content}}.{{%siteId}} = ' . \Craft::$app->sites->primarySite->id
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
				$key = 'element:' . $element['id'];

				$sources[$key] = [
					'key' => $key,
					'label' => $element['title'],
					'criteria' => [
						'elementId' => $element['id'],
						'status' => self::STATUS_COMPLETED,
					],
					'defaultSort' => ['slotStart', 'desc']
				];
			}
		}

		return $sources;
	}

	// Helpers
	// =========================================================================

	/**
	 * @return bool
	 */
	private function _commerceInstalled (): bool
	{
		return \Craft::$app->plugins->isPluginInstalled('commerce');
	}

	/**
	 * @return bool
	 */
	private function _elementIsPurchasable (): bool
	{
		/** @noinspection PhpUnnecessaryFullyQualifiedNameInspection */
		if (class_exists(\craft\commerce\base\Purchasable::class))
			return (
				CommerceValidators::isElementPurchasable($this->getElement())
				|| CommerceValidators::isElementPurchasable($this->getSubElement())
			);

		return false;
	}

}