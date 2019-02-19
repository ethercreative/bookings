<?php
/**
 * Bookings for Craft CMS
 *
 * @link      https://ethercreative.co.uk
 * @copyright Copyright (c) 2019 Ether Creative
 */

namespace ether\bookings\elements;

use craft\base\Element;
use craft\db\Query;
use craft\elements\actions\CopyReferenceTag;
use craft\elements\actions\Restore;
use craft\elements\actions\SetStatus;
use craft\elements\db\ElementQueryInterface;
use craft\elements\User;
use craft\helpers\ArrayHelper;
use craft\helpers\DateTimeHelper;
use craft\helpers\UrlHelper;
use craft\validators\DateTimeValidator;
use ether\bookings\Bookings;
use ether\bookings\elements\actions\DeleteEvent;
use ether\bookings\elements\db\EventQuery;
use ether\bookings\models\EventType;
use ether\bookings\records\Event as EventRecord;
use yii\base\InvalidConfigException;

/**
 * Class Event
 *
 * @author  Ether Creative
 * @package ether\bookings\elements
 */
class Event extends Element
{

	// Consts
	// =========================================================================

	const STATUS_LIVE    = 'live';
	const STATUS_PENDING = 'pending';
	const STATUS_EXPIRED = 'expired';
	const STATUS_FULL    = 'full';

	// Properties
	// =========================================================================

	// Public Properties
	// -------------------------------------------------------------------------

	/** @var int Event type ID */
	public $typeId;

	/** @var int Event author ID */
	public $authorId;

	/** @var \DateTime When the event is posted */
	public $postDate;

	/** @var \DateTime When the event expires */
	public $expiryDate;

	/**
	 * @var bool Whether the event was deleted along with its event type
	 * @see beforeDelete()
	 */
	public $deletedWithType = false;

	// Private Properties
	// -------------------------------------------------------------------------

	/** @var User|null */
	private $_author;

	/** @var EventType|null */
	private $_type;

	// Methods
	// =========================================================================

	// Public Static Methods
	// -------------------------------------------------------------------------

	public static function displayName (): string
	{
		return Bookings::t('Event');
	}

	public static function refHandle ()
	{
		return 'event';
	}

	public static function hasContent (): bool
	{
		return true;
	}

	public static function hasTitles (): bool
	{
		return true;
	}

	public static function hasUris (): bool
	{
		return true;
	}

	public static function isLocalized (): bool
	{
		return true;
	}

	public static function hasStatuses (): bool
	{
		return true;
	}

	public static function statuses (): array
	{
		return [
			self::STATUS_LIVE     => Bookings::t('Live'),
			self::STATUS_PENDING  => Bookings::t('Pending'),
			self::STATUS_EXPIRED  => Bookings::t('Expired'),
			self::STATUS_FULL     => Bookings::t('Full'),
			self::STATUS_DISABLED => Bookings::t('Disabled'),
		];
	}

	/**
	 * @return EventQuery|ElementQueryInterface
	 */
	public static function find (): ElementQueryInterface
	{
		return new EventQuery(static::class);
	}

	// Methods: Index
	// -------------------------------------------------------------------------

	/**
	 * @inheritdoc
	 */
	protected static function defineSources (string $context = null): array
	{
		if ($context == 'index')
		{
			$eventTypes = Bookings::$i->eventTypes->getEditableEventTypes();
			$editable = true;
		}
		else
		{
			$eventTypes = Bookings::$i->eventTypes->getAllEventTypes();
			$editable = false;
		}

		$eventTypeIds = [];

		foreach ($eventTypes as $eventType)
			$eventTypeIds[] = $eventType->id;

		$sources = [];

		$sources[] = [
			'key' => '*',
			'label' => Bookings::t('All Events'),
			'criterial' => [
				'typeId' => $eventTypeIds,
				'editable' => $editable,
			],
			'defaultSort' => ['postDate', 'desc'],
		];

		$sources[] = [
			'heading' => Bookings::t('Event Types'),
		];

		foreach ($eventTypes as $eventType)
		{
			$key = 'eventType:' . $eventType->id;
			$canEditEvents = \Craft::$app->getUser()->checkPermission(
				'bookings-manageEventType:' . $eventType->id
			);

			$sources[$key] = [
				'key' => $key,
				'label' => $eventType->name,
				'data' => [
					'handle' => $eventType->handle,
					'editable' => $canEditEvents,
				],
				'criteria' => [
					'typeId' => $eventType->id,
					'editable' => $editable,
				],
			];
		}

		return $sources;
	}

