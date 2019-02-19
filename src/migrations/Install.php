<?php
/**
 * Bookings for Craft CMS
 *
 * @link      https://ethercreative.co.uk
 * @copyright Copyright (c) 2019 Ether Creative
 */

namespace ether\bookings\migrations;

use craft\db\Migration;
use craft\db\Table;
use craft\helpers\MigrationHelper;
use ether\bookings\records\Event;
use ether\bookings\records\EventType;
use ether\bookings\records\EventTypeSite;
use ether\bookings\records\Ticket;

/**
 * Class Install
 *
 * @author  Ether Creative
 * @package ether\bookings\migrations
 */
class Install extends Migration
{

	/**
	 * @inheritdoc
	 */
	public function safeUp ()
	{
		$this->_createEventTables();
	}

	/**
	 * @inheritdoc
	 * @throws \yii\base\NotSupportedException
	 */
	public function safeDown ()
	{
		$this->_dropEventTables();

		$this->delete(
			'{{%elementindexsettings}}',
			['type' => [Event::class]]
		);

		\Craft::$app->projectConfig->remove('bookings');

		return true;
	}

	// Events
	// =========================================================================

	/**
	 * Create the tables for the Event element type
	 */
	private function _createEventTables ()
	{
		// Tables
		// ---------------------------------------------------------------------

		$this->createTable(Event::TableName, [
			'id'              => $this->primaryKey(),
			'typeId'          => $this->integer()->notNull(),
			'authorId'        => $this->integer(),
			'postDate'        => $this->dateTime(),
			'expiryDate'      => $this->dateTime(),
			'deletedWithType' => $this->boolean()->null(),
			'dateCreated'     => $this->dateTime()->notNull(),
			'dateUpdated'     => $this->dateTime()->notNull(),
			'uid'             => $this->uid(),
		]);

		$this->createTable(EventType::TableName, [
			'id'               => $this->primaryKey(),
			'fieldLayoutId'    => $this->integer(),
			'name'             => $this->string()->notNull(),
			'handle'           => $this->string()->notNull(),
			'enableVersioning' => $this->boolean()->defaultValue(true)->notNull(),
			'hasTitleField'    => $this->boolean()->defaultValue(true)->notNull(),
			'titleLabel'       => $this->string()->defaultValue('Title'),
			'titleFormat'      => $this->string(),
			'propagateEvents'  => $this->boolean()->defaultValue(true)->notNull(),
			'dateCreated'      => $this->dateTime()->notNull(),
			'dateUpdated'      => $this->dateTime()->notNull(),
			'dateDeleted'      => $this->dateTime()->null(),
			'uid'              => $this->uid(),
		]);

		$this->createTable(EventTypeSite::TableName, [
			'id'               => $this->primaryKey(),
			'eventTypeId'      => $this->integer()->notNull(),
			'siteId'           => $this->integer()->notNull(),
			'enabledByDefault' => $this->boolean()->defaultValue(true)->notNull(),
			'hasUrls'          => $this->boolean(),
			'uriFormat'        => $this->text(),
			'template'         => $this->string(500),
			'dateCreated'      => $this->dateTime()->notNull(),
			'dateUpdated'      => $this->dateTime()->notNull(),
			'uid'              => $this->uid(),
		]);

		// Indexes
		// ---------------------------------------------------------------------

		$this->createIndex(null, Event::TableName, ['postDate'], false);
		$this->createIndex(null, Event::TableName, ['expiryDate'], false);
		$this->createIndex(null, Event::TableName, ['authorId'], false);
		$this->createIndex(null, Event::TableName, ['typeId'], false);

		$this->createIndex(null, EventType::TableName, ['name'], false);
		$this->createIndex(null, EventType::TableName, ['handle'], false);
		$this->createIndex(null, EventType::TableName, ['fieldLayoutId'], false);
		$this->createIndex(null, EventType::TableName, ['dateDeleted'], false);

		$this->createIndex(null, EventTypeSite::TableName, ['eventTypeId', 'siteId'], true);
		$this->createIndex(null, EventTypeSite::TableName, ['siteId'], false);

		// Foreign Keys
		// ---------------------------------------------------------------------

		// Event

		$this->addForeignKey(
			null,
			Event::TableName,
			['id'],
			Table::ELEMENTS,
			['id'],
			'CASCADE',
			null
		);

		$this->addForeignKey(
			null,
			Event::TableName,
			['authorId'],
			Table::USERS,
			['id'],
			'CASCADE',
			null
		);

		$this->addForeignKey(
			null,
			Event::TableName,
			['typeId'],
			EventType::TableName,
			['id'],
			'CASCADE',
			null
		);

		// Event Type

		$this->addForeignKey(
			null,
			EventType::TableName,
			['fieldLayoutId'],
			Table::FIELDLAYOUTS,
			['id'],
			'SET NULL',
			null
		);

		// Event Type Site

		$this->addForeignKey(
			null,
			EventTypeSite::TableName,
			['siteId'],
			Table::SITES,
			['id'],
			'CASCADE',
			'CASCADE'
		);

		$this->addForeignKey(
			null,
			EventTypeSite::TableName,
			['eventTypeId'],
			EventType::TableName,
			['id'],
			'CASCADE',
			null
		);

	}

	/**
	 * Drop the tables for the Event element type
	 *
	 * @throws \yii\base\NotSupportedException
	 */
	private function _dropEventTables ()
	{
		if ($this->_tableExists(Event::TableName))
		{
			MigrationHelper::dropAllForeignKeysToTable(Event::TableName, $this);
			MigrationHelper::dropAllForeignKeysOnTable(Event::TableName, $this);
		}

		if ($this->_tableExists(EventType::TableName))
		{
			MigrationHelper::dropAllForeignKeysToTable(EventType::TableName, $this);
			MigrationHelper::dropAllForeignKeysOnTable(EventType::TableName, $this);
		}

		if ($this->_tableExists(EventTypeSite::TableName))
		{
			MigrationHelper::dropAllForeignKeysToTable(EventTypeSite::TableName, $this);
			MigrationHelper::dropAllForeignKeysOnTable(EventTypeSite::TableName, $this);
		}

		$this->dropTableIfExists(Event::TableName);
		$this->dropTableIfExists(EventType::TableName);
		$this->dropTableIfExists(EventTypeSite::TableName);
	}

	// Tickets
	// =========================================================================

	private function _createTicketTables ()
	{
		// Tables
		// ---------------------------------------------------------------------

		$this->createTable(Ticket::TableName, [
			'id'              => $this->primaryKey(),
			'typeId'          => $this->integer()->notNull(),
			'deletedWithType' => $this->boolean()->null(),
			'dateCreated'     => $this->dateTime()->notNull(),
			'dateUpdated'     => $this->dateTime()->notNull(),
			'uid'             => $this->uid(),
		]);
	}

	// Helpers
	// =========================================================================

	/**
	 * Checks if the given table exists
	 *
	 * @param string $tableName
	 *
	 * @return bool
	 * @throws \yii\base\NotSupportedException
	 */
	private function _tableExists (string $tableName): bool
	{
		$schema = $this->db->getSchema();
		$schema->refresh();

		$rawTableName = $schema->getRawTableName($tableName);
		$table = $schema->getTableSchema($rawTableName);

		return (bool) $table;
	}

}
