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
	'Couldn\'t expire booking {number}. Booking save failed during expiration with errors: {errors}' => 'Couldn\'t expire booking {number}. Booking save failed during expiration with errors: {errors}',

	'{attribute} must not be set when Commerce is not installed.' => '{attribute} must not be set when Commerce is not installed.',
	'{attribute} must not be set when the element being booked is not a Commerce Purchasable.' => '{attribute} must not be set when the element being booked is not a Commerce Purchasable.',
	'{attribute} is required.' => '{attribute} is required.',
	'{attribute} is not a valid occurrence.' => '{attribute} is not a valid occurrence.',
	'{attribute} is required for flexible duration bookings.' => '{attribute} is required for flexible duration bookings.',
	'{attribute} is not allowed for fixed duration bookings.' => '{attribute} is not allowed for fixed duration bookings.',
	'Unable to verify {attribute} availability' => 'Unable to verify {attribute} availability',
	'Slot Start is unavailable.' => 'Slot Start is unavailable.',
	'Slot End must occur after Slot Start.' => 'Slot End must occur after Slot Start.',
	'Slot End is unavailable.' => 'Slot End is unavailable.',
	'The selected slot range is unavailable.' => 'The selected slot range is unavailable.',
];
