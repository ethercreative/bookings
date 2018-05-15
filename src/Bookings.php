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
use craft\services\Fields;
use ether\bookings\fields\BookableField;
use ether\bookings\services\FieldService;
use yii\base\Event;

/**
 * @author    Ether Creative
 * @package   Bookings
 * @since     1.0.0-alpha.1
 *
 * @property FieldService $field
 */
class Bookings extends Plugin
{

	// Properties
	// =========================================================================

	public $schemaVersion = '1.0.0';

	// Plugin
	// =========================================================================

	public function init ()
	{
		parent::init();

		// Components
		// ---------------------------------------------------------------------

		$this->setComponents([
			'field' => FieldService::class,
		]);

		// Events
		// ---------------------------------------------------------------------

		Event::on(
			Fields::class,
			Fields::EVENT_REGISTER_FIELD_TYPES,
			[$this, 'onRegisterFieldTypes']
		);

		// Misc
		// ---------------------------------------------------------------------

		$this->_registerPoweredByHeader();
	}

	// Events
	// =========================================================================

	public function onRegisterFieldTypes (RegisterComponentTypesEvent $event)
	{
		$event->types[] = BookableField::class;
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
