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
use ether\bookings\records\BookingRecord;


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
		$this->_createBookingsTable();
	}

	public function safeDown ()
	{
		$this->dropTableIfExists(BookableRecord::$tableName);
		$this->dropTableIfExists(BookingRecord::$tableName);
	}

	// Private Methods
	// =========================================================================

	private function _createBookablesTable ()
	{
		if ($this->db->tableExists(BookableRecord::$tableName))
			return;

		$this->createTable(
			BookableRecord::$tableName,
			[
				'id'          => $this->primaryKey(),
				'ownerId'     => $this->integer()->notNull(),
				'fieldId'     => $this->integer()->notNull(),

				'enabled'     => $this->boolean()->notNull(),
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

		// FKs

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

	private function _createBookingsTable ()
	{
		if ($this->db->tableExists(BookingRecord::$tableName))
			return;

		$this->createTable(
			BookingRecord::$tableName,
			[
				'id' => $this->primaryKey(),

				'status'            => $this->integer(1)->notNull(),
				'number'            => $this->string(32)->notNull(),
				'fieldId'           => $this->integer()->notNull(),
				'elementId'         => $this->integer()->notNull(),
				'subElementId'      => $this->integer(),
				'userId'            => $this->integer(),
				'lineItemId'        => $this->integer(),
				'orderId'           => $this->integer(),
				'customerId'        => $this->integer(),
				'customerEmail'     => $this->string()->notNull(),
				'slotStart'         => $this->dateTime()->notNull(),
				'slotEnd'           => $this->dateTime(),
				'dateBooked'        => $this->dateTime(),
				'reservationExpiry' => $this->dateTime(),

				'dateCreated' => $this->dateTime()->notNull(),
				'dateUpdated' => $this->dateTime()->notNull(),
				'uid'         => $this->uid()->notNull(),
			]
		);

		// Indexes

		$this->createIndex(
			null,
			BookingRecord::$tableName,
			['fieldId', 'elementId', 'subElementId', 'slotStart', 'reservationExpiry'],
			true
		);

		// FKs

		// NOTE: Commerce related FKs are managed in OnCommerceInstall

		$this->addForeignKey(
			$this->db->getForeignKeyName(BookingRecord::$tableName, 'id'),
			BookingRecord::$tableName,
			'id',
			'{{%elements}}',
			'id',
			'CASCADE',
			null
		);

		$this->addForeignKey(
			$this->db->getForeignKeyName(BookingRecord::$tableName, 'fieldId'),
			BookingRecord::$tableName,
			'fieldId',
			'{{%fields}}',
			'id',
			'CASCADE',
			null
		);

		$this->addForeignKey(
			$this->db->getForeignKeyName(BookingRecord::$tableName, 'userId'),
			BookingRecord::$tableName,
			'userId',
			'{{%users}}',
			'id',
			'CASCADE',
			null
		);

		$this->addForeignKey(
			$this->db->getForeignKeyName(BookingRecord::$tableName, 'elementId'),
			BookingRecord::$tableName,
			'elementId',
			'{{%elements}}',
			'id',
			'CASCADE',
			null
		);

		$this->addForeignKey(
			$this->db->getForeignKeyName(BookingRecord::$tableName, 'subElementId'),
			BookingRecord::$tableName,
			'subElementId',
			'{{%elements}}',
			'id',
			'CASCADE',
			null
		);

	}

}