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
use ether\bookings\enums\EventType;
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
		$this->_createEventsTable();
		$this->_createTicketsTable();
		$this->_createBookingsTable();
		$this->_createBookedTicketsTable();
		$this->_createBookedSlotsTable();
	}

	public function safeDown ()
	{
		MigrationHelper::dropAllForeignKeysOnTable(EventRecord::$tableName);
		MigrationHelper::dropAllForeignKeysOnTable(TicketRecord::$tableName);
		MigrationHelper::dropAllForeignKeysOnTable(BookingRecord::$tableName);
		MigrationHelper::dropAllForeignKeysOnTable(BookedTicketRecord::$tableName);
		MigrationHelper::dropAllForeignKeysOnTable(BookedSlotRecord::$tableName);

		$this->dropTableIfExists(EventRecord::$tableName);
		$this->dropTableIfExists(TicketRecord::$tableName);
		$this->dropTableIfExists(BookingRecord::$tableName);
		$this->dropTableIfExists(BookedTicketRecord::$tableName);
		$this->dropTableIfExists(BookedSlotRecord::$tableName);
	}

	// Tables
	// =========================================================================

	// Events
	// -------------------------------------------------------------------------

	private function _createEventsTable ()
	{
		$types = array_values(EventType::asArray());

		$this->createTable(EventRecord::$tableName, [
			'id'         => $this->primaryKey(),
			'elementId'  => $this->integer()->notNull(),
			'fieldId'    => $this->integer()->notNull(),
			'enabled'    => $this->boolean()->defaultValue(true),
			'type'       => $this->enum('type', $types)->notNull(),
			'capacity'   => $this->integer(),
			'multiplier' => $this->integer(),
			'baseRule'   => $this->json(),
			'exceptions' => $this->json(),

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
			'eventId'   => $this->integer(),
			'elementId' => $this->integer()->notNull(),
			'fieldId'   => $this->integer()->notNull(),
			'capacity'  => $this->integer(),
			'maxQty'    => $this->integer(),

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
			'eventId'           => $this->integer()->null(),
			'userId'            => $this->integer()->null(),
			'orderId'           => $this->integer()->null(),
			'customerId'        => $this->integer()->null(),
			'customerEmail'     => $this->string()->null(),
			'dateBooked'        => $this->dateTime()->null(),
			'reservationExpiry' => $this->dateTime()->null(),

			'dateCreated' => $this->dateTime()->notNull(),
			'dateUpdated' => $this->dateTime()->notNull(),
			'uid'         => $this->uid(),
		]);

		$this->createIndex(
			null,
			BookingRecord::$tableName,
			'eventId',
			false
		);

		$this->createIndex(
			null,
			BookingRecord::$tableName,
			'userId',
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
			'eventId',
			EventRecord::$tableName,
			'id',
			'CASCADE',
			null
		);

		$this->addForeignKey(
			null,
			BookingRecord::$tableName,
			'id',
			'{{%elements}}',
			'id',
			'CASCADE',
			null
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

	private function _createBookedTicketsTable ()
	{
		$this->createTable(BookedTicketRecord::$tableName, [
			'id'         => $this->primaryKey(),
			'ticketId'   => $this->integer()->notNull(),
			'bookingId'  => $this->integer()->notNull(),
			'lineItemId' => $this->integer()->null(),

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
			'ticketId',
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
			'ticketId',
			TicketRecord::$tableName,
			'id',
			'CASCADE',
			null
		);

		// Commerce foreign keys are added / removed via
		// `integrations/commerce/OnCommerce(Uni|I)nstall.php`
	}

	// Booked Slots
	// -------------------------------------------------------------------------

	private function _createBookedSlotsTable ()
	{
		$this->createTable(BookedSlotRecord::$tableName, [
			'id'             => $this->primaryKey(),
			'start'          => $this->boolean()->notNull(),
			'end'            => $this->boolean()->notNull(),
			'ticketId'       => $this->integer()->notNull(),
			'bookingId'      => $this->integer()->notNull(),
			'bookedTicketId' => $this->integer()->notNull(),
			'date'           => $this->dateTime()->notNull(),

			'dateCreated' => $this->dateTime()->notNull(),
			'dateUpdated' => $this->dateTime()->notNull(),
			'uid'         => $this->uid(),
		]);

		$this->createIndex(
			null,
			BookedSlotRecord::$tableName,
			'ticketId',
			false
		);

		$this->createIndex(
			null,
			BookedSlotRecord::$tableName,
			'bookingId',
			false
		);

		$this->createIndex(
			null,
			BookedSlotRecord::$tableName,
			'bookedTicketId',
			false
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
			'bookedTicketId',
			BookedTicketRecord::$tableName,
			'id',
			'CASCADE',
			null
		);
	}

}