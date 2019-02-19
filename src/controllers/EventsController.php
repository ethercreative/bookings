<?php
/**
 * Bookings for Craft CMS
 *
 * @link      https://ethercreative.co.uk
 * @copyright Copyright (c) 2019 Ether Creative
 */

namespace ether\bookings\controllers;

use craft\base\Field;
use craft\elements\User;
use craft\errors\InvalidElementException;
use craft\helpers\DateTimeHelper;
use craft\helpers\Json;
use craft\helpers\UrlHelper;
use craft\models\Site;
use ether\bookings\Bookings;
use ether\bookings\elements\Event;
use ether\bookings\helpers\EventHelper;
use ether\bookings\models\EventType;
use ether\bookings\web\assets\eventindex\EventIndexAsset;
use yii\base\InvalidConfigException;
use yii\web\ForbiddenHttpException;
use yii\web\HttpException;
use yii\web\NotFoundHttpException;
use yii\web\Response;
use yii\web\ServerErrorHttpException;

/**
 * Class EventsController
 *
 * @author  Ether Creative
 * @package ether\bookings\controllers
 */
class EventsController extends BaseCpController
{

	/**
	 * @inheritdoc
	 * @throws \yii\web\ForbiddenHttpException
	 */
	public function init ()
	{
		$this->requirePermission('bookings-manageEvents');
		parent::init();
	}

	/**
	 * @return Response
	 * @throws \yii\base\InvalidConfigException
	 */
	public function actionIndex (): Response
	{
		\Craft::$app->view->registerAssetBundle(EventIndexAsset::class);
		return $this->renderTemplate('bookings/events/_index');
	}

	/**
	 * @param string      $eventTypeHandle
	 * @param int|null    $eventId
	 * @param string|null $siteHandle
	 * @param Event|null  $event
	 *
	 * @return Response
	 * @throws ForbiddenHttpException
	 * @throws NotFoundHttpException
	 * @throws \craft\errors\SiteNotFoundException
	 * @throws \yii\base\Exception
	 * @throws \yii\base\InvalidConfigException
	 */
	public function actionEdit (
		string $eventTypeHandle,
		int $eventId = null,
		string $siteHandle = null,
		Event $event = null
	): Response {
		$variables = [
			'eventTypeHandle' => $eventTypeHandle,
			'eventId' => $eventId,
			'event' => $event,
		];

		// Site
		// ---------------------------------------------------------------------

		if ($siteHandle !== null)
		{
			$variables['site'] = \Craft::$app->getSites()->getSiteByHandle($siteHandle);

			if (!$variables['site'])
				throw new NotFoundHttpException('Invalid site handle: ' . $siteHandle);
		}

		// Edit Variables
		// ---------------------------------------------------------------------

		$this->_prepEditEventVariables($variables);

		/** @var Event $event */
		$event = $variables['event'];

		/** @var EventType $eventType */
		$eventType = $variables['eventType'];

		/** @var Site $site */
		$site = $variables['site'];

		// Title
		// ---------------------------------------------------------------------

		if (empty($event->id))
			$variables['title'] = Bookings::t('Create a new event');
		else
			$variables['title'] = $event->title;

		// URLs
		// ---------------------------------------------------------------------

		// We can't use the events `getCpEditUrl()` as it might include the site
		// handle when we don't want it.
		$variables['baseCpEditUrl'] = 'bookings/events/' . $eventType->handle . '/{id}-{slug}';

		$variables['continueEditingUrl'] = $variables['baseCpEditUrl'] . (
			\Craft::$app->getIsMultiSite() && \Craft::$app->getSites()->getCurrentSite()->id !== $site->id
				? '/' . $site->handle
				: ''
		);

		// Author
		// ---------------------------------------------------------------------

		$currentUser = \Craft::$app->getUser()->getIdentity();
		$variables['userElementType'] = User::class;
		$variables['authorOptionCriteria'] = [
			'can' => 'bookings-manageEventType:' . $event->getType()->id,
		];

		try {
			if (($variables['author'] = $event->getAuthor()) === null)
				$variables['author'] = $currentUser;
		} catch (InvalidConfigException $e) {
			$variables['author'] = $currentUser;
		}

		// Tabs
		// ---------------------------------------------------------------------

		$variables['tabs'] = [];

		foreach ($eventType->getFieldLayout()->getTabs() as $index => $tab)
		{
			// Are there any errors?
			$hasErrors = false;

			if ($event->hasErrors())
				/** @var Field $field */
				foreach ($tab->getFields() as $field)
					if ($hasErrors = $event->hasErrors($field->handle . '.*'))
						break;

			$variables['tabs'][] = [
				'label' => \Craft::t('site', $tab->name),
				'url'   => '#tab' . ($index + 1),
				'class' => $hasErrors ? 'error' : null,
			];
		}

		$variables['tabs'][] = [
			'label' => Bookings::t('Availability'),
			'url' => '#availability-container',
			// TODO: Has errors class
		];

		$variables['tabs'][] = [
			'label' => Bookings::t('Tickets'),
			'url' => '#tickets-container',
			// TODO: Has errors class
		];

		// Live Preview
		// ---------------------------------------------------------------------

		if (
			\Craft::$app->getRequest()->isMobileBrowser(true) ||
			!Bookings::$i->eventTypes->isEventTypeTemplateValid($eventType, $site->id)
		) {
			$variables['showPreviewBtn'] = false;
		}
		else
		{
			$this->getView()->registerJs('Craft.LivePreview.init(' . Json::encode([
				'fields'        => '#title-field, #fields > div > div > .field',
				'extraFields'   => '#meta-pane',
				'previewUrl'    => $event->getUrl(),
				'previewAction' => \Craft::$app->getSecurity()->hashData('bookings/events/preview'),
				'previewParams' => [
					'typeId'  => $eventType->id,
					'eventId' => $event->id,
					'siteId'  => $site->id,
				],
			]) . ');');

			$variables['showPreviewBtn'] = true;

			if ($event->id)
			{
				if ($event->getIsLive())
					$variables['shareUrl'] = $event->getUrl();
				else
					$variables['shareUrl'] = UrlHelper::actionUrl(
						'bookings/events/share',
						[
							'eventId' => $event->id,
							'siteId'  => $site->id,
						]
					);
			}
		}

		return $this->renderTemplate('bookings/events/_edit', $variables);
	}

