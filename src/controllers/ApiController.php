<?php

namespace ether\bookings\controllers;

use craft\web\Controller;
use ether\bookings\models\Bookable;
use ether\bookings\models\ExRule;
use ether\bookings\models\RecursionRule;
use yii\web\BadRequestHttpException;

class ApiController extends Controller
{

	protected $allowAnonymous = false;
	public $enableCsrfValidation = false;

	public function __construct ()
	{
		parent::__construct(...func_get_args());

		// Allow anon when in plugin development
		$this->allowAnonymous = getenv('ETHER_ENVIRONMENT');
	}

	// Actions
	// =========================================================================

	/**
	 * Gets a preview of the calendar for the given rules.
	 *
	 * @throws \yii\web\BadRequestHttpException
	 */
	public function actionGetCalendar ()
	{
		if ($this->allowAnonymous) {
			\Craft::$app->getResponse()->getHeaders()->set(
				'Access-Control-Allow-Origin',
				'*'
			);
		}

		$this->requireAcceptsJson();
		$this->requirePostRequest();

		$body = $this->_getJson();
		$baseRule = $this->_getRequiredJsonParam($body, 'baseRule');
		$exceptions = $this->_getRequiredJsonParam($body, 'exceptions');

		$baseRule = new RecursionRule($baseRule);
		$exceptions = array_map(function ($rule) {
			return new ExRule($rule);
		}, $exceptions);

		$bookable = new Bookable([
			'baseRule' => $baseRule,
			'exRules' => $exceptions,
		]);

		return $this->asJson([
			'success' => true,
			'slots' => $bookable->getAllSlots(),
			'exceptions' => $bookable->invert()->getAllSlots(),
			'duration' => (int) $bookable->baseRule->duration,
		]);
	}

	// Helpers
	// =========================================================================

	private function _getJson ()
	{
		return json_decode(file_get_contents('php://input'), true);
	}

	/**
	 * @param array  $json
	 * @param string $handle
	 *
	 * @return mixed
	 * @throws BadRequestHttpException
	 */
	private function _getRequiredJsonParam ($json, $handle)
	{
		if (!array_key_exists($handle, $json))
			throw new BadRequestHttpException('Request missing required body param');

		return $json[$handle];
	}

	/**
	 * @param array  $json
	 * @param string $handle
	 * @param bool   $fallback
	 *
	 * @return bool
	 */
	private function _getJsonParam ($json, $handle, $fallback = false)
	{
		if (!array_key_exists($handle, $json))
			return $fallback;

		return $json[$handle];
	}

}