<?php
/**
 * Bookings plugin for Craft CMS 3.x
 *
 * @link      https://ethercreative.co.uk
 * @copyright Copyright (c) Ether Creative
 */

namespace ether\bookings\models;

use craft\base\Model;
use RRule\RRule;

/**
 * Class Exception
 *
 * @author  Ether Creative
 * @package ether\bookings\models
 * @since   1.0.0
 */
class ExRule extends Model
{

	// Properties
	// =========================================================================

	// Properties: Public
	// -------------------------------------------------------------------------

	/** @var RRule */
	public $rrule;

	/** @var bool */
	public $bookable = false;

}