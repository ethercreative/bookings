<?php

namespace ether\bookings\migrations;

use Craft;
use craft\commerce\elements\Product;
use craft\db\Migration;
use craft\helpers\Db;
use craft\queue\jobs\ResaveElements;
use ether\bookings\records\EventRecord;

/**
 * m180817_154950_AddSlotsMetaColumnsToEvents migration.
 */
class m180817_154950_AddSlotsMetaColumnsToEvents extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->addColumn(
        	EventRecord::$tableName,
	        'isInfinite',
	        $this->boolean()->notNull()->defaultValue(false)
        );

        $this->addColumn(
        	EventRecord::$tableName,
	        'firstSlot',
	        $this->dateTime()->notNull()->defaultValue(Db::prepareDateForDb(new \DateTime()))
        );

        $this->addColumn(
        	EventRecord::$tableName,
	        'lastSlot',
	        $this->dateTime()->null()
        );

	    Craft::$app->getQueue()->push(new ResaveElements([
	    	'elementType' => Product::class,
	    ]));
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->dropColumn(EventRecord::$tableName, 'isInfinite');
        $this->dropColumn(EventRecord::$tableName, 'firstSlot');
        $this->dropColumn(EventRecord::$tableName, 'lastSlot');
    }
}
