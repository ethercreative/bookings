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
use ether\bookings\models\BookingSettings;
use yii\web\Response;


/**
 * Class BookingSettingsController
 *
 * @author  Ether Creative
 * @package ether\bookings\controllers
 * @since   1.0.0
 */
class BookingSettingsController extends Controller
{

	/**
	 * Init
	 *
	 * @throws \yii\web\ForbiddenHttpException
	 */
	public function init ()
	{
		$this->requirePermission('bookings-manageSettings');

		parent::init();
	}

	/**
	 * Render the settings edit
	 *
	 * @param array $variables
	 *
	 * @return Response
	 */
	public function actionEdit (array $variables = []): Response
	{
		$variables['bookingSettings'] =
			Bookings::getInstance()->bookingSettings->getOrderSettingsByHandle('defaultBooking');

		return $this->renderTemplate(
			'bookings/settings/bookingsettings/_edit',
			$variables
		);
	}

	/**
	 * Save the settings
	 *
	 * @throws \yii\web\BadRequestHttpException
	 * @throws \yii\db\Exception
	 */
	public function actionSave ()
	{
		$this->requirePostRequest();

		$craft = \Craft::$app;

		$bookingSettings = new BookingSettings();

		// Shared attributes
		$bookingSettings->id = $craft->getRequest()->getBodyParam('bookingSettingsId');
		$bookingSettings->name = 'Default Booking';
		$bookingSettings->handle = 'defaultBooking';

		// Set the field layout
		$fieldLayout = $craft->getFields()->assembleLayoutFromPost();
		$fieldLayout->type = Booking::class;
		$bookingSettings->setFieldLayout($fieldLayout);

		// Save it
		if (Bookings::getInstance()->bookingSettings->saveBookingSettings($bookingSettings)) {
			$craft->getSession()->setNotice(
				\Craft::t(
					'bookings',
					'Booking settings saved.'
				)
			);
			$this->redirectToPostedUrl($bookingSettings);
		} else {
			$craft->getSession()->setError(
				\Craft::t(
					'bookings',
					'Couldn’t save booking settings.'
				)
			);
		}

		$craft->getUrlManager()->setRouteParams(['bookingSettings' => $bookingSettings]);
	}

}