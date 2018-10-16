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
use craft\web\View;
use yii\web\Response;
use ether\bookings\Bookings;
use ether\bookings\web\assets\bookingindex\BookingIndexAsset;
use yii\web\HttpException;

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

		$view->registerJs(
			'window.bookingsBaseUrl = "' . $this->_getBookingsBaseUrl() . '";',
			View::POS_BEGIN
		);
		$view->registerAssetBundle(BookingIndexAsset::class);

		return $this->renderTemplate('bookings/index');
	}

	/**
	 * @param $bookingId
	 *
	 * @return \yii\web\Response
	 * @throws HttpException
	 */
	public function actionEdit ($bookingId)
	{
		$booking = Bookings::getInstance()->bookings->getBookingById($bookingId);

		if (!$booking)
			throw new HttpException(404);

		$title = 'Booking #' . $booking->shortNumber;
		$continueEditingUrl = 'bookings/{id}';

		return $this->renderTemplate(
			'bookings/edit',
			compact('booking', 'title', 'continueEditingUrl')
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

	// Helpers
	// =========================================================================

	private function _getBookingsBaseUrl ()
	{
		$url      = UrlHelper::url('bookings');
		$hostInfo = \Craft::$app->getRequest()->getHostInfo();
		$hostInfo = StringHelper::ensureRight($hostInfo, '/');

		return (string)substr($url, strlen($hostInfo) - 1);
	}

}