<?php

namespace ether\bookings\controllers;

use craft\web\Controller;
use yii\base\Module;

class ApiController extends Controller
{

	protected $allowAnonymous = true;
	public $enableCsrfValidation = false;

	public function __construct (string $id, Module $module, array $config = [])
	{
		parent::__construct($id, $module, $config);

//		if (\Craft::$app->config->general->devMode) {
//			$this->allowAnonymous = true;
//			$this->enableCsrfValidation = false;
//		}
	}

	/**
	 * Gets a preview of the calendar for the given rules.
	 *
	 * @throws \yii\base\ExitException
	 * @throws \yii\web\BadRequestHttpException
	 */
	public function actionGetCalendar ()
	{
		$this->requireAcceptsJson();
		$this->requirePostRequest();
//		$baseRule = \Craft::$app->request->getRequiredBodyParam('baseRule');
//		$exceptions = \Craft::$app->request->getRequiredBodyParam('exceptions');
//
//		\Craft::dd(compact('baseRule', 'exceptions'));

		return $this->asJson([
			'success' => true,
		]);
	}

}