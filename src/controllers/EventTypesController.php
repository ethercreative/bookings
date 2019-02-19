<?php
/**
 * Bookings for Craft CMS
 *
 * @link      https://ethercreative.co.uk
 * @copyright Copyright (c) 2019 Ether Creative
 */

namespace ether\bookings\controllers;

use craft\behaviors\FieldLayoutBehavior;
use craft\helpers\UrlHelper;
use ether\bookings\Bookings;
use ether\bookings\elements\Event;
use ether\bookings\models\EventType;
use ether\bookings\models\EventTypeSite;
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

	/**
	 * @return Response
	 * @throws HttpException
	 * @throws \craft\errors\MissingComponentException
	 * @throws \yii\base\ErrorException
	 * @throws \yii\base\Exception
	 * @throws \yii\base\InvalidConfigException
	 * @throws \yii\base\NotSupportedException
	 * @throws \yii\web\BadRequestHttpException
	 * @throws \yii\web\ServerErrorHttpException
	 */
	public function actionSave (): Response
	{
		$currentUser = \Craft::$app->getUser()->getIdentity();

		if (!$currentUser->can('bookings-manageEvents'))
			throw new HttpException(
				403,
				Bookings::t('This action is not allowed for the current user.')
			);

		$request = \Craft::$app->getRequest();
		$this->requirePostRequest();

		// Event Type
		// ---------------------------------------------------------------------

		$eventType = new EventType();

		$eventType->id = $request->getBodyParam('eventTypeId');
		$eventType->name = $request->getBodyParam('name');
		$eventType->handle = $request->getBodyParam('handle');
		$eventType->enableVersioning = $request->getBodyParam('enableVersioning', true);
		$eventType->hasTitleField = $request->getBodyParam('hasTitleField');
		$eventType->titleLabel = $request->getBodyParam('titleLabel');
		$eventType->titleFormat = $request->getBodyParam('titleFormat');
		$eventType->propagateEvents = $request->getBodyParam('propagateEvents', true);

		// Event Type Sites
		// ---------------------------------------------------------------------

		$allSiteSettings = [];
		$isMultiSite = \Craft::$app->getIsMultiSite();

		foreach (\Craft::$app->getSites()->getAllSites() as $site)
		{
			$postedSettings = $request->getBodyParam('sites.' . $site->handle);

			if ($isMultiSite && empty($postedSettings['enabled']))
				continue;

			$siteSettings = new EventTypeSite();
			$siteSettings->siteId = $site->id;
			$siteSettings->hasUrls = !empty($postedSettings['uriFormat']);
			$siteSettings->uriFormat = $postedSettings['uriFormat'];
			$siteSettings->template = $postedSettings['template'];
			$siteSettings->enabledByDefault = (bool) $postedSettings['enabledByDefault'];

			$allSiteSettings[$site->id] = $siteSettings;
		}

		$eventType->setSiteSettings($allSiteSettings);

		// Field Layout
		// ---------------------------------------------------------------------

		$fieldLayout = \Craft::$app->getFields()->assembleLayoutFromPost();
		$fieldLayout->type = Event::class;
		/** @var FieldLayoutBehavior $fieldLayoutBehavior */
		$fieldLayoutBehavior = $eventType->getBehavior('fieldLayout');
		$fieldLayoutBehavior->setFieldLayout($fieldLayout);

		// Save
		// ---------------------------------------------------------------------

		if (Bookings::$i->eventTypes->saveEventType($eventType))
		{
			\Craft::$app->getSession()->setNotice(
				Bookings::t('Event type saved.')
			);
			return $this->redirectToPostedUrl($eventType);
		}

		\Craft::$app->getSession()->setError(
			Bookings::t('Couldn\'t save event type.')
		);

		return \Craft::$app->getUrlManager()->setRouteParams([
			'eventType' => $eventType,
		]);
	}

	/**
	 * @return Response
	 * @throws \yii\web\BadRequestHttpException
	 */
	public function actionDelete (): Response
	{
		$this->requirePostRequest();
		$this->requireAcceptsJson();

		$eventTypeId = \Craft::$app->getRequest()->getRequiredBodyParam('id');

		Bookings::$i->eventTypes->deleteEventTypeById($eventTypeId);
		return $this->asJson(['success' => true]);
	}

}