	/**
	 * @inheritdoc
	 */
	protected static function defineActions (string $source = null): array
	{
		switch ($source)
		{
			case '*':
				$eventTypes = Bookings::$i->eventTypes->getEditableEventTypes();
				break;
			default:
				if (preg_match('/^eventType:(\d+)$/', $source, $matches))
				{
					$eventType = Bookings::$i->eventTypes->getEventTypeById($matches[1]);
					if ($eventType)
						$eventTypes = [$eventType];
				}
		}

		$actions = parent::defineActions($source);

		// Copy Reference Tag
		$actions[] = \Craft::$app->getElements()->createAction([
			'type' => CopyReferenceTag::class,
		]);

		// Restore
		$actions[] = \Craft::$app->getElements()->createAction([
			'type' => Restore::class,
			'successMessage' => Bookings::t('Events restored.'),
			'partialSuccessMessage' => Bookings::t('Events partially restored.'),
			'failMessage' => Bookings::t('Events not restored.'),
		]);

		if (empty($eventTypes))
			return $actions;

		$userSession = \Craft::$app->getUser();
		$canManage = false;

		foreach ($eventTypes as $eventType)
			$canManage = $userSession->checkPermission('bookings-manageEventType:' . $eventType->id);

		if ($canManage)
		{
			$actions[] = \Craft::$app->getElements()->createAction([
				'type' => DeleteEvent::class,
				'confirmationMessage' => Bookings::t(
					'Are you sure you want to delete the selected event?'
				),
				'successMessage' => Bookings::t('Events deleted.')
			]);

			$actions[] = SetStatus::class;
		}

		return $actions;
	}

	/**
	 * @inheritdoc
	 */
	protected static function defineTableAttributes (): array
	{
		return [
			'title'       => ['label' => Bookings::t('Title')],
			'type'        => ['label' => Bookings::t('Type')],
			'slug'        => ['label' => Bookings::t('Slug')],
			'uri'         => ['label' => Bookings::t('URI')],
			'author'      => ['label' => Bookings::t('Author')],
			'postDate'    => ['label' => Bookings::t('Post Date')],
			'expiryDate'  => ['label' => Bookings::t('Expiry Date')],
			'link'        => ['label' => Bookings::t('Link')],
			'dateCreated' => ['label' => Bookings::t('Date Created')],
			'dateUpdated' => ['label' => Bookings::t('Date Updated')],
		];
	}

	/**
	 * @inheritdoc
	 */
	protected static function defineDefaultTableAttributes (string $source): array
	{
		$attrs = [];

		if ($source == '*')
			$attrs[] = 'type';

		$attrs[] = 'postDate';
		$attrs[] = 'expiryDate';
		$attrs[] = 'author';
		$attrs[] = 'link';

		return $attrs;
	}

	/**
	 * @inheritdoc
	 */
	protected static function defineSearchableAttributes (): array
	{
		return ['title'];
	}

	/**
	 * @inheritdoc
	 */
	protected static function defineSortOptions (): array
	{
		return [
			'title'      => Bookings::t('Title'),
			'postDate'   => Bookings::t('Post Date'),
			'expiryDate' => Bookings::t('Expiry Date'),
		];
	}

	/**
	 * @inheritdoc
	 * @throws InvalidConfigException
	 * @throws \Twig_Error_Loader
	 * @throws \yii\base\Exception
	 */
	protected function tableAttributeHtml (string $attribute): string
	{
		switch ($attribute)
		{
			case 'author':
				$author = $this->getAuthor();
				$html = $author
					? \Craft::$app->getView()->renderTemplate(
						'_elements/element',
						['element' => $author]
					)
					: '';

				return $html;
			case 'type':
				return \Craft::t('site', $this->getType()->name);

			default:
				return parent::tableAttributeHtml($attribute);
		}
	}

	// Static Methods: Eager Loading
	// -------------------------------------------------------------------------

	/**
	 * @inheritdoc
	 */
	public static function eagerLoadingMap (array $sourceElements, string $handle)
	{
		switch ($handle)
		{
			case 'author':
				$sourceElementIds =
					ArrayHelper::getColumn($sourceElements, 'id');

				$map = (new Query())
					->select(['id as source', 'authorId as target'])
					->from([EventRecord::TableName])
					->where([
						'and',
						['id' => $sourceElementIds],
						['not', ['authorId' => null]]
					])
					->all();

				return [
					'elementType' => User::class,
					'map'         => $map,
				];
			default:
				return parent::eagerLoadingMap($sourceElements, $handle);
		}
	}

	/**
	 * @inheritdoc
	 */
	protected static function prepElementQueryForTableAttribute (ElementQueryInterface $elementQuery, string $attribute)
	{
		switch ($attribute)
		{
			case 'author':
				$elementQuery->andWith('author');
				break;
			default:
				parent::prepElementQueryForTableAttribute($elementQuery, $attribute);
		}
	}

