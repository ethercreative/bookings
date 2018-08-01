<?php
/**
 * Bookings plugin for Craft CMS 3.x
 *
 * @link      https://ethercreative.co.uk
 * @copyright Copyright (c) Ether Creative
 */

namespace ether\bookings\models;


/**
 * Class ExceptionRule
 *
 * @author  Ether Creative
 * @package ether\bookings\models
 * @since   1.0.0
 */
class ExceptionRule extends RecursionRule
{

	// Properties
	// =========================================================================

	/** @var bool */
	public $bookable = false;

}