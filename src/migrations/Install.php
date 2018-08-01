<?php
/**
 * Bookings plugin for Craft CMS 3.x
 *
 * @link      https://ethercreative.co.uk
 * @copyright Copyright (c) Ether Creative
 */

namespace ether\bookings\migrations;

use craft\db\Migration;
use craft\helpers\MigrationHelper;
use ether\bookings\records\BookedEventRecord;
use ether\bookings\records\BookedSlotRecord;
use ether\bookings\records\BookedTicketRecord;
use ether\bookings\records\BookingRecord;
use ether\bookings\records\EventRecord;
use ether\bookings\records\TicketFieldSettingsRecord;
use ether\bookings\records\TicketRecord;


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
		$this->_createTicketFieldsSettingsTable();
		$this->_createEventsTable();
		$this->_createTicketsTable();
		$this->_createBookingsTable();
		$this->_createBookedEventsTable();
		$this->_createBookedTicketsTable();
		$this->_createBookedSlotsTable();
	}

	public function safeDown ()
	{
		MigrationHelper::dropAllForeignKeysOnTable(TicketFieldSettingsRecord::$tableName);
		MigrationHelper::dropAllForeignKeysOnTable(EventRecord::$tableName);
		MigrationHelper::dropAllForeignKeysOnTable(TicketRecord::$tableName);
		MigrationHelper::dropAllForeignKeysOnTable(BookingRecord::$tableName);
		MigrationHelper::dropAllForeignKeysOnTable(BookedEventRecord::$tableName);
		MigrationHelper::dropAllForeignKeysOnTable(BookedTicketRecord::$tableName);
		MigrationHelper::dropAllForeignKeysOnTable(BookedSlotRecord::$tableName);

		$this->dropTableIfExists(TicketFieldSettingsRecord::$tableName);
		$this->dropTableIfExists(EventRecord::$tableName);
		$this->dropTableIfExists(TicketRecord::$tableName);
		$this->dropTableIfExists(BookingRecord::$tableName);
		$this->dropTableIfExists(BookedEventRecord::$tableName);
		$this->dropTableIfExists(BookedTicketRecord::$tableName);
		$this->dropTableIfExists(BookedSlotRecord::$tableName);
	}

	// Tables
	// =========================================================================

	// Ticket Fields Settings
	// -------------------------------------------------------------------------

	private function _createTicketFieldsSettingsTable ()
	{
		$this->createTable(TicketFieldSettingsRecord::$tableName, [
			'id'            => $this->primaryKey(),
			'fieldId'       => $this->integer()->notNull(),
			'fieldLayoutId' => $this->integer()->notNull(),

			'dateCreated' => $this->dateTime()->notNull(),
			'dateUpdated' => $this->dateTime()->notNull(),
			'uid'         => $this->uid(),
		]);

		$this->createIndex(
			null,
			TicketFieldSettingsRecord::$tableName,
			'fieldId',
			true
		);

		$this->createIndex(
			null,
			TicketFieldSettingsRecord::$tableName,
			'fieldLayoutId',
			true
		);

		$this->addForeignKey(
			null,
			TicketFieldSettingsRecord::$tableName,
			'fieldId',
			'{{%fields}}',
			'id',
			'CASCADE',
			null
		);

		$this->addForeignKey(
			null,
			TicketFieldSettingsRecord::$tableName,
			'fieldLayoutId',
			'{{%fieldlayouts}}',
			'id',
			'CASCADE',
			null
		);
	}

	// Events
	// -------------------------------------------------------------------------

	private function _createEventsTable ()
	{
		$this->createTable(EventRecord::$tableName, [
			'id'        => $this->primaryKey(),
			'elementId' => $this->integer()->notNull(),
			'fieldId'   => $this->integer()->notNull(),
			'enabled'   => $this->boolean()->defaultValue(true),
			'settings'  => $this->json()->notNull(),

			'dateCreated' => $this->dateTime()->notNull(),
			'dateUpdated' => $this->dateTime()->notNull(),
			'uid'         => $this->uid(),
		]);

		$this->createIndex(
			null,
			EventRecord::$tableName,
			'elementId',
			false
		);

		$this->createIndex(
			null,
			EventRecord::$tableName,
			'fieldId',
			false
		);

		$this->addForeignKey(
			null,
			EventRecord::$tableName,
			'elementId',
			'{{%elements}}',
			'id',
			'CASCADE',
			null
		);

		$this->addForeignKey(
			null,
			EventRecord::$tableName,
			'fieldId',
			'{{%fields}}',
			'id',
			'CASCADE',
			null
		);
	}

	// Tickets
	// -------------------------------------------------------------------------

	private function _createTicketsTable ()
	{
		$this->createTable(TicketRecord::$tableName, [
			'id'        => $this->primaryKey(),
			'eventId'   => $this->integer()->notNull(),
			'elementId' => $this->integer()->notNull(),
			'fieldId'   => $this->integer()->notNull(),
			'settings'  => $this->json()->notNull(),

			'dateCreated' => $this->dateTime()->notNull(),
			'dateUpdated' => $this->dateTime()->notNull(),
			'uid'         => $this->uid(),
		]);

		$this->createIndex(
			null,
			TicketRecord::$tableName,
			'eventId',
			false
		);

		$this->createIndex(
			null,
			TicketRecord::$tableName,
			'elementId',
			false
		);

		$this->createIndex(
			null,
			TicketRecord::$tableName,
			'fieldId',
			false
		);

		$this->addForeignKey(
			null,
			TicketRecord::$tableName,
			'eventId',
			EventRecord::$tableName,
			'id',
			'CASCADE',
			null
		);

		$this->addForeignKey(
			null,
			TicketRecord::$tableName,
			'elementId',
			'{{%elements}}',
			'id',
			'CASCADE',
			null
		);

		$this->addForeignKey(
			null,
			TicketRecord::$tableName,
			'fieldId',
			'{{%fields}}',
			'id',
			'CASCADE',
			null
		);
	}

	// Bookings
	// -------------------------------------------------------------------------

	private function _createBookingsTable ()
	{
		$this->createTable(BookingRecord::$tableName, [
			'id'                => $this->primaryKey(),
			'status'            => $this->integer(1)->notNull(),
			'number'            => $this->string(32)->notNull(),
			'userId'            => $this->integer()->null(),
			'lineItemId'        => $this->integer()->null(),
			'orderId'           => $this->integer()->null(),
			'customerId'        => $this->integer()->null(),
			'customerEmail'     => $this->string()->null(),
			'dateBooked'        => $this->dateTime()->notNull(),
			'reservationExpiry' => $this->dateTime()->null(),

			'dateCreated' => $this->dateTime()->notNull(),
			'dateUpdated' => $this->dateTime()->notNull(),
			'uid'         => $this->uid(),
		]);

		$this->createIndex(
			null,
			BookingRecord::$tableName,
			'userId',
			false
		);

		// TODO: Should this be unique?
		$this->createIndex(
			null,
			BookingRecord::$tableName,
			'lineItemId',
			false
		);

		$this->createIndex(
			null,
			BookingRecord::$tableName,
			'orderId',
			false
		);

		$this->createIndex(
			null,
			BookingRecord::$tableName,
			'customerId',
			false
		);

		$this->addForeignKey(
			null,
			BookingRecord::$tableName,
			'userId',
			'{{%users}}',
			'id',
			'CASCADE',
			null
		);

		// Commerce foreign keys are added / removed via
		// `integrations/commerce/OnCommerce(Uni|I)nstall.php`

	}

	// Booked Events
	// -------------------------------------------------------------------------

	private function _createBookedEventsTable ()
	{
		$this->createTable(BookedEventRecord::$tableName, [
			'id'        => $this->primaryKey(),
			'bookingId' => $this->integer()->notNull(),
			'eventId'   => $this->integer()->notNull(),

			'dateCreated' => $this->dateTime()->notNull(),
			'dateUpdated' => $this->dateTime()->notNull(),
			'uid'         => $this->uid(),
		]);

		$this->createIndex(
			null,
			BookedEventRecord::$tableName,
			'bookingId',
			false
		);

		$this->createIndex(
			null,
			BookedEventRecord::$tableName,
			'eventId',
			false
		);

		$this->addForeignKey(
			null,
			BookedEventRecord::$tableName,
			'bookingId',
			BookingRecord::$tableName,
			'id',
			'CASCADE',
			null
		);

		$this->addForeignKey(
			null,
			BookedEventRecord::$tableName,
			'eventId',
			EventRecord::$tableName,
			'id',
			'CASCADE',
			null
		);
	}

	// Booked Events
	// -------------------------------------------------------------------------

	private function _createBookedTicketsTable ()
	{
		$this->createTable(BookedTicketRecord::$tableName, [
			'id'        => $this->primaryKey(),
			'bookingId' => $this->integer()->notNull(),
			'tickedId'  => $this->integer()->notNull(),

			'dateCreated' => $this->dateTime()->notNull(),
			'dateUpdated' => $this->dateTime()->notNull(),
			'uid'         => $this->uid(),
		]);

		$this->createIndex(
			null,
			BookedTicketRecord::$tableName,
			'bookingId',
			false
		);

		$this->createIndex(
			null,
			BookedTicketRecord::$tableName,
			'tickedId',
			false
		);

		$this->addForeignKey(
			null,
			BookedTicketRecord::$tableName,
			'bookingId',
			BookingRecord::$tableName,
			'id',
			'CASCADE',
			null
		);

		$this->addForeignKey(
			null,
			BookedTicketRecord::$tableName,
			'tickedId',
			TicketRecord::$tableName,
			'id',
			'CASCADE',
			null
		);
	}

	// Booked Slots
	// -------------------------------------------------------------------------

	private function _createBookedSlotsTable ()
	{
		$this->createTable(BookedSlotRecord::$tableName, [
			'id'        => $this->primaryKey(),
			'start'     => $this->boolean()->notNull(),
			'end'       => $this->boolean()->notNull(),
			'bookingId' => $this->integer()->notNull(),
			'eventId'   => $this->integer()->notNull(),
			'ticketId'  => $this->integer()->notNull(),
			'date'      => $this->dateTime()->notNull(),

			'dateCreated' => $this->dateTime()->notNull(),
			'dateUpdated' => $this->dateTime()->notNull(),
			'uid'         => $this->uid(),
		]);

		$this->createIndex(
			null,
			BookedSlotRecord::$tableName,
			'bookingId',
			false
		);

		$this->createIndex(
			null,
			BookedSlotRecord::$tableName,
			'eventId',
			false
		);

		$this->createIndex(
			null,
			BookedSlotRecord::$tableName,
			'ticketId',
			false
		);

		$this->addForeignKey(
			null,
			BookedSlotRecord::$tableName,
			'bookingId',
			BookingRecord::$tableName,
			'id',
			'CASCADE',
			null
		);

		$this->addForeignKey(
			null,
			BookedSlotRecord::$tableName,
			'eventId',
			EventRecord::$tableName,
			'id',
			'CASCADE',
			null
		);

		$this->addForeignKey(
			null,
			BookedSlotRecord::$tableName,
			'ticketId',
			TicketRecord::$tableName,
			'id',
			'CASCADE',
			null
		);
	}

}