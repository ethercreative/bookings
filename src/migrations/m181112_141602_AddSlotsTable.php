<?php

namespace ether\bookings\migrations;

use Craft;
use craft\db\Migration;
use craft\helpers\MigrationHelper;
use ether\bookings\records\EventRecord;
use ether\bookings\records\SlotRecord;

/**
 * m181112_141602_AddSlotsTable migration.
 */
class m181112_141602_AddSlotsTable extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
	    $this->createTable(
		    SlotRecord::$tableName, [
		    'id'      => $this->primaryKey(),
		    'eventId' => $this->integer()->notNull(),
		    'slot'    => $this->dateTime()->notNull(),
	    ]
	    );

	    $this->createIndex(
		    null,
		    SlotRecord::$tableName,
		    'eventId',
		    false
	    );

	    $this->addForeignKey(
		    null,
		    SlotRecord::$tableName,
		    'eventId',
		    EventRecord::$tableName,
		    'id',
		    'CASCADE',
		    null
	    );
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
	    MigrationHelper::dropAllForeignKeysOnTable(SlotRecord::$tableName);

	    $this->dropTableIfExists(SlotRecord::$tableName);
    }
}
