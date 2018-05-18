<?php
/**
 * Bookings plugin for Craft CMS 3.x
 *
 * @link      https://ethercreative.co.uk
 * @copyright Copyright (c) Ether Creative
 */

namespace ether\bookings\controllers;

use craft\web\Controller;
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
	 * @throws \yii\web\ForbiddenHttpException
	 */
	public function init ()
	{
		$this->requirePermission('bookings-manageSettings');

		parent::init();
	}

	public function actionEdit (array $variables = []): Response
	{
//		$variables['orderSettings'] = Plugin::getInstance()->getOrderSettings()->getOrderSettingByHandle('order');
		return $this->renderTemplate(
			'bookings/settings/bookingsettings/_edit',
			$variables
		);
	}

	/**
	 * @throws \yii\web\BadRequestHttpException
	 */
	public function actionSave ()
	{
		$this->requirePostRequest();

		\Craft::dd("TODO");

//		$orderSettings = new OrderSettingsModel();
//
//		// Shared attributes
//		$orderSettings->id = Craft::$app->getRequest()->getBodyParam('orderSettingsId');
//		$orderSettings->name = 'Order';
//		$orderSettings->handle = 'order';
//
//		// Set the field layout
//		$fieldLayout = Craft::$app->getFields()->assembleLayoutFromPost();
//		$fieldLayout->type = Order::class;
//		$orderSettings->setFieldLayout($fieldLayout);
//
//		// Save it
//		if (Plugin::getInstance()->getOrderSettings()->saveOrderSetting($orderSettings)) {
//			Craft::$app->getSession()->setNotice(Craft::t('commerce', 'Order settings saved.'));
//			$this->redirectToPostedUrl($orderSettings);
//		} else {
//			Craft::$app->getSession()->setError(Craft::t('commerce', 'Couldn’t save order settings.'));
//		}
//
//		Craft::$app->getUrlManager()->setRouteParams(['orderSettings' => $orderSettings]);
	}

}