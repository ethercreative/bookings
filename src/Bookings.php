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
use yii\base\Event;

/**
 * @author    Ether Creative
 * @package   Bookings
 * @since     1.0.0-alpha.1
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

		Event::on(
			Fields::class,
			Fields::EVENT_REGISTER_FIELD_TYPES,
			[$this, 'onRegisterFieldTypes']
		);
	}

	// Events
	// =========================================================================

	public function onRegisterFieldTypes (RegisterComponentTypesEvent $event)
	{
		$event->types[] = BookableField::class;
	}

}
