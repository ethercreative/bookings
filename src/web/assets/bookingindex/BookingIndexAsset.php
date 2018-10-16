<?php
/**
 * Bookings plugin for Craft CMS 3.x
 *
 * @link      https://ethercreative.co.uk
 * @copyright Copyright (c) Ether Creative
 */

namespace ether\bookings\web\assets\bookingindex;

use craft\web\AssetBundle;
use craft\web\assets\cp\CpAsset;


/**
 * Class BookingIndexAsset
 *
 * @author  Ether Creative
 * @package ether\bookings\web\assets\bookingindex
 * @since   1.0.0
 */
class BookingIndexAsset extends AssetBundle
{

	public function init ()
	{
		$this->sourcePath = __DIR__ . '/dist';

		$this->depends = [
			CpAsset::class,
		];

		$manifest = json_decode(
			file_get_contents(__DIR__ . '/dist/manifest.json'),
			true
		);

		$this->js = [
			$manifest['BookingsIndex.js'],
		];

		parent::init();
	}

}