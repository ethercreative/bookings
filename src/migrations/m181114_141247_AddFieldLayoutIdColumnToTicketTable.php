<?php

namespace ether\bookings\migrations;

use Craft;
use craft\db\Migration;
use ether\bookings\records\TicketRecord;

/**
 * m181114_141247_AddFieldLayoutIdColumnToTicketTable migration.
 */
class m181114_141247_AddFieldLayoutIdColumnToTicketTable extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->addColumn(
        	TicketRecord::$tableName,
	        'fieldLayoutId',
	        $this->integer()->null()
        );
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->dropColumn(
        	TicketRecord::$tableName,
	        'fieldLayoutId'
        );
    }
}
