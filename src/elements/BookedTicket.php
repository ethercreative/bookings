<?php
/**
 * Bookings plugin for Craft CMS 3.x
 *
 * @link      https://ethercreative.co.uk
 * @copyright Copyright (c) Ether Creative
 */

namespace ether\bookings\elements;

use craft\base\Element;
use craft\commerce\elements\Variant;
use craft\db\Query;
use craft\elements\db\ElementQueryInterface;
use craft\helpers\UrlHelper;
use ether\bookings\elements\db\BookedTicketQuery;
use ether\bookings\helpers\DateHelper;
use ether\bookings\integrations\commerce\CommerceGetters;
use ether\bookings\models\BookedSlot;
use ether\bookings\models\Ticket;
use ether\bookings\records\BookedSlotRecord;
use ether\bookings\records\BookedTicketRecord;
use ether\bookings\records\EventRecord;
use ether\bookings\records\TicketRecord;


/**
 * Class BookedTicket
 *
 * @author  Ether Creative
 * @package ether\bookings\elements
 * @since   1.0.0
 */
class BookedTicket extends Element
{

	// Properties
	// =========================================================================

	/** @var int */
	public $ticketId;

	/** @var int */
	public $bookingId;

	/** @var int|null */
	public $lineItemId;

	/** @var \DateTime */
	public $startDate;

	/** @var \DateTime|null */
	public $endDate = null;

	// Properties: Private
	// -------------------------------------------------------------------------

	private $_lineItem;

	// Methods
	// =========================================================================

	public function __construct (array $config = [])
	{
		parent::__construct($config);

		if ($this->startDate)
			$this->startDate = DateHelper::toUTCDateTime($this->startDate);

		if ($this->endDate)
			$this->endDate = DateHelper::toUTCDateTime($this->endDate);
	}

	public static function find (): ElementQueryInterface
	{
		return new BookedTicketQuery(self::class);
	}

	public function extraFields ()
	{
		$fields = parent::extraFields();

		$fields[] = 'productName';
		$fields[] = 'ticketName';
		$fields[] = 'slots';

		return $fields;
	}

	// Events
	// -------------------------------------------------------------------------

	/**
	 * @param bool $isNew
	 *
	 * @throws \Exception
	 */
	public function afterSave (bool $isNew)
	{
		if ($isNew)
		{
			$record = new BookedTicketRecord();
			$record->id = $this->id;
		}
		else
		{
			$record = BookedTicketRecord::findOne($this->id);

			if ($record === null)
				throw new \Exception('Invalid Booked Ticket ID: ' . $this->id);
		}

		$record->ticketId = $this->ticketId;
		$record->bookingId = $this->bookingId;
		$record->lineItemId = $this->lineItemId;
		$record->startDate = $this->startDate;
		$record->endDate = $this->endDate;

		$record->save();

		parent::afterSave($isNew);
	}

	/**
	 * @throws \Throwable
	 * @throws \yii\db\StaleObjectException
	 */
	public function afterDelete ()
	{
		BookedTicketRecord::findOne([
			'id' => $this->id,
		])->delete();

		parent::afterDelete();
	}

	// Getters
	// -------------------------------------------------------------------------

	public function getBooking ()
	{
		return Booking::findOne($this->bookingId);
	}

	public function getTicket ()
	{
		return Ticket::fromRecord(TicketRecord::findOne($this->ticketId));
	}

	public function getLineItem ()
	{
		if ($this->lineItemId === null)
			return null;

		if ($this->_lineItem)
			return $this->_lineItem;

		if (class_exists(\craft\commerce\models\LineItem::class))
			return $this->_lineItem = CommerceGetters::getLineItemById($this->lineItemId);

		return null;
	}

	public function getSlots ()
	{
		return array_map(function ($r) {
			return BookedSlot::fromRecord($r);
		}, BookedSlotRecord::findAll([
			'bookedTicketId' => $this->id,
		]));
	}

	public function getCustomerEmail ()
	{
		return $this->getBooking()->customerEmail;
	}

	public function getSlot ()
	{
		return $this->startDate->format('H:i');
	}

	public function getTicketName ()
	{
		return $this->getLineItem()->purchasable->title;
	}

	public function getProductName ()
	{
		$purchasable = $this->getLineItem()->purchasable;

		$title = '';

		if ($purchasable instanceof Variant)
			$title = $purchasable->product->title;

		return $title;
	}

	/**
	 * @return string
	 */
	public function getCpEditUrl (): string
	{
		return UrlHelper::cpUrl('bookings/bookings/' . $this->bookingId);
	}

	// Elements Index
	// =========================================================================

	protected static function defineTableAttributes (): array
	{
		return [
			'id'            => ['label' => \Craft::t('bookings', 'ID')],
			'slot'          => ['label' => 'Slot'],
			'customerEmail' => ['label' => 'Customer Email'],
			'ticketName'    => ['label' => 'Ticket'],
			'dateCreated'   => ['label' => \Craft::t('app', 'Date Created')],
			'dateUpdated'   => ['label' => \Craft::t('app', 'Date Updated')],
		];
	}

//	protected static function defineDefaultTableAttributes (string $source): array
//	{
//		$attrs = parent::defineDefaultTableAttributes($source);
//
//		$attrs[] = 'dateUpdated';
//
//		return $attrs;
//	}

	public static function sortOptions (): array
	{
		return [
			'id'         => \Craft::t('bookings', 'ID'),
			'startDate'  => \Craft::t('bookings', 'Slot'),
			[
				'label'     => \Craft::t('app', 'Date Updated'),
				'orderBy'   => BookedTicketRecord::$tableNameUnprefixed . '.dateUpdated',
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
				'key'         => '*',
				'label'       => \Craft::t('bookings', 'All Bookings'),
				'criteria'    => [],
				'defaultSort' => ['startDate', 'desc']
			],
		];

		$enabledBookables = (new Query())
			->select(['events.elementId', 'events.id'])
			->from([EventRecord::$tableName . ' events'])
			->where(['events.enabled' => true]);

		$enabledBookableElementToEvent = $enabledBookables->pairs();
		$enabledBookableElementIds     = $enabledBookables->column();

		$elements = (new Query())
			->select(['content.title', 'elements.id', 'elements.type'])
			->from(['{{%elements}} elements'])
			->where(
				[
					'elements.id'       => $enabledBookableElementIds,
					'elements.enabled'  => true,
					'elements.archived' => false,
				]
			)
			->innerJoin(
				'{{%content}} content',
				'content.[[elementId]] = elements.id AND content.[[siteId]] = ' .
				\Craft::$app->sites->primarySite->id
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
					'key'         => $key,
					'label'       => $element['title'],
					'criteria'    => [
						'eventId' => $enabledBookableElementToEvent[$element['id']],
					],
					'defaultSort' => ['startDate', 'desc']
				];
			}
		}

		return $sources;
	}

	protected static function defineSearchableAttributes (): array
	{
		return [];
	}

}