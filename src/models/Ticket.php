<?php
/**
 * Bookings plugin for Craft CMS 3.x
 *
 * @link      https://ethercreative.co.uk
 * @copyright Copyright (c) Ether Creative
 */

namespace ether\bookings\models;

use ether\bookings\base\Model;


/**
 * Class Ticket
 *
 * @author  Ether Creative
 * @package ether\bookings\models
 * @since   1.0.0
 */
class Ticket extends Model
{

	// Properties
	// =========================================================================

	/** @var int */
	public $id;

	/** @var int */
	public $eventId;

	/** @var int */
	public $elementId;

	/** @var int */
	public $fieldId;

	/**
	 * @var int - The max number of this type of ticket that can be sold per
	 * slot (or selected slot range if flexible)
	 */
	public $capacity = 1;

	/**
	 * @var int - The maximum number of this type of ticket that can be booked
	 * per booking
	 */
	public $maxQty;

	// Methods
	// =========================================================================

	public function rules ()
	{
		$rules = parent::rules();

		$rules[] = [
			['capacity', 'maxQty'],
			'number'
		];

		return $rules;
	}

}