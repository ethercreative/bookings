<?php
/**
 * Bookings plugin for Craft CMS 3.x
 *
 * An advanced booking plugin for Craft CMS and Craft Commerce
 *
 * @link      https://ethercreative.co.uk
 * @copyright Copyright (c) 2018 Ether Creative
 */

/**
 * Bookings en Translation
 *
 * Returns an array with the string to be translated
 * (as passed to `Craft::t('bookings', '...')`) as
 * the key, and the translation as the value.
 *
 * @author    Ether Creative
 * @package   Bookings
 * @since     1.0.0-alpha.1
 */
return [
	'Bookings' => 'Bookings',
	'Bookable Event' => 'Bookable Event',
	'Bookable Ticket' => 'Bookable Ticket',

	'Capacity' => 'Capacity',
	'Max Quantity' => 'Max Quantity',

	'The max number of this type of ticket that can be sold per slot (or selected slot range if flexible)' => 'The max number of this type of ticket that can be sold per slot (or selected slot range if flexible)',
	'The maximum number of this type of ticket that can be booked per booking' => 'The maximum number of this type of ticket that can be booked per booking',

	'Booking' => 'Booking',
	'Reserved' => 'Reserved',
	'Completed' => 'Completed',
	'Expired' => 'Expired',

	'{attribute} is required.' => '{attribute} is required.',
	'{attribute} must not be set when Commerce is not installed.' => '{attribute} must not be set when Commerce is not installed.',
	'{attribute} must not be set when the element being booked is not a Commerce Purchasable.' => '{attribute} must not be set when the element being booked is not a Commerce Purchasable.',

	'Couldn\'t mark booking {number} as complete. Booking save failed during completion with errors: {errors}' => 'Couldn\'t mark booking {number} as complete. Booking save failed during completion with errors: {errors}',
	'Couldn\'t expire booking {number}. Booking save failed during expiration with errors: {errors}' => 'Couldn\'t expire booking {number}. Booking save failed during expiration with errors: {errors}',

	'A booking has expired.' => 'A booking has expired.',
	'Ticket ID input is invalid.' => 'Ticket ID input is invalid.',
	'Unable to find ticket for the given ID.' => 'Unable to find ticket for the given ID.',
	'Selected Date / Time is invalid.' => 'Selected Date / Time is invalid.',
	'Selected Date / Time is unavailable.' => 'Selected Date / Time is unavailable.',
	'Selected Date / Time is unavailable at that quantity.' => 'Selected Date / Time is unavailable at that quantity.',

	'Number' => 'Number',
	'ID' => 'ID',
	'Customer Email' => 'Customer Email',
	'Date Booked' => 'Date Booked',

	'All Bookings' => 'All Bookings',
];
