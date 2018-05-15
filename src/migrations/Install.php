<?php
/**
 * Bookings plugin for Craft CMS 3.x
 *
 * @link      https://ethercreative.co.uk
 * @copyright Copyright (c) Ether Creative
 */

namespace ether\bookings\migrations;

use craft\db\Migration;
use ether\bookings\records\BookableRecord;


/**
 * Class Install
 *
 * @author  Ether Creative
 * @package ether\bookings\migrations
 * @since   1.0.0
 */
class Install extends Migration
{

	public function safeUp ()
	{
		$this->_createBookablesTable();
	}

	public function safeDown ()
	{
		$this->dropTableIfExists(BookableRecord::$tableName);
	}

	// Private Methods
	// =========================================================================

	private function _createBookablesTable ()
	{
		$this->createTable(
			BookableRecord::$tableName,
			[
				'id'          => $this->primaryKey(),
				'ownerId'     => $this->integer()->notNull(),
				'fieldId'     => $this->integer()->notNull(),

				'settings'    => $this->text()->notNull(),

				'dateCreated' => $this->dateTime()->notNull(),
				'dateUpdated' => $this->dateTime()->notNull(),
				'uid'         => $this->uid()->notNull(),
			]
		);

		// Indexes

		$this->createIndex(
			null,
			BookableRecord::$tableName,
			['ownerId', 'fieldId'],
			true
		);

		// Relations

		$this->addForeignKey(
			null,
			BookableRecord::$tableName,
			['ownerId'],
			'{{%elements}}',
			['id'],
			'CASCADE',
			null
		);

		$this->addForeignKey(
			null,
			BookableRecord::$tableName,
			['fieldId'],
			'{{%fields}}',
			['id'],
			'CASCADE',
			'CASCADE'
		);
	}

}