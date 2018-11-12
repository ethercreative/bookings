<?php
/**
 * Bookings plugin for Craft CMS 3.x
 *
 * @link      https://ethercreative.co.uk
 * @copyright Copyright (c) 2018 Ether Creative
 */

namespace ether\bookings\console\controllers;

use ether\bookings\Bookings;
use yii\console\Controller;
use yii\console\ExitCode;

/**
 * Class DefaultController
 *
 * @author  Ether Creative
 * @package ether\bookings\console\controllers
 */
class DefaultController extends Controller
{

	/**
	 * @return int
	 * @throws \Throwable
	 */
	public function actionIndex ()
	{
		// Clear expired bookings
		Bookings::getInstance()->bookings->clearExpiredBookings();

		// Update next available cache
		// TODO: this

		return ExitCode::OK;
	}

}