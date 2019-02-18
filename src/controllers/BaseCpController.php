<?php
/**
 * Bookings for Craft CMS
 *
 * @link      https://ethercreative.co.uk
 * @copyright Copyright (c) 2019 Ether Creative
 */

namespace ether\bookings\controllers;

use craft\web\Controller;

/**
 * Class BaseCpController
 *
 * @author  Ether Creative
 * @package ether\bookings\controllers
 */
class BaseCpController extends Controller
{

	/**
	 * @throws \yii\web\ForbiddenHttpException
	 */
	public function init ()
	{
		$this->requirePermission('accessPlugin-bookings');

		parent::init();
	}

}
