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
use ether\bookings\elements\Booking;


/**
 * Class BookController
 *
 * @author  Ether Creative
 * @package ether\bookings\controllers
 * @since   1.0.0
 */
class BookController extends Controller
{

	// Properties
	// =========================================================================

	protected $allowAnonymous = true;

	// Actions
	// =========================================================================

	/**
	 * bookings/book
	 *
	 * @throws \Throwable
	 * @throws \craft\errors\ElementNotFoundException
	 * @throws \yii\base\Exception
	 * @throws \yii\web\BadRequestHttpException
	 */
	public function actionIndex ()
	{
		$this->requirePostRequest();
		$craft = \Craft::$app;

		$book = $craft->request->getRequiredBodyParam('book');
		$book = $craft->security->validateData($book);

		if ($book === false)
		{
			$craft->session->setError('Book input is invalid.');
			\Craft::getLogger()->log(
				'Book input is invalid.',
				LOG_ERR,
				'bookings'
			);
			return null;
		}

		$book = explode('_', $book);
		$elementId = $book[0];
		$fieldId = $book[1];

		$customerEmail = $craft->request->getRequiredBodyParam('customerEmail');
		$slotStart = $craft->request->getRequiredBodyParam('slotStart');
		$slotEnd = $craft->request->getBodyParam('slotEnd');

		$user = \Craft::$app->user;
		$userId = $user->isGuest ? null : $user->id;

		$booking = Bookings::getInstance()->booking->create(
			compact(
				'fieldId',
				'elementId',
				'customerEmail',
				'slotStart',
				'slotEnd',
				'userId'
			)
		);

		return $this->_redirectWithErrors($booking);
	}

	/**
	 * bookings/book/confirm
	 *
	 * @return null|\yii\web\Response
	 * @throws \Throwable
	 * @throws \craft\errors\ElementNotFoundException
	 * @throws \yii\base\Exception
	 * @throws \yii\web\BadRequestHttpException
	 */
	public function actionConfirm ()
	{
		$this->requirePostRequest();
		$craft = \Craft::$app;

		$bookingId = $craft->request->getRequiredBodyParam('booking');
		$bookingId = $craft->security->validateData($bookingId);

		if ($bookingId === false)
		{
			$craft->session->setError('Booking ID is invalid.');
			\Craft::getLogger()->log(
				'Booking ID is invalid.',
				LOG_ERR,
				'bookings'
			);
			return null;
		}

		/** @var Booking $booking */
		$booking = Booking::find()->id($bookingId)->one();

		if (!$booking)
		{
			$craft->session->setError('Unable to find booking with ID: ' . $bookingId);
			\Craft::getLogger()->log(
				'Unable to find booking with ID: ' . $bookingId,
				LOG_ERR,
				'bookings'
			);
			return null;
		}

		$booking->markAsComplete();

		return $this->_redirectWithErrors($booking);
	}

	// Helpers
	// =========================================================================

	private function _redirectWithErrors (Booking $booking)
	{
		if (\Craft::$app->getRequest()->getAcceptsJson()) {
			return $this->asJson([
				'success' => false,
				'errors' => $booking->getErrors(),
			]);
		}

		\Craft::$app->getUrlManager()->setRouteParams([
			'booking' => $booking
		]);

		return null;
	}

}