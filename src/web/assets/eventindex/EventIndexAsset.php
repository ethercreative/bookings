<?php
/**
 * Bookings for Craft CMS
 *
 * @link      https://ethercreative.co.uk
 * @copyright Copyright (c) 2019 Ether Creative
 */

namespace ether\bookings\web\assets\eventindex;

use craft\web\AssetBundle;
use craft\web\View;
use ether\bookings\web\assets\bookingscp\BookingsCpAsset;

/**
 * Class EventIndexAsset
 *
 * @author  Ether Creative
 * @package ether\bookings\web\assets\eventindex
 */
class EventIndexAsset extends AssetBundle
{

	/**
	 * @inheritdoc
	 */
	public function init ()
	{
		$this->sourcePath = __DIR__ . '/dist';

		$this->depends = [
			BookingsCpAsset::class
		];

		$this->js = [
			'BookingsEventIndex.js',
		];

		parent::init();
	}


	/**
	 * @inheritdoc
	 */
	public function registerAssetFiles ($view)
	{
		parent::registerAssetFiles($view);

		if ($view instanceof View)
		{
			$view->registerTranslations('bookings', [
				'New {eventType} event',
				'New event',
			]);
		}
	}

}
