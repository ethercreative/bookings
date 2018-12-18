<?php
/**
 * Bookings plugin for Craft CMS 3.x
 *
 * @link      https://ethercreative.co.uk
 * @copyright Copyright (c) Ether Creative
 */

namespace ether\bookings\controllers;

use craft\helpers\StringHelper;
use craft\helpers\UrlHelper;
use craft\web\Controller;
use ether\bookings\Bookings;
use ether\bookings\web\assets\cp\BookingsCpAsset;
use yii\web\NotFoundHttpException;
use yii\web\Response;
use ether\bookings\web\assets\bookingindex\BookingIndexAsset;

/**
 * Class CpController
 *
 * @author  Ether Creative
 * @package ether\bookings\controllers
 * @since   1.0.0
 */
class CpController extends Controller
{

	/**
	 * @throws \yii\web\ForbiddenHttpException
	 */
	public function init ()
	{
		$this->requirePermission('bookings-manageBookings');
		parent::init();
	}

	// Actions
	// =========================================================================

	/**
	 * @return \yii\web\Response
	 * @throws \yii\base\InvalidConfigException
	 */
	public function actionIndex ()
	{
		$view = $this->getView();
		$view->registerAssetBundle(BookingsCpAsset::class);

		return $this->renderTemplate('bookings/cp/index', [
			'events' => Bookings::getInstance()->api->getEvents(),
		]);
	}

	public function actionEvent (int $eventId)
	{
		$view = $this->getView();
		$view->registerAssetBundle(BookingsCpAsset::class);

		return $this->renderTemplate('bookings/cp/_event', [
			'event' => Bookings::getInstance()->api->getEventById($eventId),
		]);
	}

	/**
	 * @return Response
	 * @throws NotFoundHttpException
	 * @throws \yii\web\BadRequestHttpException
	 */
	public function actionTicketFields ()
	{
		$id = \Craft::$app->request->getRequiredParam('id');
		$booking = Bookings::getInstance()->bookings->getBookingById($id);

		if (!$booking)
			throw new NotFoundHttpException('Unable to find booking with ID: ' . $id);

		return $this->renderTemplate(
			'bookings/_ticketFields',
			compact('booking')
		);
	}

	public function actionResource (string $fileName = ''): Response
	{
		$bundle = new BookingIndexAsset();
		$baseAssetsUrl = \Craft::$app->assetManager->getPublishedUrl(
			$bundle->sourcePath
		);
		$url = $baseAssetsUrl . '/' . $fileName;

		return $this->redirect($url);
	}

}