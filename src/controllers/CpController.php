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
 * Class CpController
 *
 * @author  Ether Creative
 * @package ether\bookings\controllers
 * @since   1.0.0
 */
class CpController extends Controller
{

	/**
	 * @throws \yii\web\ForbiddenHttpException
	 */
	public function init()
	{
		$this->requirePermission('bookings-manageBookings');
		parent::init();
	}

	// Actions
	// =========================================================================

	public function actionIndex (): Response
	{
		return $this->renderTemplate('bookings/index');
	}

}