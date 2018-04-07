<?php
/**
 * Bookings plugin for Craft CMS 3.x
 *
 * @link      https://ethercreative.co.uk
 * @copyright Copyright (c) 2018 Ether Creative
 */

namespace ether\bookings\web\assets\ui;

use craft\web\AssetBundle;
use ether\bookings\web\assets\BaseAsset;

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
		include __DIR__ . '/dist/assets-manifest.php';

		$this->sourcePath = __DIR__ . '/dist';

		$this->depends = [
			BaseAsset::class,
		];

		$this->css = [
			\WebpackBuiltFiles::$cssFiles['app'],
		];
		$this->js = [
			\WebpackBuiltFiles::$jsFiles['vendor'],
			\WebpackBuiltFiles::$jsFiles['app'],
		];

		parent::init();
	}

}