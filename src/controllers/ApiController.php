<?php

namespace ether\bookings\controllers;

use craft\web\Controller;
use ether\bookings\models\Bookable;
use ether\bookings\models\ExRule;
use ether\bookings\models\RecursionRule;

class ApiController extends Controller
{

	// Actions
	// =========================================================================

	/**
	 * Gets a preview of the calendar for the given rules.
	 *
	 * @throws \yii\web\BadRequestHttpException
	 */
	public function actionGetCalendar ()
	{
		$request = \Craft::$app->request;

		$this->requireAcceptsJson();
		$this->requirePostRequest();

		$body = json_decode($request->getRequiredBodyParam('body'), true);
		$baseRule = $body['baseRule'];
		$exceptions = $body['exceptions'];

		$bookable = new Bookable(compact('baseRule', 'exceptions'));

		return $this->asJson([
			'slots' => $bookable->getAllSlots(),
			'exceptions' => $bookable->invert()->getAllSlots(),
		]);
	}

}