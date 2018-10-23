<?php
/**
 * Bookings plugin for Craft CMS 3.x
 *
 * An advanced booking plugin for Craft CMS and Craft Commerce
 *
 * @link      https://ethercreative.co.uk
 * @copyright Copyright (c) 2018 Ether Creative
 */

namespace ether\bookings;

use craft\base\Plugin;
use craft\events\PluginEvent;
use craft\events\RegisterComponentTypesEvent;
use craft\events\RegisterUrlRulesEvent;
use craft\events\RegisterUserPermissionsEvent;
use craft\services\Fields;
use craft\services\Plugins;
use craft\services\UserPermissions;
use craft\web\twig\variables\CraftVariable;
use craft\web\UrlManager;
use ether\bookings\fields\EventField;
use ether\bookings\fields\TicketField;
use ether\bookings\integrations\commerce\OnCommerceUninstall;
use ether\bookings\integrations\commerce\OnOrderEvent;
use ether\bookings\models\Settings;
use ether\bookings\services\AvailabilityService;
use ether\bookings\services\BookingsService;
use ether\bookings\services\EventsService;
use ether\bookings\services\FieldService;
use ether\bookings\services\SlotsService;
use ether\bookings\services\TicketsService;
use ether\bookings\web\twig\CraftVariableBehavior;
use ether\bookings\web\twig\Extension;
use yii\base\Event;
use yii\base\Model;

/**
 * @property FieldService $field
 * @property EventsService $events
 * @property TicketsService $tickets
 * @property AvailabilityService $availability
 * @property BookingsService $bookings
 * @property SlotsService $slots
 *
 * @property Settings $settings
 *
 * @author    Ether Creative
 * @package   Bookings
 * @since     1.0.0-alpha.1
 */
class Bookings extends Plugin
{

	// Properties
	// =========================================================================

	public $schemaVersion = '1.0.4';

	public $hasCpSettings = true;

	public $hasCpSection = true;

	// Plugin
	// =========================================================================

	public function init ()
	{
		parent::init();

		// Components
		// ---------------------------------------------------------------------

		$this->setComponents([
			'field' => FieldService::class,
			'events'=> EventsService::class,
			'tickets' => TicketsService::class,
			'availability' => AvailabilityService::class,
			'bookings' => BookingsService::class,
			'slots' => SlotsService::class,
		]);

		// Events
		// ---------------------------------------------------------------------

		Event::on(
			UrlManager::class,
			UrlManager::EVENT_REGISTER_CP_URL_RULES,
			[$this, 'onRegisterCpUrlRules']
		);

		Event::on(
			UserPermissions::class,
			UserPermissions::EVENT_REGISTER_PERMISSIONS,
			[$this, 'onRegisterUserPermissions']
		);

		Event::on(
			Plugins::class,
			Plugins::EVENT_AFTER_INSTALL_PLUGIN,
			[$this, 'onPluginInstall']
		);

		Event::on(
			Plugins::class,
			Plugins::EVENT_BEFORE_UNINSTALL_PLUGIN,
			[$this, 'onPluginUninstall']
		);

		Event::on(
			CraftVariable::class,
			CraftVariable::EVENT_INIT,
			[$this, 'onVariableInit']
		);

		Event::on(
			Fields::class,
			Fields::EVENT_REGISTER_FIELD_TYPES,
			[$this, 'onRegisterFieldTypes']
		);

		if (class_exists(\craft\commerce\elements\Order::class))
		{
			Event::on(
				\craft\commerce\models\LineItem::class,
				Model::EVENT_AFTER_VALIDATE,
				[new OnOrderEvent, 'onBeforeSaveLineItem']
			);

			Event::on(
				\craft\commerce\services\LineItems::class,
				\craft\commerce\services\LineItems::EVENT_AFTER_SAVE_LINE_ITEM,
				[new OnOrderEvent, 'onAfterSaveLineItem']
			);

			Event::on(
				\craft\commerce\elements\Order::class,
				\craft\commerce\elements\Order::EVENT_BEFORE_COMPLETE_ORDER,
				[new OnOrderEvent, 'onComplete']
			);
		}

		// Twig Extension
		// ---------------------------------------------------------------------

		if (\Craft::$app->request->isSiteRequest || \Craft::$app->request->isCpRequest)
		{
			$view = \Craft::$app->view;
			$extension = new Extension();
			$view->registerTwigExtension($extension);
		}

		// Misc
		// ---------------------------------------------------------------------

		$this->_registerPoweredByHeader();
	}

