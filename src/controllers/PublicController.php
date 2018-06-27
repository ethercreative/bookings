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


/**
 * Class PublicController
 *
 * @author  Ether Creative
 * @package ether\bookings\controllers
 * @since   1.0.0
 */
class PublicController extends Controller
{

	// Properties
	// =========================================================================

	protected $allowAnonymous = true;

	// Actions
	// =========================================================================

	/**
	 * TODO: TEST ME
	 *
	 * @throws \Throwable
	 * @throws \craft\errors\ElementNotFoundException
	 * @throws \yii\base\Exception
	 * @throws \yii\web\BadRequestHttpException
	 */
	public function actionBook ()
	{
		$this->requirePostRequest();
		$craft = \Craft::$app;

		$fieldId = $craft->request->getRequiredBodyParam('fieldId');
		$elementId = $craft->request->getRequiredBodyParam('elementId');
		$customerEmail = $craft->request->getRequiredBodyParam('customerEmail');
		$slotStart = $craft->request->getRequiredBodyParam('slotStart');
		$slotEnd = $craft->request->getBodyParam('slotEnd');

		$booking = Bookings::getInstance()->booking->create(
			compact(
				'fieldId',
				'elementId',
				'customerEmail',
				'slotStart',
				'slotEnd'
			)
		);

		return $this->redirectToPostedUrl([
			'errors' => $booking->getErrors(),
		]);
	}

}