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
use ether\bookings\helpers\DateHelper;
use ether\bookings\models\Event;
use ether\bookings\records\BookedSlotRecord;
use ether\bookings\records\EventRecord;


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

	public function actionGetEvent ()
	{
		$eventId = \Craft::$app->request->getRequiredParam('eventId');

		$event = $this->_eventsQuery()
			->where(['e.id' => $eventId])
			->one();

		return $this->asJson([$event]);
	}

	public function actionGetEvents ()
	{
		$enabledEvents = $this->_eventsQuery()
			->where(['e.enabled' => true])
			->all();

		return $this->asJson($enabledEvents);
	}

	public function actionGetBookings ()
	{
		$request = \Craft::$app->request;
		$eventId = $request->getRequiredParam('eventId');
		$offset  = $request->getParam('offset');
		$slot    = $request->getParam('slot');

		if ($slot !== null)
			$slot = DateHelper::toUTCDateTime($slot);

		$bookings = Bookings::getInstance()->bookings->getBookingsByEventIdAndSlot(
			$eventId,
			$slot,
			$offset
		);

		return $this->asJson($bookings);
	}

	// Export
	// -------------------------------------------------------------------------

	public function actionExport ()
	{
		$eventId = \Craft::$app->request->getRequiredParam('eventId');

		$slots = Bookings::getInstance()->reports->allSlotsForEvent($eventId);

		$out = fopen('php://output', 'w');
		fputcsv($out, array_keys($slots[0]));
		foreach ($slots as $slot)
			fputcsv($out, array_values($slot));
		fclose($out);
		exit();
	}

	// Helpers
	// =========================================================================

	private function _eventsQuery ()
	{
		return (new Query())
			->select(['e.id', 'c.title'])
			->from([EventRecord::$tableName . ' e'])
			->leftJoin('{{%content}} c', 'e.elementId = c.elementId');
	}

}