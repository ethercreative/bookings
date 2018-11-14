<?php
/**
 * Bookings plugin for Craft CMS 3.x
 *
 * @link      https://ethercreative.co.uk
 * @copyright Copyright (c) 2018 Ether Creative
 */

namespace ether\bookings\controllers;

use craft\web\Controller;
use ether\bookings\Bookings;
use yii\web\HttpException;

/**
 * Class DefaultController
 *
 * @author  Ether Creative
 * @package ether\bookings\controllers
 */
class DefaultController extends Controller
{

	protected $allowAnonymous = true;

	public function actionIndex ()
	{
		return 'hi';
	}

	/**
	 * Saves the booked ticket
	 *
	 * @return null|\yii\web\Response
	 * @throws HttpException
	 * @throws \Throwable
	 * @throws \craft\errors\ElementNotFoundException
	 * @throws \yii\base\Exception
	 * @throws \yii\web\BadRequestHttpException
	 */
	public function actionSaveBookedTicket ()
	{
		$this->requirePostRequest();
		$craft = \Craft::$app;
		$request = $craft->request;

		$id = $request->getRequiredBodyParam('id');
		$ticket = Bookings::getInstance()->tickets->getBookedTicketById($id);

		if (!$ticket)
			throw new HttpException('Unable to find ticket with id: ' . $id);

		$ticket->setFieldValuesFromRequest('fields');

		if (
			!$ticket->validate() ||
			!Bookings::getInstance()->tickets->saveBookedTicket($ticket)
		) {
			$error = \Craft::t('tickets', 'Unable to update booked ticket.');

			if ($request->getAcceptsJson())
			{
				return $this->asJson([
					'error' => $error,
					'success' => !$ticket->hasErrors(),
					'ticket' => $ticket->toArray(),
				]);
			}

			$craft->urlManager->setRouteParams([
				'ticket' => $ticket,
			]);

			$craft->session->setError($error);

			return null;
		}

		if ($request->getAcceptsJson())
		{
			return $this->asJson([
				'success' => !$ticket->hasErrors(),
				'ticket' => $ticket->toArray(),
			]);
		}

		$craft->session->setNotice(
			\Craft::t('bookings', 'Booked ticket updated.')
		);

		$craft->urlManager->setRouteParams([
			'ticket' => $ticket,
		]);

		return $this->redirectToPostedUrl();
	}

}