<?php
/**
 * Bookings plugin for Craft CMS 3.x
 *
 * @link      https://ethercreative.co.uk
 * @copyright Copyright (c) 2018 Ether Creative
 */

namespace ether\bookings\web\assets\ui;

use craft\web\AssetBundle;
use craft\web\assets\cp\CpAsset;

/**
 * Class UIAsset
 *
 * @author   Ether Creative
 * @package  ether\bookings\web\assets\ui
 * @since    1.0.0
 */
class UIAsset extends AssetBundle
{

	public function init ()
	{
		$this->sourcePath = __DIR__ . '/dist';

		$this->depends = [
			CpAsset::class,
		];

		$this->js = ['bookings.js'];
		$this->css = ['bookings.css'];

		parent::init();
	}

}