	/**
	 * @return Response
	 * @throws ForbiddenHttpException
	 * @throws HttpException
	 * @throws InvalidConfigException
	 * @throws NotFoundHttpException
	 * @throws ServerErrorHttpException
	 * @throws \Throwable
	 * @throws \craft\errors\ElementNotFoundException
	 * @throws \craft\errors\MissingComponentException
	 * @throws \yii\base\Exception
	 * @throws \yii\web\BadRequestHttpException
	 */
	public function actionSave ()
	{
		$this->requirePostRequest();

		$request = \Craft::$app->getRequest();
		$session = \Craft::$app->getSession();
		$urlManager = \Craft::$app->getUrlManager();
		$elements = \Craft::$app->getElements();

		// Event Model
		// ---------------------------------------------------------------------

		$eventId = $request->getBodyParam('eventId');
		$siteId  = $request->getBodyParam('siteId');

		if ($eventId)
		{
			$event = Bookings::$i->events->getEventById($eventId, $siteId);

			if (!$event)
				throw new NotFoundHttpException('Event not found');
		}
		else
		{
			$event = new Event();
			$event->typeId = $request->getRequiredBodyParam('typeId');

			if ($siteId)
				$event->siteId = $siteId;
		}

		$this->_enforceEventPermissions($event);

		// Duplicate
		// ---------------------------------------------------------------------

		if ((bool) $request->getBodyParam('duplicate'))
		{
			try
			{
				$event = $elements->duplicateElement($event);
			}
			catch (InvalidElementException $e)
			{
				/** @var Event $clone */
				$clone = $e->element;

				if ($request->getAcceptsJson())
					return $this->asJson([
						'success' => false,
						'errors' => $clone->getErrors(),
					]);

				$session->setError(
					Bookings::t('Couldn\'t duplicate event.')
				);

				$event->addErrors($clone->getErrors());
				return $urlManager->setRouteParams([
					'event' => $event,
				]);
			}
			catch (\Throwable $e)
			{
				throw new ServerErrorHttpException(
					Bookings::t('An error occurred when duplicating the event.'),
					0,
					$e
				);
			}
		}

		// Populate
		// ---------------------------------------------------------------------

		$event = EventHelper::populateEventFromPost($event);

		// Save
		// ---------------------------------------------------------------------

		if ($event->enabled && $event->enabledForSite)
			$event->setScenario(Event::SCENARIO_LIVE);

		if (!$elements->saveElement($event))
		{
			if ($request->getAcceptsJson())
				return $this->asJson([
					'success' => false,
					'errors' => $event->getErrors(),
				]);

			$session->setError(
				Bookings::t('Couldn\'t save event.')
			);

			return $urlManager->setRouteParams([
				'event' => $event,
			]);
		}

		if ($request->getAcceptsJson())
		{
			$return = [
				'success' => true,
				'id' => $event->id,
				'title' => $event->title,
				'slug' => $event->slug,
			];

			if ($request->getIsCpRequest())
				$return['cpEditUrl'] = $event->getCpEditUrl();

			if (($author = $event->getAuthor()) !== null)
				$return['authorUsername'] = $author->username;

			$return['dateCreated'] = DateTimeHelper::toIso8601($event->dateCreated);
			$return['dateUpdated'] = DateTimeHelper::toIso8601($event->dateUpdated);
			$return['postDate'] = $event->postDate ? DateTimeHelper::toIso8601($event->postDate) : null;

			return $this->asJson($return);
		}

		$session->setNotice(
			Bookings::t('Event saved.')
		);

		return $this->redirectToPostedUrl($event);
	}

