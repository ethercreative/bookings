<?php
/**
 * Bookings plugin for Craft CMS 3.x
 *
 * @link      https://ethercreative.co.uk
 * @copyright Copyright (c) Ether Creative
 */

namespace ether\bookings\base;

use craft\base\Model as BaseModel;

/**
 * Class Model
 *
 * @author  Ether Creative
 * @package ether\bookings\base
 * @since   1.0.0
 */
class Model extends BaseModel
{

	public function __construct (array $attributes = [], array $config = [])
	{
		foreach ($attributes as $key => $value)
			if (property_exists($this, $key))
				$this[$key] = $value;

		parent::__construct($config);
	}

}