<?php
/**
 * Bookings plugin for Craft CMS 3.x
 *
 * @link      https://ethercreative.co.uk
 * @copyright Copyright (c) 2018 Ether Creative
 */

namespace ether\bookings\console\controllers;

use ether\bookings\Bookings;
use ether\bookings\common\scheduling\Schedule;
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
	 * Runs everything required to keep the booking data up-to-date.
	 * This should be run as a CRON every minute.
	 *
	 * CRON: * * * * * /path/to/site/craft bookings >> /dev/null 2>&1
	 * e.g.: * * * * * /var/www/vhosts/site.com/craft bookings >> /dev/null 2>&1
	 *
	 * ./craft bookings
	 *
	 * @return int
	 * @throws \Throwable
	 */
	public function actionIndex ()
	{
		$schedule = new Schedule();

		// Clear expired bookings
		// FIXME: See BookingsService.php:128
//		$schedule->queue(function () {
//			echo 'Clearing Expired' . PHP_EOL;
//			Bookings::getInstance()->bookings->clearExpiredBookings();
//		})->everyMinute();

		// Update next available cache
		$schedule->queue(function () {
			echo 'Updating Next Available Cache' . PHP_EOL;
			Bookings::getInstance()->events->refreshNextAvailableSlot();
		})->everyFiveMinutes();


		$schedule->run();

		return ExitCode::OK;
	}

	/**
	 * Will clear any expired bookings when run.
	 *
	 * We recommend running this on a CRON every 5 - 15 minutes for average load
	 * sites, or every 1 minute or less for high load sites.
	 *
	 * ./craft bookings/clear-expired [true]
	 *
	 * @param bool $force - If true the `clearExpiredDuration` setting will be
	 *                    ignored, meaning all bookings marked as EXPIRED will
	 *                    be deleted.
	 *
	 * @return int
	 * @throws \Throwable
	 */
	public function actionClearExpired (bool $force = false)
	{
		Bookings::getInstance()->bookings->clearExpiredBookings($force);

		return ExitCode::OK;
	}

	/**
	 * Refreshes the cached next available slot column for all events where the
	 * next available slot and last slot columns are not in the past.
	 *
	 * We recommend running this every 5 - 15 minutes.
	 *
	 * ./craft bookings/refresh-next-available-slot [true]
	 *
	 * @param bool $includeNull - If true null next available slot columns will
	 *                          be included.
	 *
	 * @return int
	 */
	public function actionRefreshNextAvailableSlot (bool $includeNull = false)
	{
		Bookings::getInstance()->events->refreshNextAvailableSlot($includeNull);

		return ExitCode::OK;
	}

}