	/**
	 * @return Response
	 * @throws NotFoundHttpException
	 * @throws \Throwable
	 * @throws \craft\errors\MissingComponentException
	 * @throws \yii\web\BadRequestHttpException
	 */
	public function actionDelete ()
	{
		$this->requirePostRequest();

		$request = \Craft::$app->getRequest();
		$elements = \Craft::$app->getElements();
		$session = \Craft::$app->getSession();
		$urlManager = \Craft::$app->getUrlManager();

		$eventId = $request->getRequiredBodyParam('eventId');
		$siteId  = $request->getBodyParam('siteId');

		$event = Bookings::$i->events->getEventById($eventId, $siteId);

		if (!$event)
			throw new NotFoundHttpException('Event not found');

		if (!$elements->deleteElement($event))
		{
			if ($request->getAcceptsJson())
				return $this->asJson(['success' => false]);

			$session->setError(
				Bookings::t('Couldn\'t delete event.')
			);

			return $urlManager->setRouteParams([
				'event' => $event,
			]);
		}

		if ($request->getAcceptsJson())
			return $this->asJson(['success' => true]);

		$session->setNotice(
			Bookings::t('Event deleted.')
		);

		return $this->redirectToPostedUrl($event);
	}

	/**
	 * @return Response
	 * @throws ForbiddenHttpException
	 * @throws ServerErrorHttpException
	 * @throws \yii\base\InvalidConfigException
	 * @throws \yii\web\BadRequestHttpException
	 * @throws \yii\web\HttpException
	 */
	public function actionPreview (): Response
	{
		$this->requirePostRequest();

		$event = EventHelper::populateEventFromPost();

		$this->_enforceEventPermissions($event);

		return $this->_showEvent($event);
	}

	/**
	 * @param int  $eventId
	 * @param null $siteId
	 *
	 * @return Response
	 * @throws HttpException
	 * @throws ServerErrorHttpException
	 * @throws \yii\base\InvalidConfigException
	 * @throws \yii\web\BadRequestHttpException
	 */
	public function actionShare (int $eventId, $siteId = null): Response
	{
		$this->requireToken();

		$event = Bookings::$i->events->getEventById($eventId, $siteId);

		if (!$event)
			throw new HttpException(404);

		return $this->_showEvent($event);
	}

	// Helpers
	// =========================================================================

