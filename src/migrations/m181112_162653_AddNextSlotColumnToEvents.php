<?php

namespace ether\bookings\migrations;

use Craft;
use craft\db\Migration;
use ether\bookings\records\EventRecord;

/**
 * m181112_162653_AddNextSlotColumnToEvents migration.
 */
class m181112_162653_AddNextSlotColumnToEvents extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->addColumn(
        	EventRecord::$tableName,
	        'nextSlot',
	        $this->dateTime()->null()
        );
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->dropColumn(
        	EventRecord::$tableName,
	        'nextSlot'
        );
    }
}
