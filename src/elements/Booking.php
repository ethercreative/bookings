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

	// TODO: Is this the bookings field or the bookable field (if we're doing that)?

	/** @var int - The element this booking is bound to (i.e. entry, product, etc.) */
	public $elementId;

	/** @var int - The order this booking belongs to (if Commerce is used) */
	public $orderId;

	/** @var int - The customer this booking belongs to (if Commerce is used) */
	public $customerId;

	/** @var string - The customers email (always required) */
	public $customerEmail;

	// TODO: Custom fields (like an order)

	/** @var \DateTime - The slot that was booked */
	public $slotStart;

	/** @var \DateTime - The end slot that was booked (if booking is flexible) */
	public $slotEnd;

	/** @var \DateTime - The time the booking was placed */
	public $dateBooked;

	/** @var \DateTime|null - The time this booking reservation will expire (if null, this is a complete booking) */
	public $reservationExpiry = null;

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