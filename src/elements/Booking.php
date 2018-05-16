<?php
/**
 * Bookings plugin for Craft CMS 3.x
 *
 * @link      https://ethercreative.co.uk
 * @copyright Copyright (c) Ether Creative
 */

namespace ether\bookings\elements;

use craft\base\Element;


/**
 * Class Booking
 *
 * @author  Ether Creative
 * @package ether\bookings\elements
 * @since   1.0.0
 */
class Booking extends Element
{

	// Properties
	// =========================================================================

	// TODO: Finish adding properties to this then add them to the Install migration

	// TODO: Do we want our own Commerce-esque customer system, or should we just use JSON?

	/** @inheritdoc */
	public $id;

	/** @var string - A unique identifier for this booking */
	public $number;

	/** @var int - The field this booking is bound to */
	public $fieldId;

	/** @var int - The element this booking is bound to */
	public $elementId;

	/** @var int - The order this booking belongs to (if Commerce is used) */
	public $orderId;

	/** @var \DateTime - The slot that was booked */
	public $slot;

	/** @var \DateTime - The time the booking was placed */
	public $dateBooked;

	// Public Methods
	// =========================================================================

	/**
	 * @return null|string
	 */
	public static function displayName(): string
	{
		return \Craft::t('bookings', 'Bookings');
	}

	/**
	 * @inheritdoc
	 */
	public function __toString()
	{
		return $this->getShortNumber();
	}

	// Helpers
	// =========================================================================

	/**
	 * @return string
	 */
	public function getShortNumber(): string
	{
		return substr($this->number, 0, 7);
	}

}