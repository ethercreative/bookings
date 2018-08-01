<?php
/**
 * Bookings plugin for Craft CMS 3.x
 *
 * @link      https://ethercreative.co.uk
 * @copyright Copyright (c) Ether Creative
 */

namespace ether\bookings\controllers;

use craft\helpers\Json;
use craft\web\Controller;
use ether\bookings\models\Event;


/**
 * Class ApiController
 *
 * @author  Ether Creative
 * @package ether\bookings\controllers
 * @since   1.0.0
 */
class ApiController extends Controller
{

	// Actions
	// =========================================================================

	/**
	 * Gets a preview of the calendar for the given rules.
	 *
	 * @return \yii\web\Response
	 * @throws \yii\web\BadRequestHttpException
	 */
	public function actionGetCalendar ()
	{
		$this->requireAcceptsJson();
		$this->requirePostRequest();

		$request = \Craft::$app->request;

		$body = Json::decode($request->getRequiredBodyParam('body'), true);
		list('baseRule' => $baseRule, 'exceptions' => $exceptions) = $body;

		$event = new Event();
		$event->baseRule = $baseRule;
		$event->exceptions = $exceptions;

		return $this->asJson([
			'slots' => $event->getAllSlots(),
			'exceptions' => $event->invert()->getAllSlots(),
		]);
	}

}