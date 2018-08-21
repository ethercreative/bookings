<?php
/**
 * Created by PhpStorm.
 * User: tam
 * Date: 09/08/2018
 * Time: 16:26
 */

namespace ether\bookings\controllers;


use craft\web\Controller;
use ether\bookings\Bookings;
use ether\bookings\common\Availability;

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