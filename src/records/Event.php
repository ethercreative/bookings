<?php
/**
 * Bookings for Craft CMS
 *
 * @link      https://ethercreative.co.uk
 * @copyright Copyright (c) 2019 Ether Creative
 */

namespace ether\bookings\records;

use craft\db\ActiveRecord;
use craft\records\Element;
use craft\records\User;
use yii\db\ActiveQueryInterface;

/**
 * Class Event
 *
 * @property int $id ID
 * @property int $typeId Event Type ID
 * @property int $authorId Author ID
 * @property \DateTime $postDate Post Date
 * @property \DateTime $expiryDate Expiry Date
 * @property bool $deletedWithType Was the event deleted its event type?
 * @property Element $element Element
 * @property EventType $eventType Event Type
 * @property User $author Author
 *
 * TODO: Event Tickets / Resources / Versions
 *
 * @author  Ether Creative
 * @package ether\bookings\records
 */
class Event extends ActiveRecord
{

	// Consts
	// =========================================================================

	const TableName = '{{%bookings_events}}';
	const TableNameUnprefixed = 'bookings_events';

	// Methods
	// =========================================================================

	/**
	 * @inheritdoc
	 */
	public static function tableName ()
	{
		return self::TableName;
	}

	/**
	 * Gets the events element
	 *
	 * @return ActiveQueryInterface
	 */
	public function getElement (): ActiveQueryInterface
	{
		return $this->hasOne(
			Element::class,
			['id' => 'id']
		);
	}

	/**
	 * Gets the events type
	 *
	 * @return ActiveQueryInterface
	 */
	public function getType (): ActiveQueryInterface
	{
		return $this->hasOne(
			EventType::class,
			['id' => 'typeId']
		);
	}

	/**
	 * Gets the events author
	 *
	 * @return ActiveQueryInterface
	 */
	public function getAuthor (): ActiveQueryInterface
	{
		return $this->hasOne(
			User::class,
			['id' => 'authorId']
		);
	}

}
