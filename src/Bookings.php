<?php
/**
 * Bookings for Craft CMS
 *
 * @link      https://ethercreative.co.uk
 * @copyright Copyright (c) 2019 Ether Creative
 */

namespace ether\bookings;

use craft\base\Plugin;
use craft\events\RegisterComponentTypesEvent;
use craft\events\RegisterUrlRulesEvent;
use craft\events\RegisterUserPermissionsEvent;
use craft\helpers\UrlHelper;
use craft\services\Elements;
use craft\services\Fields;
use craft\services\Sites;
use craft\services\UserPermissions;
use craft\web\twig\variables\CraftVariable;
use craft\web\UrlManager;
use ether\bookings\services\Events;
use ether\bookings\services\EventTypes;
use ether\bookings\elements\Event as EventElement;
use ether\bookings\web\twig\CraftVariableBehavior;
use yii\base\Event;

/**
 * Class Bookings
 *
 * @property EventTypes $eventTypes
 * @property Events     $events
 * @author  Ether Creative
 * @package ether\bookings
 */
class Bookings extends Plugin
{

	// Public Properties
	// =========================================================================

	/** @var self */
	public static $i;

	public $schemaVersion = '1.0.8.2';

	public $hasCpSettings = true;

	public $hasCpSection = true;

	// Init
	// =========================================================================

	public function init ()
	{
		Bookings::$i = Bookings::getInstance();

		parent::init();

		$this->setComponents([
			'eventTypes' => EventTypes::class,
			'events' => Events::class,
		]);

		// Events
		// ---------------------------------------------------------------------

		$this->_registerProjectConfigEventListeners();

		Event::on(
			UrlManager::class,
			UrlManager::EVENT_REGISTER_CP_URL_RULES,
			[$this, 'onRegisterCpUrlRules']
		);

		Event::on(
			UserPermissions::class,
			UserPermissions::EVENT_REGISTER_PERMISSIONS,
			[$this, 'onRegisterPermissions']
		);

		Event::on(
			Elements::class,
			Elements::EVENT_REGISTER_ELEMENT_TYPES,
			[$this, 'onRegisterElementTypes']
		);

		Event::on(
			CraftVariable::class,
			CraftVariable::EVENT_INIT,
			[$this, 'onCraftVariableInit']
		);

		// Misc
		// ---------------------------------------------------------------------

		$this->_poweredBy();
	}

	// Craft
	// =========================================================================

	public function getCpNavItem ()
	{
		$ret = parent::getCpNavItem();
		$user = \Craft::$app->getUser();

		$ret['label'] = Bookings::t('Bookings');

		// TODO: Bookings

		if ($user->checkPermission('bookings-manageEvents'))
		{
			$ret['subnav']['events'] = [
				'label' => Bookings::t('Events'),
				'url' => 'bookings/events',
 			];
		}

		// TODO: Tickets

		// TODO: Resources

		if ($user->getIsAdmin())
		{
			$ret['subnav']['settings'] = [
				'label' => Bookings::t('Settings'),
				'url' => 'bookings/settings',
			];
		}

		return $ret;
	}

	public function getSettingsResponse ()
	{
		return \Craft::$app->getResponse()->redirect(
			UrlHelper::cpUrl('bookings/settings')
		);
	}

	// Events
	// =========================================================================

	private function _registerProjectConfigEventListeners ()
	{
		$projectConfig = \Craft::$app->getProjectConfig();

		// Event Types
		// ---------------------------------------------------------------------

		$eventTypes = Bookings::$i->eventTypes;
		$projectConfig
			->onAdd(
				EventTypes::CONFIG_EVENTTYPES_KEY . '.{uid}',
				[$eventTypes, 'handleChangedEventType']
			)
			->onUpdate(
				EventTypes::CONFIG_EVENTTYPES_KEY . '.{uid}',
				[$eventTypes, 'handleChangedEventType']
			)
			->onRemove(
				EventTypes::CONFIG_EVENTTYPES_KEY . '.{uid}',
				[$eventTypes, 'handleDeletedEventType']
			);

		Event::on(
			Fields::class,
			Fields::EVENT_AFTER_DELETE_FIELD,
			[$eventTypes, 'pruneDeletedField']
		);

		Event::on(
			Sites::class,
			Sites::EVENT_AFTER_SAVE_SITE,
			[$eventTypes, 'afterSaveSiteHandler']
		);

		Event::on(
			Sites::class,
			Sites::EVENT_AFTER_DELETE_SITE,
			[$eventTypes, 'pruneDeletedSite']
		);

		// Events
		// ---------------------------------------------------------------------

		$events = Bookings::$i->events;

		Event::on(
			Sites::class,
			Sites::EVENT_AFTER_SAVE_SITE,
			[$events, 'afterSiteSaveHandler']
		);

	}

