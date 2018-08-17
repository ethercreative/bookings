<?php

namespace ether\bookings\migrations;

use Craft;
use craft\db\Migration;
use ether\bookings\records\BookingRecord;
use yii\db\Schema;

/**
 * m180817_134348_AddSlotColumnToBookings migration.
 */
class m180817_134348_AddSlotColumnToBookings extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->addColumn(
        	BookingRecord::$tableName,
	        'slot',
	        $this->dateTime()->notNull()
        );
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->dropColumn(
        	BookingRecord::$tableName,
	        'slot'
        );
    }
}
