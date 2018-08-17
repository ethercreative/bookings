<?php

namespace ether\bookings\migrations;

use Craft;
use craft\db\Migration;
use craft\helpers\Db;
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
	        $this->dateTime()->notNull()->defaultValue(
	        	// This is *technically* wrong but fuck it
	        	Db::prepareDateForDb(new \DateTime())
	        )
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
