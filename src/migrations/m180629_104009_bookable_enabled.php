<?php

namespace ether\bookings\migrations;

use craft\db\Migration;
use ether\bookings\records\BookableRecord;

/**
 * m180629_104009_bookable_enabled migration.
 */
class m180629_104009_bookable_enabled extends Migration
{

	/**
	 * @inheritdoc
	 */
	public function safeUp ()
	{
		$this->addColumn(
			BookableRecord::$tableName,
			'enabled',
			$this->boolean()->notNull()->defaultValue(false)
		);
	}

	/**
	 * @inheritdoc
	 */
	public function safeDown ()
	{
		echo "m180629_104009_bookable_enabled cannot be reverted.\n";

		return false;
	}

}
