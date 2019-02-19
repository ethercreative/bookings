<?php
/**
 * Bookings for Craft CMS
 *
 * @link      https://ethercreative.co.uk
 * @copyright Copyright (c) 2019 Ether Creative
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

	// Element Types
	// =========================================================================

	// Element Types: Event
	// -------------------------------------------------------------------------

	'Event' => 'Event',
	'Events' => 'Events',

	'All Events' => 'All Events',
	'Event Types' => 'Event Types',

	'Events restored.' => 'Events restored.',
	'Events partially restored.' => 'Events partially restored.',
	'Events not restored.' => 'Events not restored.',

	'Are you sure you want to delete the selected event?' =>
		'Are you sure you want to delete the selected event?',
	'Events deleted.' => 'Events deleted.',

	// Statuses
	// =========================================================================

	'Live'     => 'Live',
	'Pending'  => 'Pending',
	'Expired'  => 'Expired',
	'Full'     => 'Full',
	'Disabled' => 'Disabled',

	// Permissions
	// =========================================================================

	'Manage Bookings'  => 'Manage Bookings',
	'Manage Events'    => 'Manage Events',
	'Manage Tickets'   => 'Manage Tickets',
	'Manage Resources' => 'Manage Resources',

	'Manage “{type}” events' => 'Manage “{type}” events',

	// Settings
	// =========================================================================

	'Bookings Settings' => 'Bookings Settings',
	'Settings' => 'Settings',

	'Delete' => 'Delete',

	// Settings: Event Types
	// -------------------------------------------------------------------------

	'No event types exist yet.' => 'No event types exist yet.',

	'New event type'       => 'New event type',
	'Create an Event Type' => 'Create an Event Type',

	'Event Fields' => 'Event Fields',

	'Are you sure you want to delete “{name}” and all its events? Please make sure you have a backup of your database before performing this destructive action.' =>
		'Are you sure you want to delete “{name}” and all its events? Please make sure you have a backup of your database before performing this destructive action.',

	'Name' => 'Name',
	'What this event type will be called in the CP.' =>
		'What this event type will be called in the CP.',

	'Handle' => 'Handle',
	'How you\'ll refer to this event type in the templates.' =>
		'How you\'ll refer to this event type in the templates.',

	'Enable versioning for events of this type?' =>
		'Enable versioning for events of this type?',

	'Show the Title field' => 'Show the Title field',

	'Title Field Label' => 'Title Field Label',
	'What do you want the Title field to be called?' =>
		'What do you want the Title field to be called?',

	'Title Format' => 'Title Format',
	'What the auto-generated entry titles should look like. You can include tags that output entry properties, such as `{myCustomField}`.' =>
		'What the auto-generated entry titles should look like. You can include tags that output entry properties, such as `{myCustomField}`.',

	'Site Settings' => 'Site Settings',
	'Configure the event type\'s site-specific settings.' =>
		'Configure the event type\'s site-specific settings.',

	'Event Type URI Format' => 'Event Type URI Format',
	'What the Event Type URIs should look like for this site.' =>
		'What the Event Type URIs should look like for this site.',
	'Leave blank if this Event Type doesn\'t have URLs' =>
		'Leave blank if this Event Type doesn\'t have URLs',

	'Template' => 'Template',
	'Which template should be loaded when an event\'s URL is requested' =>
		'Which template should be loaded when an event\'s URL is requested',

	'Default Status' => 'Default Status',

	'Propagate Events' => 'Propagate Events',
	'Propagate events across all enabled sites?' =>
		'Propagate events across all enabled sites?',
	'Whether events should be propagated across all the sites. If this is disabled, each event will only belong to the site it was created in.' =>
		'Whether events should be propagated across all the sites. If this is disabled, each event will only belong to the site it was created in.',

	'Event type saved.' => 'Event type saved.',
	'Couldn\'t save event type.' => 'Couldn\'t save event type.',

	// Events
	// =========================================================================

	'Resaving {type} events' => 'Resaving {type} events',
	'Resaving {type} events ({site})' => 'Resaving {type} events ({site})',

	'New event' => 'New event',
	'New {eventType} event' => 'New {eventType} event',

	// Errors
	// =========================================================================

	'You don’t have permission to access to any of the Bookings sub-sections. Please contact an admin.' =>
		'You don’t have permission to access to any of the Bookings sub-sections. Please contact an admin.',

	'This action is not allowed for the current user.' =>
		'This action is not allowed for the current user.',

	// Misc
	// =========================================================================

	'Title'        => 'Title',
	'Type'         => 'Type',
	'Slug'         => 'Slug',
	'URI'          => 'URI',
	'Author'       => 'Author',
	'Post Date'    => 'Post Date',
	'Expiry Date'  => 'Expiry Date',
	'Link'         => 'Link',
	'Date Created' => 'Date Created',
	'Date Updated' => 'Date Updated',

];
