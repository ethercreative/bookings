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
	 * @param Event|null $event
	 *
	 * @return Event
	 * @throws HttpException
	 */
	public static function populateEventFromPost (Event $event = null): Event
	{
		$request = \Craft::$app->getRequest();

		$eventId = $request->getBodyParam('eventId');
		$siteId  = $request->getBodyParam('siteId');

		if (!$event && $eventId)
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
		else if (!$event)
		{
			$event = new Event();
		}

		$event->typeId = $request->getBodyParam('typeId');
		$event->siteId = $siteId ?? $event->siteId;
		$event->enabled = (bool) $request->getBodyParam('enabled');

		if (($postDate = $request->getBodyParam('postDate')) !== null)
			$event->postDate = DateTimeHelper::toDateTime($postDate) ?: null;

		if (($expiryDate = $request->getBodyParam('expiryDate')) !== null)
			$event->expiryDate = DateTimeHelper::toDateTime($expiryDate) ?: null;

		$event->slug = $request->getBodyParam('slug');
		$event->enabledForSite = (bool) $request->getBodyParam('enabledForSite', $event->enabledForSite);
		$event->title = $request->getBodyParam('title', $event->title);

		// Author
		$event->authorId = $request->getBodyParam(
			'authorId',
			$event->authorId ?: \Craft::$app->getUser()->getId()
		);

		if (is_array($event->authorId))
			$event->authorId = $event->authorId[0];

		// Fields
		$event->fieldLayoutId = null;
		$fieldsLocation = $request->getParam('fieldsLocation', 'fields');
		$event->setFieldValuesFromRequest($fieldsLocation);

		return $event;
	}

}
