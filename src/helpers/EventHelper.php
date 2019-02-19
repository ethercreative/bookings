<?php
/**
 * Bookings for Craft CMS
 *
 * @link      https://ethercreative.co.uk
 * @copyright Copyright (c) 2019 Ether Creative
 */

namespace ether\bookings\helpers;

use craft\helpers\DateTimeHelper;
use ether\bookings\Bookings;
use ether\bookings\elements\Event;
use yii\web\HttpException;

/**
 * Class EventHelpers
 *
 * @author  Ether Creative
 * @package ether\bookings\helpers
 */
class EventHelper
{

	/**
	 * @return Event
	 * @throws HttpException
	 */
	public static function populateEventFromPost (): Event
	{
		$request = \Craft::$app->getRequest();

		$eventId = $request->getBodyParam('eventId');
		$siteId  = $request->getBodyParam('siteId');

		if ($eventId)
		{
			$event = Bookings::$i->events->getEventById($eventId, $siteId);

			if (!$event)
				throw new HttpException(
					404,
					Bookings::t(
						'No event with the ID "{id}"',
						['id' => $eventId]
					)
				);
		}
		else
		{
			$event = new Event();
		}

		$event->typeId = $request->getBodyParam('typeId');
		$event->authorId = $request->getBodyParam('authorId');
		$event->siteId = $siteId ?? $event->siteId;
		$event->enabled = (bool) $request->getBodyParam('enabled');

		if (($postDate = $request->getBodyParam('postDate')) !== null)
			$event->postDate = DateTimeHelper::toDateTime($postDate) ?: null;

		if (($expiryDate = $request->getBodyParam('expiryDate')) !== null)
			$event->expiryDate = DateTimeHelper::toDateTime($expiryDate) ?: null;

		$event->slug = $request->getBodyParam('slug');
		$event->enabledForSite = (bool) $request->getBodyParam('enabledForSite', $event->enabledForSite);
		$event->title = $request->getBodyParam('title', $event->title);

		/** @noinspection PhpParamsInspection */
		$event->setFieldValuesFromRequest('fields');

		return $event;
	}

}
