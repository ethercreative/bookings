<?php
/**
 * Bookings plugin for Craft CMS 3.x
 *
 * @link      https://ethercreative.co.uk
 * @copyright Copyright (c) 2018 Ether Creative
 */

namespace ether\bookings\controllers;

use craft\base\Field;
use craft\web\Controller;
use ether\bookings\Bookings;
use ether\bookings\elements\BookedTicket;
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

		if ($tickets = $request->getBodyParam('tickets'))
			return $this->_saveBookedTickets($tickets);

		return $this->_saveBookedTicket();
	}

	// Helpers
	// =========================================================================

	/**
	 * Updates a single ticket
	 *
	 * @return null|\yii\web\Response
	 * @throws HttpException
	 * @throws \Throwable
	 * @throws \craft\errors\ElementNotFoundException
	 * @throws \yii\base\Exception
	 * @throws \yii\web\BadRequestHttpException
	 */
	private function _saveBookedTicket ()
	{
		$craft   = \Craft::$app;
		$request = $craft->request;

		$id     = $request->getRequiredBodyParam('id');
		$ticket = Bookings::getInstance()->tickets->getBookedTicketById($id);

		if (!$ticket)
			throw new HttpException('Unable to find ticket with id: ' . $id);

		$ticket->setFieldValuesFromRequest('fields');

		if (
			!$ticket->validate() ||
			!Bookings::getInstance()->tickets->saveBookedTicket($ticket)
		)
		{
			$error = \Craft::t('tickets', 'Unable to update booked ticket.');

			if ($request->getAcceptsJson())
			{
				return $this->asJson([
					'error'   => $error,
					'success' => !$ticket->hasErrors(),
					'ticket'  => $ticket->toArray(),
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
				'ticket'  => $ticket->toArray(),
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

	/**
	 * Saves multiple tickets
	 *
	 * @param $tickets
	 *
	 * @return \yii\web\Response
	 * @throws HttpException
	 * @throws \Throwable
	 * @throws \craft\errors\ElementNotFoundException
	 * @throws \yii\base\Exception
	 * @throws \yii\db\Exception
	 * @throws \yii\web\BadRequestHttpException
	 */
	private function _saveBookedTickets ($tickets)
	{
		$ids = array_keys($tickets);
		$fieldsByTicketId = $tickets;

		$tickets = Bookings::getInstance()->tickets->getBookedTicketsByIds($ids);

		if (count($tickets) !== count($ids))
			throw new HttpException('Unable to find all tickets with those ID\'s');

		$craft = \Craft::$app;

		// Set the fields
		// ---------------------------------------------------------------------

		/** @var BookedTicket $ticket */
		foreach ($tickets as $ticket)
		{
			$fields = $fieldsByTicketId[$ticket->id];

			/** @var Field $field */
			foreach ($ticket->getFieldLayout()->getFields() as $field)
			{
				$value = $fields[$field->handle];

				if (!isset($value))
					continue;

				$ticket->setFieldValue($field->handle, $value);
			}
		}

		// Save the tickets
		// ---------------------------------------------------------------------

		$transaction = \Craft::$app->db->beginTransaction();
		$failed = false;

		foreach ($tickets as $ticket)
		{
			if (Bookings::getInstance()->tickets->saveBookedTicket($ticket))
				continue;

			$transaction->rollBack();
			$failed = true;
			break;
		}

		$transaction->commit();

		// Return
		// ---------------------------------------------------------------------

		if ($failed) {
			$craft->session->setError(
				\Craft::t('bookings', 'Unable to update booked tickets.')
			);
		} else {
			$craft->session->setNotice(
				\Craft::t('bookings', 'Booked tickets updated.')
			);
		}

		$craft->urlManager->setRouteParams([
			'tickets' => array_reduce($tickets, function ($a, $b) {
				$a[$b->id] = $b;
				return $a;
			}, []),
		]);

		return $this->redirectToPostedUrl();
	}

}