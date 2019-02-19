<?php
/**
 * Bookings for Craft CMS
 *
 * @link      https://ethercreative.co.uk
 * @copyright Copyright (c) 2019 Ether Creative
 */

namespace ether\bookings\records;

use craft\db\ActiveRecord;

/**
 * Class Ticket
 *
 * @author  Ether Creative
 * @package ether\bookings\records
 */
class Ticket extends ActiveRecord
{

	// Consts
	// =========================================================================

	const TableName = '{{%bookings_tickets}}';
	const TableNameClean = 'bookings_tickets';

	// Methods
	// =========================================================================

	public static function tableName ()
	{
		return self::TableName;
	}

}
