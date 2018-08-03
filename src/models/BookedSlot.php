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
 * Class BookedSlot
 *
 * @author  Ether Creative
 * @package ether\bookings\models
 * @since   1.0.0
 */
class BookedSlot extends Model
{

	// Properties
	// =========================================================================

	/** @var int */
	public $id;

	/** @var bool */
	public $start;

	/** @var bool */
	public $end = false;

	/** @var int */
	public $ticketId;

	/** @var int */
	public $bookingId;

	/** @var int */
	public $bookedTicketId;

	/** @var \DateTime */
	public $date;

}