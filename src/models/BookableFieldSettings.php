<?php
/**
 * Bookings plugin for Craft CMS 3.x
 *
 * @link      https://ethercreative.co.uk
 * @copyright Copyright (c) Ether Creative
 */

namespace ether\bookings\models;

use craft\behaviors\FieldLayoutBehavior;
use craft\models\FieldLayout;
use ether\bookings\base\Model;
use ether\bookings\elements\Booking;


/**
 * Class BookableFieldSettings
 *
 * @method FieldLayout getFieldLayout()
 * @method setFieldLayout(FieldLayout $fieldLayout)
 *
 * @author  Ether Creative
 * @package ether\bookings\models
 * @since   1.0.0
 */
class BookableFieldSettings extends Model
{

	// Properties
	// =========================================================================

	/** @var int */
	public $id;

	/** @var int - The field these settings belong to */
	public $fieldId;

	/** @var int - The field layouts ID */
	public $fieldLayoutId;

	/** @var \DateTime */
	public $dateCreated;

	/** @var \DateTime */
	public $dateUpdated;

	// Methods
	// =========================================================================

	public function behaviors (): array
	{
		$behaviors = parent::behaviors();

		$behaviors['fieldLayout'] = [
			'class' => FieldLayoutBehavior::class,
			'elementType' => Booking::class
		];

		return $behaviors;
	}

}