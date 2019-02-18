<?php
/**
 * Bookings for Craft CMS
 *
 * @link      https://ethercreative.co.uk
 * @copyright Copyright (c) 2019 Ether Creative
 */

namespace ether\bookings\models;

use craft\base\Model;
use craft\behaviors\FieldLayoutBehavior;
use craft\helpers\ArrayHelper;
use craft\helpers\UrlHelper;
use craft\models\FieldLayout;
use craft\validators\HandleValidator;
use craft\validators\UniqueValidator;
use ether\bookings\Bookings;
use ether\bookings\elements\Event;
use ether\bookings\records\EventType as EventTypeRecord;

/**
 * Class EventType
 *
 * @property EventTypeSite[] $siteSettings
 * @property string          $cpEditUrl
 * @mixin FieldLayoutBehavior
 * @author  Ether Creative
 * @package ether\bookings\models
 */
class EventType extends Model
{

	// Properties
	// =========================================================================

	// Public Properties
	// -------------------------------------------------------------------------

	/** @var int|null ID */
	public $id;

	/** @var int|null Field layout ID */
	public $fieldLayoutId;

	/** @var string|null Name */
	public $name;

	/** @var string|null Handle */
	public $handle;

	/** @var bool Has title field */
	public $hasTitleField = true;

	/** @var string Title field label */
	public $titleLabel = 'Title';

	/** @var string|null Title format */
	public $titleFormat;

	/** @var string UID */
	public $uid;

	// Private Properties
	// -------------------------------------------------------------------------

	/** @var EventTypeSite[] */
	private $_siteSettings;

	// Methods
	// =========================================================================

	// Public Getters / Setters
	// -------------------------------------------------------------------------

	/**
	 * Returns the event types site-specific settings
	 *
	 * @return EventTypeSite[]
	 */
	public function getSiteSettings (): array
	{
		if ($this->_siteSettings !== null)
			return $this->_siteSettings;

		if (!$this->id)
			return [];

		$this->setSiteSettings(
			ArrayHelper::index(
				Bookings::$i->eventTypes->getEventTypeSites($this->id),
				'siteId'
			)
		);

		return $this->_siteSettings;
	}

	/**
	 * Sets the event types site-specific settings
	 *
	 * @param EventTypeSite[] $siteSettings
	 */
	public function setSiteSettings (array $siteSettings)
	{
		$this->_siteSettings = $siteSettings;

		foreach ($this->_siteSettings as $settings)
			$settings->setEventType($this);
	}

	/**
	 * Returns the CP edit url
	 *
	 * @return string
	 */
	public function getCpEditUrl (): string
	{
		return UrlHelper::cpUrl('bookings/settings/eventtypes/' . $this->id);
	}

	/**
	 * Gets the field layout for this event type
	 *
	 * @return FieldLayout
	 * @throws \yii\base\InvalidConfigException
	 */
	public function getFieldLayout (): FieldLayout
	{
		/** @var FieldLayoutBehavior $behaviour */
		$behaviour = $this->getBehavior('fieldLayout');

		return $behaviour->getFieldLayout();
	}

	// Public Instance Methods
	// -------------------------------------------------------------------------

	/**
	 * Use the handle as the string representation
	 *
	 * @return string
	 */
	public function __toString ()
	{
		return (string)$this->handle ?: static::class;
	}

	/**
	 * @inheritdoc
	 */
	public function behaviors ()
	{
		return [
			'fieldLayout' => [
				'class' => FieldLayoutBehavior::class,
				'elementType' => Event::class,
			],
		];
	}

	/**
	 * @inheritdoc
	 */
	public function attributeLabels ()
	{
		return [
			'handle'      => \Craft::t('app', 'Handle'),
			'name'        => \Craft::t('app', 'Name'),
			'titleFormat' => \Craft::t('app', 'Title Format'),
			'titleLabel'  => \Craft::t('app', 'Title Field Label'),
		];
	}

	/**
	 * @inheritdoc
	 */
	public function rules ()
	{
		$rules = parent::rules();

		$rules[] = [
			['id', 'fieldLayoutId'],
			'number',
			'integerOnly' => true,
		];

		$rules[] = [
			['name', 'handle'],
			'required',
		];

		$rules[] = [
			['name', 'handle'],
			'string',
			'max' => 255,
		];

		$rules[] = [
			['handle'],
			HandleValidator::class,
			'reservedWords' => ['id', 'dateCreated', 'dateUpdated', 'uid', 'title'],
		];

		$rules[] = [
			['name'],
			UniqueValidator::class,
			'targetClass' => EventTypeRecord::class,
			'targetAttribute' => ['name'],
			'comboNotUnique' => \Craft::t('yii', '{attribute} "{value}" has already been taken.'),
		];

		$rules[] = [
			['handle'],
			UniqueValidator::class,
			'targetClass' => EventTypeRecord::class,
			'targetAttribute' => ['handle'],
			'comboNotUnique' => \Craft::t('yii', '{attribute} "{value}" has already been taken.'),
		];

		if ($this->hasTitleField)
			$rules[] = [['titleLabel'], 'required'];
		else
			$rules[] = [['titleFormat'], 'required'];

		return $rules;
	}

}
