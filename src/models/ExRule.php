<?php
/**
 * Bookings plugin for Craft CMS 3.x
 *
 * @link      https://ethercreative.co.uk
 * @copyright Copyright (c) Ether Creative
 */

namespace ether\bookings\models;

/**
 * Class Exception
 *
 * @author  Ether Creative
 * @package ether\bookings\models
 * @since   1.0.0
 */
class ExRule extends RecursionRule
{

	// Properties
	// =========================================================================

	// Properties: Public
	// -------------------------------------------------------------------------

	/** @var bool */
	public $bookable = false;

}