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
	'Bookable' => 'Bookable',
	'Bookable Variant' => 'Bookable Variant',
	'You don’t have permission to access to any of the Bookings sub-sections. Please contact an admin.' => 'You don’t have permission to access to any of the Bookings sub-sections. Please contact an admin.',

	'Bookings Settings' => 'Bookings Settings',
	'General' => 'General',
	'General Settings' => 'General Settings',

	'Couldn\'t mark booking {number} as complete. Booking save failed during completion with errors: {errors}' => 'Couldn\'t mark booking {number} as complete. Booking save failed during completion with errors: {errors}',
	'Couldn\'t expire booking {number} as complete. Booking deletion failed during expiration with errors: {errors}' => 'Couldn\'t expire booking {number} as complete. Booking deletion failed during expiration with errors: {errors}',

	'{attribute} must not be set when Commerce is not installed.' => '{attribute} must not be set when Commerce is not installed.',
	'{attribute} must not be set when the element being booked is not a Commerce Purchasable.' => '{attribute} must not be set when the element being booked is not a Commerce Purchasable.',
	'{attribute} is required.' => '{attribute} is required.',
];
