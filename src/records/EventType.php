<?php
/**
 * Bookings for Craft CMS
 *
 * @link      https://ethercreative.co.uk
 * @copyright Copyright (c) 2019 Ether Creative
 */

namespace ether\bookings\records;

use craft\db\ActiveRecord;
use craft\db\SoftDeleteTrait;
use craft\records\FieldLayout;
use yii\db\ActiveQueryInterface;

/**
 * Class EventType
 *
 * @property int         $id               ID
 * @property int         $fieldLayoutId    Field Layout ID
 * @property string      $name             Name
 * @property string      $handle           Handle
 * @property bool        $enableVersioning Enable Versioning
 * @property bool        $hasTitleField    Has title field
 * @property string      $titleLabel       Title label
 * @property string      $titleFormat      Title format
 * @property int         $sortOrder        Sort order
 * @property bool        $propagateEvents  Should propagate events across sites
 * @property FieldLayout $fieldLayout      Field Layout
 * @author  Ether Creative
 * @package ether\bookings\records
 */
class EventType extends ActiveRecord
{

	// Consts
	// =========================================================================

	const TableName = '{{%bookings_eventtypes}}';

	// Traits
	// =========================================================================

	use SoftDeleteTrait;

	// Methods
	// =========================================================================

	/**
	 * @inheritdoc
	 * @return string
	 */
	public static function tableName (): string
	{
		return self::TableName;
	}

	/**
	 * Returns the event type's field layout
	 *
	 * @return ActiveQueryInterface
	 */
	public function getFieldLayout (): ActiveQueryInterface
	{
		return $this->hasOne(
			FieldLayout::class,
			['id' => 'fieldLayoutId']
		);
	}

}
