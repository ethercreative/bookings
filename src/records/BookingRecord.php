<?php
/**
 * Bookings plugin for Craft CMS 3.x
 *
 * @link      https://ethercreative.co.uk
 * @copyright Copyright (c) Ether Creative
 */

namespace ether\bookings\records;

use craft\db\ActiveRecord;
use craft\records\Element;
use yii\db\ActiveQueryInterface;


/**
 * Class BookingRecord
 *
 * @property int $id
 * @property bool $isCompleted
 * @property string $number
 * @property int $fieldId
 * @property int $elementId
 * @property int $userId
 * @property int $lineItemId
 * @property int $orderId
 * @property int $customerId
 * @property string $customerEmail
 * @property \DateTime $slotStart
 * @property \DateTime $slotEnd
 * @property \DateTime $dateBooked
 * @property \DateTime $reservationExpiry
 *
 * @author  Ether Creative
 * @package ether\bookings\records
 * @since   1.0.0
 */
class BookingRecord extends ActiveRecord
{

	// Properties
	// =========================================================================

	public static $tableName = '{{%bookings_bookings}}';

	// Public Methods
	// =========================================================================

	// Public Methods: Static
	// -------------------------------------------------------------------------

	public static function tableName (): string
	{
		return self::$tableName;
	}

	// Public Methods: Instance
	// -------------------------------------------------------------------------

	/**
	 * @return ActiveQueryInterface
	 */
	public function getElement(): ActiveQueryInterface
	{
		return $this->hasOne(Element::class, ['id' => 'id']);
	}

}