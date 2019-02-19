<?php
/**
 * Bookings for Craft CMS
 *
 * @link      https://ethercreative.co.uk
 * @copyright Copyright (c) 2019 Ether Creative
 */

namespace ether\bookings\web\assets\bookingscp;

use craft\web\AssetBundle;
use craft\web\assets\cp\CpAsset;
use yii\web\JqueryAsset;

/**
 * Class BaseBookingsAsset
 *
 * @author  Ether Creative
 * @package ether\bookings\web\assets
 */
class BookingsCpAsset extends AssetBundle
{

	/**
	 * @inheritdoc
	 */
	public function init ()
	{
		$this->sourcePath = __DIR__ . '/dist';

		$this->depends = [
			CpAsset::class,
			JqueryAsset::class,
		];

		$this->js = [
			'Bookings.js',
		];

		parent::init();
	}

}
