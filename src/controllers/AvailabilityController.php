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
use ether\bookings\common\Availability;

/**
 * Class AvailabilityController
 *
 * @author  Ether Creative
 * @package ether\bookings\controllers
 * @since   1.0.0
 */
class AvailabilityController extends Controller
{

	protected $allowAnonymous = true;

	/**
	 * @return array|\yii\web\Response
	 * @throws \yii\base\Exception|\yii\web\BadRequestHttpException|\Exception
	 */
	public function actionIndex ()
	{
		$this->requirePostRequest();
		$bookings = Bookings::getInstance();
		$request = \Craft::$app->request;

		$eventId = $request->getRequiredParam('eventId');

		$event = $bookings->events->getEventById($eventId);

		if (!$event)
			return $this->asErrorJson(
				\Craft::t(
					'bookings',
					'Unable to find event matching the given ID'
				)
			);

		$availability = new Availability($event);

		if ($ticketId = $request->getParam('ticketId'))
			if ($ticket = $bookings->tickets->getTicketById($ticketId))
				$availability->ticket($ticket);

		if ($start = $request->getParam('start'))
			$availability->start($start);

		if ($end = $request->getParam('end'))
			$availability->end($end);

		if ($limit = $request->getParam('limit'))
			$availability->limit($limit);

		if ($group = $request->getParam('group'))
			$availability->groupBy($group);

		return $this->asJson($availability->all());
	}

}