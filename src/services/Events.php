<?php
/**
 * Bookings for Craft CMS
 *
 * @link      https://ethercreative.co.uk
 * @copyright Copyright (c) 2019 Ether Creative
 */

namespace ether\bookings\services;

use craft\base\Component;
use craft\events\SiteEvent;
use craft\queue\jobs\ResaveElements;
use ether\bookings\elements\Event;

/**
 * Class Events
 *
 * @author  Ether Creative
 * @package ether\bookings\services
 */
class Events extends Component
{

	/**
	 * Get an event by its ID
	 *
	 * @param int  $id
	 * @param null $siteId
	 *
	 * @return Event|null
	 */
	public function getEventById (int $id, $siteId = null)
	{
		/** @var Event $event */
		$event = \Craft::$app->getElements()->getElementById(
			$id,
			Event::class,
			$siteId
		);

		return $event;
	}

	/**
	 * Handle a Site being saved
	 *
	 * @param SiteEvent $event
	 */
	public function afterSiteSaveHandler (SiteEvent $event)
	{
		\Craft::$app->getQueue()->push(new ResaveElements([
			'elementType' => Event::class,
			'criteria' => [
				'siteId' => $event->oldPrimarySiteId,
				'status' => null,
				'enabledForSite' => false,
			],
		]));
	}

}
