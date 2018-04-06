<?php
/**
 * Bookings plugin for Craft CMS 3.x
 *
 * @link      https://ethercreative.co.uk
 * @copyright Copyright (c) 2018 Ether Creative
 */

namespace ether\bookings\web\assets;

use craft\web\AssetBundle;
use craft\web\assets\cp\CpAsset;

/**
 * Class BaseAsset
 *
 * @author   Ether Creative
 * @package  ether\bookings\web\assets
 * @since    1.0.0
 */
class BaseAsset extends AssetBundle
{

	public function init ()
	{
		$this->sourcePath = '@lib';

		$this->depends = [
			CpAsset::class,
		];

		$this->js = [
			'vue/vue.min.js',
		];

		parent::init();
	}

}