	// Public Methods: Getters / Setters
	// -------------------------------------------------------------------------

	/**
	 * Returns the event's type
	 *
	 * @return EventType
	 * @throws InvalidConfigException
	 */
	public function getType (): EventType
	{
		if ($this->_type !== null)
			return $this->_type;

		if ($this->typeId === null)
			throw new InvalidConfigException('Event is missing its type ID');

		return $this->_type =
			Bookings::$i->eventTypes->getEventTypeById($this->typeId);
	}

	/**
	 * @inheritdoc
	 * @throws InvalidConfigException
	 */
	public function getIsEditable (): bool
	{
		if (!$this->getType())
			return false;

		return (
			\Craft::$app->getUser()->checkPermission(
				'bookings-manageEventType:' . $this->getType()->id
			) && (
				!$this->authorId ||
				$this->authorId == \Craft::$app->getUser()->getId()
			)
		);
	}

	/**
	 * @inheritdoc
	 * @throws InvalidConfigException
	 */
	public function getSupportedSites (): array
	{
		$type = $this->getType();
		$sites = [];

		foreach ($type->getSiteSettings() as $siteSettings)
		{
			if ($type->propagateEvents || $siteSettings->siteId == $this->siteId)
			{
				$sites[] = [
					'siteId' => $siteSettings->siteId,
					'enabledByDefault' => $siteSettings->enabledByDefault,
				];
			}
		}

		return $sites;
	}

	/**
	 * @inheritdoc
	 */
	public function getStatus ()
	{
		$status = parent::getStatus();

		if ($status != self::STATUS_ENABLED)
			return $status;

		// TODO: Check if event is full (return self::STATUS_FULL)

		if ($this->postDate)
		{
			$currentTime = DateTimeHelper::currentTimeStamp();
			$postDate    = $this->postDate->getTimestamp();
			$expiryDate  = $this->expiryDate ? $this->expiryDate->getTimestamp() : null;

			if ($postDate <= $currentTime && ($expiryDate === null || $expiryDate > $currentTime))
				return self::STATUS_LIVE;

			if ($postDate > $currentTime)
				return self::STATUS_PENDING;

			return self::STATUS_EXPIRED;
		}

		return $status;
	}

	/**
	 * Returns true if the event is Live or Full
	 *
	 * @return bool
	 */
	public function getIsLive ()
	{
		$status = $this->getStatus();

		return (
			$status == self::STATUS_LIVE ||
			$status == self::STATUS_FULL
		);
	}

	/**
	 * Returns the event's author
	 *
	 * @return User|null
	 * @throws InvalidConfigException
	 */
	public function getAuthor ()
	{
		if ($this->_author !== null)
			return $this->_author;

		if ($this->authorId === null)
			return null;

		$this->_author = \Craft::$app->getUsers()->getUserById($this->authorId);

		if ($this->_author === null)
			throw new InvalidConfigException('Invalid author ID: ' . $this->authorId);

		return $this->_author;
	}

	/**
	 * Set the event's author
	 *
	 * @param User|null $author
	 */
	public function setAuthor (User $author = null)
	{
		$this->_author = $author;
	}

	/**
	 * @inheritdoc
	 * @throws InvalidConfigException
	 */
	public function getUriFormat ()
	{
		$siteSettings = $this->getType()->getSiteSettings();

		if (!isset($siteSettings[$this->siteId]))
			throw new InvalidConfigException(
				'Product\'s type (' . $this->typeId . ') is not enabled for site ' . $this->siteId
			);

		return $siteSettings[$this->siteId]->uriFormat;
	}

	/**
	 * @inheritdoc
	 * @throws InvalidConfigException
	 */
	public function getCpEditUrl ()
	{
		$type = $this->getType();

		$slug = $this->slug ? '-' . $this->slug : '';

		$url = UrlHelper::cpUrl(
			'bookings/events/' . $type->handle . '/' . $this->id . $slug
		);

		if (\Craft::$app->getIsMultiSite())
			$url .= '/' . $this->getSite()->handle;

		return $url;
	}

	/**
	 * @inheritdoc
	 * @throws InvalidConfigException
	 */
	public function getFieldLayout ()
	{
		return $this->getType()->getFieldLayout();
	}

	/**
	 * @inheritdoc
	 */
	public function getEditorHtml (): string
	{
		return parent::getEditorHtml(); // TODO: Change the autogenerated stub
	}

	// Methods: Misc
	// -------------------------------------------------------------------------

