<?php
/**
 * Bookings for Craft CMS
 *
 * @link      https://ethercreative.co.uk
 * @copyright Copyright (c) 2018 Ether Creative
 */

namespace ether\bookings\web\assets\cp;

use craft\web\AssetBundle;
use craft\web\assets\cp\CpAsset;

/**
 * Class BookingsCpAsset
 *
 * @author  Ether Creative
 * @package ether\bookings\web\assets\cp
 */
class BookingsCpAsset extends AssetBundle
{

	public function init ()
	{
		$this->sourcePath = __DIR__ . '/dist';

		$this->depends = [
			CpAsset::class,
		];

		/** @noinspection PhpComposerExtensionStubsInspection */
		$manifest = json_decode(
			file_get_contents(__DIR__ . '/dist/manifest.json'),
			true
		);

		$this->css = [
			$manifest['cp.less'],
		];

		parent::init();
	}

}