	// Craft
	// =========================================================================

	public function getSettings ()
	{
		return new Settings();
	}

	public function getCpNavItem ()
	{
		$ret = parent::getCpNavItem();
		$currentUser = \Craft::$app->user;

		$ret['label'] = \Craft::t('bookings', 'Bookings');

		if ($currentUser->checkPermission('bookings-manageBookings')) {
			$ret['subnav']['bookings'] = [
				'label' => \Craft::t('bookings', 'Bookings'),
				'url' => 'bookings',
			];
		}

		if ($currentUser->checkPermission('bookings-manageSettings')) {
			$ret['subnav']['settings'] = [
				'label' => \Craft::t('bookings', 'Settings'),
				'url' => 'bookings/settings',
			];
		}

		return $ret;
	}

	// Events
	// =========================================================================

	public function onRegisterCpUrlRules (RegisterUrlRulesEvent $event)
	{
		$event->rules['bookings'] = 'bookings/cp/index';
//		$event->rules['bookings/<bookingId:\d+>'] = 'bookings/cp/edit';
		$event->rules['bookings/<url:(.*)>'] = 'bookings/cp/index';
		$event->rules['/cpresources/bookings/<fileName>'] = 'bookings/cp/resource';
	}

	public function onRegisterUserPermissions (RegisterUserPermissionsEvent $event)
	{
		$event->permissions[\Craft::t('bookings', 'Bookings')] = [
			'bookings-manageBookings' => ['label' => \Craft::t('bookings', 'Manage Bookings')],
			'bookings-manageSettings' => ['label' => \Craft::t('bookings', 'Manage Settings')],
		];
	}

	/**
	 * @param PluginEvent $event
	 *
	 * @throws \yii\db\Exception
	 */
	public function onPluginInstall (PluginEvent $event)
	{
		if ($event->plugin->getHandle() === 'Commerce')
			new OnCommerceInstall();
	}

	/**
	 * @param PluginEvent $event
	 *
	 * @throws \yii\db\Exception
	 */
	public function onPluginUninstall (PluginEvent $event)
	{
		if ($event->plugin->getHandle() === 'Commerce')
			new OnCommerceUninstall();
	}

	/**
	 * @param Event $event
	 *
	 * @throws \yii\base\InvalidConfigException
	 */
	public function onVariableInit (Event $event)
	{
		/** @var CraftVariable $variable */
		$variable = $event->sender;
		$variable->attachBehaviors([
			CraftVariableBehavior::class,
		]);

		if (\Craft::$app->request->isCpRequest)
		{
			$variable->set('events', EventsService::class);
			$variable->set('tickets', TicketsService::class);
		}
	}

	public function onRegisterFieldTypes (RegisterComponentTypesEvent $event)
	{
		$event->types[] = EventField::class;
		$event->types[] = TicketField::class;
	}

	// Events: Internal
	// -------------------------------------------------------------------------

	/**
	 * @throws \yii\db\Exception
	 */
	protected function afterInstall ()
	{
		if (\Craft::$app->plugins->isPluginInstalled('commerce'))
			new OnCommerceInstall();
	}

	// Helpers
	// =========================================================================

	private function _registerPoweredByHeader()
	{
		$craft = \Craft::$app;

		if (!$craft->request->isConsoleRequest) {
			$headers = $craft->getResponse()->getHeaders();
			if ($craft->getConfig()->getGeneral()->sendPoweredByHeader) {
				$original = $headers->get('X-Powered-By');
				$headers->set(
					'X-Powered-By',
					$original . ($original ? ', ' : '') . 'Bookings for Craft'
				);
			} else {
				header_remove('X-Powered-By');
			}
		}
	}

}
