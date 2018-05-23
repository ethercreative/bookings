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
use craft\events\RegisterComponentTypesEvent;
use craft\events\RegisterUrlRulesEvent;
use craft\events\RegisterUserPermissionsEvent;
use craft\helpers\UrlHelper;
use craft\services\Fields;
use craft\services\UserPermissions;
use craft\web\UrlManager;
use ether\bookings\fields\BookableField;
use ether\bookings\services\BookingSettingsService;
use ether\bookings\services\FieldService;
use yii\base\Event;

/**
 * @author    Ether Creative
 * @package   Bookings
 * @since     1.0.0-alpha.1
 *
 * @property FieldService $field
 * @property BookingSettingsService $bookingSettings
 */
class Bookings extends Plugin
{

	// Properties
	// =========================================================================

	public $schemaVersion = '1.0.0';

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
			'bookingSettings' => BookingSettingsService::class,
		]);

		// Events
		// ---------------------------------------------------------------------

		Event::on(
			Fields::class,
			Fields::EVENT_REGISTER_FIELD_TYPES,
			[$this, 'onRegisterFieldTypes']
		);

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

		// Misc
		// ---------------------------------------------------------------------

		$this->_registerPoweredByHeader();
	}

	// Craft
	// =========================================================================

	public function getSettingsResponse()
	{
		return \Craft::$app->getResponse()->redirect(
			UrlHelper::cpUrl('bookings/settings')
		);
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

	public function onRegisterFieldTypes (RegisterComponentTypesEvent $event)
	{
		$event->types[] = BookableField::class;
	}

	public function onRegisterCpUrlRules (RegisterUrlRulesEvent $event)
	{
		$event->rules['bookings'] = 'bookings/cp/index';

		// Settings
		$event->rules['bookings/settings/bookingsettings'] = 'bookings/booking-settings/edit';
	}

	public function onRegisterUserPermissions (RegisterUserPermissionsEvent $event)
	{
		$event->permissions[\Craft::t('bookings', 'Bookings')] = [
			'bookings-manageBookings' => ['label' => \Craft::t('bookings', 'Manage Bookings')],
			'bookings-manageSettings' => ['label' => \Craft::t('bookings', 'Manage Settings')],
		];
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
