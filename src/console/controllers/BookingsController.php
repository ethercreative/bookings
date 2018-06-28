<?php
/**
 * Bookings plugin for Craft CMS 3.x
 *
 * @link      https://ethercreative.co.uk
 * @copyright Copyright (c) Ether Creative
 */

namespace ether\bookings\console\controllers;

use ether\bookings\Bookings;
use yii\console\Controller;
use yii\console\ExitCode;


/**
 * Class BookingsController
 *
 * @author  Ether Creative
 * @package ether\bookings\console
 * @since   1.0.0
 */
class BookingsController extends Controller
{

	/**
	 * Will clear any expired bookings when run.
	 *
	 * We recommend running this on a CRON every 5 - 10 minutes for average load
	 * sites, or every 1 minute or less for high load sites.
	 *
	 * ./craft bookings/bookings/clear-expired
	 *
	 * @param bool $force If true the `clearExpiredDuration` will be ignored,
	 *                    meaning all bookings marked as EXPIRED will be deleted
	 *
	 * @return int
	 * @throws \Throwable
	 */
	public function actionClearExpired (bool $force = false)
	{
		Bookings::getInstance()->booking->clearExpiredBookings($force);
		return ExitCode::OK;
	}

}