	/**
	 * @param Event $event
	 *
	 * @throws ForbiddenHttpException
	 * @throws \yii\base\InvalidConfigException
	 */
	private function _enforceEventPermissions (Event $event)
	{
		$this->requirePermission('bookings-manageEventType:' . $event->getType()->id);
	}

	/**
	 * @param array $variables
	 *
	 * @throws ForbiddenHttpException
	 * @throws NotFoundHttpException
	 * @throws \craft\errors\SiteNotFoundException
	 * @throws \yii\base\InvalidConfigException
	 */
	private function _prepEditEventVariables (array &$variables)
	{
		// Event Type
		// ---------------------------------------------------------------------

		if (!empty($variables['eventTypeHandle']))
			$variables['eventType'] =
				Bookings::$i->eventTypes->getEventTypeByHandle($variables['eventTypeHandle']);

		else if (!empty($variables['eventTypeId']))
			$variables['eventType'] =
				Bookings::$i->eventTypes->getEventTypeById($variables['eventTypeId']);

		if (empty($variables['eventType']))
			throw new NotFoundHttpException('Event Type not found');

		// Site
		// ---------------------------------------------------------------------

		$sites = \Craft::$app->getSites();

		if (\Craft::$app->getIsMultiSite())
			$variables['siteIds'] = $sites->getEditableSiteIds();
		else
			$variables['siteIds'] = [$sites->getPrimarySite()->id];

		if (!$variables['siteIds'])
			throw new ForbiddenHttpException(
				'User not permitted to edit content in any sites supported by this event type'
			);

		if (empty($variables['site']))
		{
			$variables['site'] = $sites->currentSite;

			if (!in_array($variables['site']->id, $variables['siteIds'], false))
				$variables['site'] = $sites->getSiteById($variables['siteIds'][0]);

			$site = $variables['site'];
		}
		else
		{
			/** @var Site $site */
			$site = $variables['site'];

			if (!in_array($site->id, $variables['siteIds'], false))
				throw new ForbiddenHttpException(
					'User not permitted to edit content in this site'
				);
		}

		// Event
		// ---------------------------------------------------------------------

		if (empty($variables['event']))
		{
			if (!empty($variables['eventId']))
			{
				$variables['event'] = Bookings::$i->events->getEventById(
					$variables['eventId'],
					$variables['site']->id
				);

				if (!$variables['event'])
					throw new NotFoundHttpException('Event not found');
			}
			else
			{
				$event = new Event();
				$event->typeId = $variables['eventType']->id;
				$event->enabled = true;
				$event->siteId = $site->id;

				$variables['event'] = $event;
			}
		}

		if ($variables['event']->id)
		{
			$this->_enforceEventPermissions($variables['event']);
			$variables['enabledSiteIds'] = \Craft::$app->getElements()->getEnabledSiteIdsForElement(
				$variables['event']->id
			);
		}
		else
		{
			$variables['enabledSiteIds'] = [];

			foreach ($sites->getEditableSiteIds() as $site)
				$variables['enabledSiteIds'][] = $site;
		}
	}

	/**
	 * Displays the event
	 *
	 * @param Event $event
	 *
	 * @return Response
	 * @throws ServerErrorHttpException
	 * @throws \yii\base\InvalidConfigException
	 */
	private function _showEvent (Event $event): Response
	{
		$eventType = $event->getType();

		if (!$eventType)
			throw new ServerErrorHttpException('Event type not found.');

		$siteSettings = $eventType->getSiteSettings();

		if (!isset($siteSettings[$event->siteId]) || !$siteSettings[$event->siteId]->hasUrls)
			throw new ServerErrorHttpException('The event ' . $event->id . ' doesn\'t have a URL for the site ' . $event->siteId);

		$site = \Craft::$app->getSites()->getSiteById($event->siteId);

		if (!$site)
			throw new ServerErrorHttpException('Invalid site ID: ' . $event->siteId);

		\Craft::$app->language = $site->language;

		$this->getView()->getTwig()->disableStrictVariables();

		return $this->renderTemplate(
			$siteSettings[$event->siteId]->template,
			compact('event')
		);
	}

}
