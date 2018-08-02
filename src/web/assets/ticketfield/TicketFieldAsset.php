<?php
/**
 * Bookings plugin for Craft CMS 3.x
 *
 * @link      https://ethercreative.co.uk
 * @copyright Copyright (c) Ether Creative
 */

namespace ether\bookings\web\assets\ticketfield;

use craft\web\AssetBundle;


/**
 * Class TicketFieldAsset
 *
 * @author  Ether Creative
 * @package ether\bookings\web\assets\ticketfield
 * @since   1.0.0
 */
class TicketFieldAsset extends AssetBundle
{

	public function init ()
	{
		$this->sourcePath = __DIR__ . '/dist';

		$this->css = ['ticketfield.css'];

		parent::init();
	}

}