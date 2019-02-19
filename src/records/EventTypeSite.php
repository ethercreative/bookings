<?php
/**
 * Bookings for Craft CMS
 *
 * @link      https://ethercreative.co.uk
 * @copyright Copyright (c) 2019 Ether Creative
 */

namespace ether\bookings\records;

use craft\db\ActiveRecord;
use craft\records\Site;
use yii\db\ActiveQueryInterface;

/**
 * Class EventTypeSite
 *
 * @property int       $id
 * @property int       $eventTypeId
 * @property int       $siteId
 * @property bool      $enabledByDefault
 * @property bool      $hasUrls
 * @property string    $uriFormat
 * @property string    $template
 * @property EventType $eventType
 * @property Site      $site
 * @author  Ether Creative
 * @package ether\bookings\records
 */
class EventTypeSite extends ActiveRecord
{

	// Consts
	// =========================================================================

	const TableName = '{{%bookings_eventtypes_sites}}';

	// Methods
	// =========================================================================

	public static function tableName ()
	{
		return self::TableName;
	}

	public function getEventType (): ActiveQueryInterface
	{
		return $this->hasOne(
			EventType::class,
			['id' => 'eventTypeId']
		);
	}

	public function getSite (): ActiveQueryInterface
	{
		return $this->hasOne(
			Site::class,
			['id' => 'siteId']
		);
	}

}
