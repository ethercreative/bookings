<?php
/**
 * Bookings plugin for Craft CMS 3.x
 *
 * @link      https://ethercreative.co.uk
 * @copyright Copyright (c) Ether Creative
 */

namespace ether\bookings\controllers;

use craft\web\Controller;
use ether\bookings\Bookings;
use ether\bookings\web\assets\bookingindex\BookingIndexAsset;
use yii\web\HttpException;
use yii\web\Response;


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
	public function init()
	{
		$this->requirePermission('bookings-manageBookings');
		parent::init();
	}

	// Actions
	// =========================================================================

	/**
	 * @return Response
	 * @throws \yii\base\InvalidConfigException
	 */
	public function actionIndex (): Response
	{
		$this->view->registerAssetBundle(BookingIndexAsset::class);
		return $this->renderTemplate('bookings/index');
	}

	/**
	 * @param $bookingId
	 *
	 * @return Response
	 * @throws \Throwable
	 */
	public function actionEditBooking ($bookingId): Response
	{
		$booking = Bookings::getInstance()->booking->getBookingById($bookingId);

		if (!$booking)
			throw new HttpException(404);

		$title = 'Booking #' . $booking->shortNumber;
		$continueEditingUrl = 'bookings/{id}';

		return $this->renderTemplate(
			'bookings/edit',
			compact('booking', 'title', 'continueEditingUrl')
		);
	}

}