<?php
/**
 * Bookings plugin for Craft CMS 3.x
 *
 * @link      https://ethercreative.co.uk
 * @copyright Copyright (c) Ether Creative
 */

namespace ether\bookings\services;

use yii\base\Component;

/**
 * Class BookableTypes
 *
 * @author  Ether Creative
 * @package ether\bookings\services
 * @since   1.0.0
 */
class BookableTypes extends Component
{

	// Consts
	// =========================================================================

	/**
	 * Plugins can get notified before a bookable type is being saved.
	 *
	 * ```php
	 * use ether\bookings\events\BookableTypeEvent;
	 * use ether\bookings\services\BookableTypes;
	 * use yii\base\Event;
	 *
	 * Event::on(
	 *     BookableTypes::class,
	 *     BookableTypes::EVENT_BEFORE_SAVE_BOOKABLETYPE,
	 *     function (BookableTypeEvent $event) {
	 *         // ...
	 *     }
	 * );
	 * ```
	 *
	 * @event BookableTypeEvent The event that is triggered before a category
	 *        group is saved.
	 */
	const EVENT_BEFORE_SAVE_BOOKABLETYPE = 'beforeSaveBookableType';

}