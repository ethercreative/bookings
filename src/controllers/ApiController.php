<?php
/**
 * Bookings plugin for Craft CMS 3.x
 *
 * @link      https://ethercreative.co.uk
 * @copyright Copyright (c) Ether Creative
 */

namespace ether\bookings\controllers;

use craft\db\Query;
use craft\helpers\Json;
use craft\web\Controller;
use ether\bookings\Bookings;
use ether\bookings\common\Availability;
use ether\bookings\models\Event;
use ether\bookings\records\BookedSlotRecord;


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
	 * @throws \yii\base\Exception
	 * @throws \Exception
	 */
	public function actionGetCalendar ()
	{
		$this->requireAcceptsJson();
		$this->requirePostRequest();

		$request = \Craft::$app->request;

		$body = Json::decode($request->getRequiredBodyParam('body'), true);
		$id = $request->getBodyParam('id');
		$baseRule = $body['baseRule'];
		$exceptions = $body['exceptions'];

		if ($id)
			$event = Bookings::getInstance()->events->getEventById($id);

		if (empty($event))
			$event = new Event();

		$event->baseRule = $baseRule;
		$event->exceptions = $exceptions;

		$availability = new Availability(clone $event);

		// TODO: Move to service?
		$hasAnyBookings = (new Query())
			->from(BookedSlotRecord::$tableName)
			->where(['eventId' => $event->id])
			->count('id') > 0;

		return $this->asJson([
			'slots' => $event->getAllSlots(),
			'exceptions' => $event->invert()->getAllSlots(),
			'availability' => $availability->all(),
			'hasAnyBookings' => $hasAnyBookings,
		]);
	}

}