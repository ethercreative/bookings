<?php
/**
 * Bookings for Craft CMS
 *
 * @link      https://ethercreative.co.uk
 * @copyright Copyright (c) 2019 Ether Creative
 */

namespace ether\bookings\controllers;

use ether\bookings\web\assets\eventindex\EventIndexAsset;
use yii\web\Response;

/**
 * Class EventsController
 *
 * @author  Ether Creative
 * @package ether\bookings\controllers
 */
class EventsController extends BaseCpController
{

	/**
	 * @inheritdoc
	 * @throws \yii\web\ForbiddenHttpException
	 */
	public function init ()
	{
		$this->requirePermission('bookings-manageEvents');
		parent::init();
	}

	/**
	 * @return Response
	 * @throws \yii\base\InvalidConfigException
	 */
	public function actionIndex (): Response
	{
		\Craft::$app->view->registerAssetBundle(EventIndexAsset::class);
		return $this->renderTemplate('bookings/events/_index');
	}

}
