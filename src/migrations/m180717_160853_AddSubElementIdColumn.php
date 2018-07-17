<?php

namespace ether\bookings\migrations;

use Craft;
use craft\db\Migration;
use ether\bookings\records\BookingRecord;

/**
 * m180717_160853_AddSubElementIdColumn migration.
 */
class m180717_160853_AddSubElementIdColumn extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
	    $this->addColumn(
		    BookingRecord::$tableName,
		    'subElementId',
		    $this->integer()
	    );
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m180717_160853_AddSubElementIdColumn cannot be reverted.\n";
        return false;
    }
}
