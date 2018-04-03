<?php
/**
 * Bookings plugin for Craft CMS 3.x
 *
 * An advanced booking plugin for Craft CMS and Craft Commerce
 *
 * @link      https://ethercreative.co.uk
 * @copyright Copyright (c) 2018 Ether Creative
 */

namespace ether\bookings\migrations;

use craft\db\Migration;
use ether\bookings\elements\Booking;

/**
 * Class Install
 *
 * @author    Ether Creative
 * @package   ether\bookings\migrations
 * @since     1.0.0-alpha.1
 */
class Install extends Migration
{

	public function safeUp ()
	{
		$this->bookingsTableUp();
	}

	public function safeDown ()
	{
		$this->bookingsTableDown();
	}

	// Tables
	// =========================================================================

	// Bookings
	// -------------------------------------------------------------------------

	private function bookingsTableUp ()
	{
		if ($this->db->tableExists(Booking::$tableName))
			return;

		$this->createTable(Booking::$tableName, [
			'id' => $this->integer()->notNull(),

			// TODO: Add necessary columns

			'dateCreated' => $this->dateTime()->notNull(),
			'dateUpdated' => $this->dateTime()->notNull(),
			'uid'         => $this->uid(),
			'PRIMARY KEY(id)',
		]);

		$this->addForeignKey(
			$this->db->getForeignKeyName(Booking::$tableName, 'id'),
			Booking::$tableName,
			'id',
			'{{%elements}}',
			'id',
			'CASCADE',
			null
		);
	}

	private function bookingsTableDown ()
	{
		if (!$this->db->tableExists(Booking::$tableName))
			return;

		$this->dropForeignKey(
			$this->db->getForeignKeyName(Booking::$tableName, 'id'),
			Booking::$tableName
		);

		$this->dropTable(Booking::$tableName);
	}

}