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
use ether\bookings\records\Bookable;

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
		$this->bookablesTableUp();
	}

	public function safeDown ()
	{
		$this->bookablesTableDown();
	}

	// Tables
	// =========================================================================

	// Bookings
	// -------------------------------------------------------------------------

	private function bookablesTableUp ()
	{
		if ($this->db->tableExists(Bookable::TABLE_NAME))
			return;

		$this->createTable(Bookable::TABLE_NAME, [
			'id' => $this->integer()->notNull(),

			// TODO: Add necessary columns

			'dateCreated' => $this->dateTime()->notNull(),
			'dateUpdated' => $this->dateTime()->notNull(),
			'uid'         => $this->uid(),
			'PRIMARY KEY(id)',
		]);

		$this->addForeignKey(
			$this->db->getForeignKeyName(Bookable::TABLE_NAME, 'id'),
			Bookable::TABLE_NAME,
			'id',
			'{{%elements}}',
			'id',
			'CASCADE',
			null
		);
	}

	private function bookablesTableDown ()
	{
		if (!$this->db->tableExists(Bookable::TABLE_NAME))
			return;

		$this->dropForeignKey(
			$this->db->getForeignKeyName(Bookable::TABLE_NAME, 'id'),
			Bookable::TABLE_NAME
		);

		$this->dropTable(Bookable::TABLE_NAME);
	}

}