	/**
	 * @inheritdoc
	 */
	public function extraFields ()
	{
		$names = parent::extraFields();

		$names[] = 'author';
		$names[] = 'type';

		return $names;
	}

	/**
	 * @inheritdoc
	 */
	public function datetimeAttributes (): array
	{
		$attrs = parent::datetimeAttributes();

		$attrs[] = 'postDate';
		$attrs[] = 'expiryDate';

		return $attrs;
	}

	/**
	 * @inheritdoc
	 * @throws InvalidConfigException
	 */
	public function attributeLabels ()
	{
		$labels = parent::attributeLabels();

		if ($titleLabel = $this->getType()->titleLabel)
			$labels['title'] = \Craft::t('site', $titleLabel);

		return $labels;
	}

	/**
	 * @inheritdoc
	 * @throws InvalidConfigException
	 */
	public function rules ()
	{
		$rules = parent::rules();

		$rules[] = [
			['typeId', 'authorId'],
			'number',
			'integerOnly' => true,
		];

		$rules[] = [
			['postDate', 'expiryDate'],
			DateTimeValidator::class,
		];

		$rules[] = [
			['authorId'],
			'required',
			'on' => self::SCENARIO_LIVE,
		];

		return $rules;
	}

	/**
	 * @inheritdoc
	 * @throws InvalidConfigException
	 * @throws \craft\errors\SiteNotFoundException
	 */
	protected function route ()
	{
		if (!$this->getIsLive())
			return null;

		$siteId = \Craft::$app->getSites()->getCurrentSite()->id;
		$siteSettings = $this->getType()->getSiteSettings();

		if (!isset($siteSettings[$siteId]) || !$siteSettings[$siteId]->hasUrls)
			return null;

		return [
			'templates/render', [
				'template' => $siteSettings[$siteId]->template,
				'variables' => [
					'event' => $this,
				],
			],
		];
	}

	// Public Methods: Events
	// -------------------------------------------------------------------------

	/**
	 * @inheritdoc
	 */
	public function beforeValidate ()
	{
		if (!$this->authorId)
			$this->authorId = \Craft::$app->getUser()->getId();

		return parent::beforeValidate();
	}

	/**
	 * @inheritdoc
	 * @throws InvalidConfigException
	 * @throws \Throwable
	 * @throws \yii\base\Exception
	 */
	public function beforeSave (bool $isNew): bool
	{
		$type = $this->getType();
		$siteSettings = $type->getSiteSettings();

		// Verify the event type supports this site
		if (!isset($siteSettings[$this->siteId]))
			throw new \Exception(
				'The event type "' . $type->name . '" is not enabled for the site ' . $this->siteId
			);

		// Update the title
		$this->_updateTitle();

		// Ensure the field layout is set correctly
		$this->fieldLayoutId = $type->fieldLayoutId;

		// Set the post date
		if ($this->enabled && !$this->postDate)
			$this->postDate = DateTimeHelper::currentUTCDateTime();

		return parent::beforeSave($isNew);
	}

	/**
	 * @inheritdoc
	 * @throws \Exception
	 */
	public function afterSave (bool $isNew)
	{
		if ($isNew)
		{
			$record = new EventRecord();
			$record->id = $this->id;
		}
		else
		{
			$record = EventRecord::findOne($this->id);

			if (!$record)
				throw new \Exception('Invalid Event ID: ' . $this->id);
		}

		$record->typeId     = $this->typeId;
		$record->authorId   = $this->authorId;
		$record->postDate   = $this->postDate;
		$record->expiryDate = $this->expiryDate;
		$record->save(false);

		return parent::afterSave($isNew);
	}

	/**
	 * @inheritdoc
	 * @throws \yii\db\Exception
	 */
	public function beforeDelete (): bool
	{
		if (!parent::beforeDelete())
			return false;

		\Craft::$app->getDb()->createCommand()
			->update(
				EventRecord::TableName,
				['deletedWithType' => $this->deletedWithType],
				['id' => $this->id],
				[],
				false
			)->execute();

		return true;
	}

	// Helpers
	// =========================================================================

	/**
	 * Updates the events title (if it doesn't have a title field)
	 *
	 * @throws InvalidConfigException
	 * @throws \Throwable
	 * @throws \yii\base\Exception
	 */
	private function _updateTitle ()
	{
		$type = $this->getType();

		if ($type->hasTitleField)
			return;

		\Craft::$app->getLocale();

		$lang = \Craft::$app->language;
		\Craft::$app->language = $this->getSite()->language;

		$this->title = \Craft::$app->getView()->renderObjectTemplate(
			$type->titleFormat,
			$this
		);

		\Craft::$app->language = $lang;
	}

}
