<?php
/**
 * Bookings for Craft CMS
 *
 * @link      https://ethercreative.co.uk
 * @copyright Copyright (c) 2019 Ether Creative
 */

namespace ether\bookings\models;

use craft\base\Model;
use craft\models\Site;
use ether\bookings\Bookings;
use yii\base\InvalidConfigException;

/**
 * Class EventTypeSite
 *
 * @author  Ether Creative
 * @package ether\bookings\models
 */
class EventTypeSite extends Model
{

	// Properties
	// =========================================================================

	// Public Properties
	// -------------------------------------------------------------------------

	/** @var int ID */
	public $id;

	/** @var int Event type ID */
	public $eventTypeId;

	/** @var int Site ID */
	public $siteId;

	/** @var bool Enabled by default */
	public $enabledByDefault = true;

	/** @var bool Has URLs */
	public $hasUrls;

	/** @var string URI Format */
	public $uriFormat;

	/** @var bool Is the URI format required? */
	public $uriFormatIsRequired = true;

	/** @var string Template path */
	public $template;

	/** @var string UID */
	public $uid;

	/** @var \DateTime */
	public $dateCreated;

	/** @var \DateTime */
	public $dateUpdated;

	// Private Properties
	// -------------------------------------------------------------------------

	/** @var EventType */
	private $_eventType;

	/** @var Site */
	private $_site;

	// Methods
	// =========================================================================

	// Public Getters / Setters
	// -------------------------------------------------------------------------

	/**
	 * Gets the Event Type
	 *
	 * @return EventType
	 * @throws InvalidConfigException
	 */
	public function getEventType ()
	{
		if ($this->_eventType !== null)
			return $this->_eventType;

		if (!$this->eventTypeId)
			throw new InvalidConfigException('Event type site is missing its event type ID');

		$this->_eventType = Bookings::$i->eventTypes->getEventTypeById($this->eventTypeId);

		if ($this->_eventType === null)
			throw new InvalidConfigException('Invalid event type ID: ' . $this->eventTypeId);

		return $this->_eventType;
	}

	/**
	 * Sets the Event Type
	 *
	 * @param EventType|null $type
	 */
	public function setEventType (EventType $type = null)
	{
		$this->_eventType = $type;
	}

	/**
	 * Gets the Site
	 *
	 * @return Site|null
	 * @throws InvalidConfigException
	 */
	public function getSite ()
	{
		if ($this->_site !== null)
			return $this->_site;

		if (!$this->siteId)
			throw new InvalidConfigException('Event type site is missing its site ID');

		$this->_site = \Craft::$app->getSites()->getSiteById($this->siteId);

		if ($this->_site === null)
			throw new InvalidConfigException('Invalid site ID: ' . $this->siteId);

		return $this->_site;
	}

	// Public Instance Methods
	// -------------------------------------------------------------------------

	/**
	 * @inheritdoc
	 */
	public function rules ()
	{
		$rules = parent::rules();

		if ($this->uriFormatIsRequired)
			$rules[] = [['uriFormat'], 'required'];

		return $rules;
	}

}