	public function onRegisterCpUrlRules (RegisterUrlRulesEvent $event)
	{
		$event->rules['bookings'] = ['template' => 'bookings/index'];

		// Events
		// ---------------------------------------------------------------------

		$event->rules['bookings/events'] = 'bookings/events/index';
		$event->rules['bookings/events/<eventTypeHandle:{handle}>'] = 'bookings/events/index';
		$event->rules['bookings/events/<eventTypeHandle:{handle}>/new'] = 'bookings/events/edit';
		$event->rules['bookings/events/<eventTypeHandle:{handle}>/new/<siteHandle:{handle}>'] = 'bookings/events/edit';
		$event->rules['bookings/events/<eventTypeHandle:{handle}>/<eventId:\d+><slug:(?:-[^\/]*)?>'] = 'bookings/events/edit';
		$event->rules['bookings/events/<eventTypeHandle:{handle}>/<eventId:\d+><slug:(?:-[^\/]*)?>/<siteHandle:{handle}>'] = 'bookings/events/edit';

		// Settings
		// ---------------------------------------------------------------------

		// Settings: Event Types
		// ---------------------------------------------------------------------

		$event->rules['bookings/settings/eventtypes'] = 'bookings/event-types/index';
		$event->rules['bookings/settings/eventtypes/new'] = 'bookings/event-types/edit';
		$event->rules['bookings/settings/eventtypes/<eventTypeId:\d+>'] = 'bookings/event-types/edit';

	}

	public function onRegisterPermissions (RegisterUserPermissionsEvent $event)
	{
		$eventTypes = [];
		$eventTypePermissions = [];

		foreach ($eventTypes as $id => $eventType)
		{
			$eventTypePermissions['bookings-manageEventType:' . $id] = [
				'label' => Bookings::t(
					'Manage “{type}” events',
					['type' => $eventType->name]
				),
			];
		}

		$event->permissions[Bookings::t('Bookings')] = [
			'bookings-manageBookings' => [
				'label' => Bookings::t('Manage Bookings'),
			],
			'bookings-manageEvents' => [
				'label' => Bookings::t('Manage Events'),
				'nested' => $eventTypePermissions,
			],
			'bookings-manageTickets' => [
				'label' => Bookings::t('Manage Tickets'),
			],
			'bookings-manageResources' => [
				'label' => Bookings::t('Manage Resources'),
			],
		];
	}

	public function onRegisterElementTypes (RegisterComponentTypesEvent $event)
	{
		$event->types[] = EventElement::class;
	}

	public function onCraftVariableInit (Event $event)
	{
		/** @var CraftVariable $variable */
		$variable = $event->sender;
		$variable->attachBehavior(
			'bookings',
			CraftVariableBehavior::class
		);
	}

	// Helpers
	// =========================================================================

	// Public Helpers
	// -------------------------------------------------------------------------

	/**
	 * Translates using bookings' translations
	 *
	 * @param string $message
	 * @param array  $params
	 *
	 * @return string
	 */
	public static function t ($message, array $params = [])
	{
		return \Craft::t('bookings', $message, $params);
	}

	// Private Helpers
	// -------------------------------------------------------------------------

	/**
	 * Sends the "X-Powered-By: Bookings for Craft CMS" header (if enabled)
	 */
	private function _poweredBy ()
	{
		$craft = \Craft::$app;

		if ($craft->request->isConsoleRequest)
			return;

		$headers = $craft->getResponse()->getHeaders();

		if ($craft->getConfig()->getGeneral()->sendPoweredByHeader)
		{
			$original = $headers->get('X-Powered-By');

			$headers->set(
				'X-Powered-By',
				$original . ($original ? ', ' : '') . 'Bookings for Craft CMS'
			);
		}

		else header_remove('X-Powered-By');
	}

}
