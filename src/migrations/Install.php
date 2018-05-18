<?php
/**
 * Bookings plugin for Craft CMS 3.x
 *
 * @link      https://ethercreative.co.uk
 * @copyright Copyright (c) Ether Creative
 */

namespace ether\bookings\migrations;

use craft\db\Migration;
use craft\records\FieldLayout;
use ether\bookings\elements\Booking;
use ether\bookings\records\BookableRecord;
use ether\bookings\records\BookingRecord;
use ether\bookings\records\BookingSettingsRecord;


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

		$this->_createBookingSettingsTable();
	}

	public function safeDown ()
	{
		$this->dropTableIfExists(BookableRecord::$tableName);
		$this->dropTableIfExists(BookingRecord::$tableName);
		$this->dropTableIfExists(BookingSettingsRecord::$tableName);
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

	private function _createBookingsTable ()
	{
		if ($this->db->tableExists(BookingRecord::$tableName))
			return;

		$this->createTable(
			BookingRecord::$tableName,
			[
				'id' => $this->primaryKey(),

				// TODO: ...

				'dateCreated' => $this->dateTime()->notNull(),
				'dateUpdated' => $this->dateTime()->notNull(),
				'uid'         => $this->uid()->notNull(),
			]
		);

		// FKs

		// TODO: Create a separate file/method that will perform the migrations
		// to link the bookings to Commerce orders (so it can be used on install
		// of Bookings or Commerce).

		$this->addForeignKey(
			$this->db->getForeignKeyName(BookingRecord::$tableName, 'id'),
			BookingRecord::$tableName,
			'id',
			'{{%elements}}',
			'id',
			'CASCADE',
			null
		);

	}

	private function _createBookingSettingsTable ()
	{
		if ($this->db->tableExists(BookingSettingsRecord::$tableName))
			return;

		$this->createTable(
			BookingSettingsRecord::$tableName,
			[
				'id' => $this->primaryKey(),

				'name'          => $this->string()->notNull(),
				'handle'        => $this->string()->notNull(),
				'fieldLayoutId' => $this->integer(),

				'dateCreated' => $this->dateTime()->notNull(),
				'dateUpdated' => $this->dateTime()->notNull(),
				'uid'         => $this->uid(),
			]
		);

		// Indexes

		$this->createIndex(
			null,
			BookingSettingsRecord::$tableName,
			'handle',
			true
		);

		$this->createIndex(
			null,
			BookingSettingsRecord::$tableName,
			'fieldLayoutId',
			false
		);

		// FKs

		$this->addForeignKey(
			null,
			BookingSettingsRecord::$tableName,
			['fieldLayoutId'],
			FieldLayout::tableName(),
			['id'],
			'SET NULL'
		);

		// Pre-populate default data

		$this->insert(
			FieldLayout::tableName(),
			['type' => Booking::class]
		);
		$data = [
			'name' => 'Default Booking',
			'handle' => 'defaultBooking',
			'fieldLayoutId' => $this->db->getLastInsertID(FieldLayout::tableName())
		];
		$this->insert(BookingSettingsRecord::tableName(), $data);

		// TODO: Add additional booking settings for each booking field created?

	}

}