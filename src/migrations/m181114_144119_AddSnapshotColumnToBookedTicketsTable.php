<?php

namespace ether\bookings\migrations;

use Craft;
use craft\db\Migration;
use ether\bookings\records\BookedTicketRecord;

/**
 * m181114_144119_AddSnapshotColumnToBookedTicketsTable migration.
 */
class m181114_144119_AddSnapshotColumnToBookedTicketsTable extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->addColumn(
        	BookedTicketRecord::$tableName,
	        'snapshot',
	        $this->_json()->null()
        );
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->dropColumn(
        	BookedTicketRecord::$tableName,
	        'snapshot'
        );
    }

	// Helpers
	// =========================================================================

	public function _json ()
	{
		if ($this->db->driverName === 'mysql')
			return $this->longText();

		return $this->json();
	}

}
