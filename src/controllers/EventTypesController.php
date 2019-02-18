<?php
/**
 * Bookings for Craft CMS
 *
 * @link      https://ethercreative.co.uk
 * @copyright Copyright (c) 2019 Ether Creative
 */

namespace ether\bookings\controllers;

use craft\helpers\UrlHelper;
use ether\bookings\Bookings;
use ether\bookings\models\EventType;
use yii\web\HttpException;
use yii\web\Response;

/**
 * Class EventTypesController
 *
 * @author  Ether Creative
 * @package ether\bookings\controllers
 */
class EventTypesController extends BaseCpController
{

	public function actionIndex (): Response
	{
		$eventTypes = Bookings::$i->eventTypes->getAllEventTypes();

		return $this->renderTemplate(
			'bookings/settings/eventtypes/index',
			compact('eventTypes')
		);
	}

	/**
	 * @param int|null       $eventTypeId
	 * @param EventType|null $eventType
	 *
	 * @return Response
	 * @throws HttpException
	 */
	public function actionEdit (int $eventTypeId = null, EventType $eventType = null): Response
	{
		$variables = [
			'eventTypeId'  => $eventTypeId,
			'eventType'    => $eventType,
			'newEventType' => false,
			'fullPageForm' => true,
		];

		// Get event type (if none set)
		if ($eventType === null)
		{
			if ($eventTypeId === null)
			{
				$variables['eventType'] = new EventType();
				$variables['newEventType'] = true;
			}
			else
			{
				$variables['eventType'] = Bookings::$i->eventTypes->getEventTypeById($eventTypeId);

				if (!$variables['eventType'])
					throw new HttpException(404);
			}
		}

		// Set title
		if ($eventTypeId === null)
			$variables['title'] = Bookings::t('Create an Event Type');
		else
			$variables['title'] = $variables['eventType']->name;

		// Set tabs
		$variables['tabs'] = [
			'eventTypeSettings' => [
				'label' => Bookings::t('Settings'),
				'url' => '#event-type-settings',
			],
			'eventFields' => [
				'label' => Bookings::t('Event Fields'),
				'url' => '#event-fields',
			],
		];

		$variables['selectedTab'] = 'eventTypeSettings';

		// Set Breadcrumbs
		$variables['crumbs'] = [
			[
				'label' => Bookings::t('Bookings Settings'),
				'url' => UrlHelper::cpUrl('bookings/settings'),
			],
			[
				'label' => Bookings::t('Event Types'),
				'url' => UrlHelper::cpUrl('bookings/settings/eventtypes'),
			],
		];

		return $this->renderTemplate(
			'bookings/settings/eventtypes/_edit',
			$variables
		);
	}

	public function actionSave (): Response
	{
		// TODO:

		return null;
	}

	public function actionDelete (): Response
	{
		// TODO:

		return null;
	}

}
