<?php
/**
 * Bookings plugin for Craft CMS 3.x
 *
 * @link      https://ethercreative.co.uk
 * @copyright Copyright (c) Ether Creative
 */

namespace ether\bookings\models;

use craft\behaviors\FieldLayoutBehavior;
use craft\helpers\UrlHelper;
use ether\bookings\base\Model;
use ether\bookings\elements\Booking;


/**
 * Class BookingSettings
 *
 * @author  Ether Creative
 * @package ether\bookings\models
 * @since   1.0.0
 */
class BookingSettings extends Model
{

	// Properties
	// =========================================================================

	/** @var int */
	public $id;

	/** @var string */
	public $name;

	/** @var string */
	public $handle;

	/** @var int */
	public $fieldLayoutId;

	// Public Methods
	// =========================================================================

	public function __toString (): string
	{
		return $this->handle;
	}

	public function getCpEditUrl (): string
	{
		return UrlHelper::cpUrl('bookings/settings/bookingsettings');
	}

	public function behaviors (): array
	{
		$behaviors = parent::behaviors();

		$behaviors['fieldLayout'] = [
			'class' => FieldLayoutBehavior::class,
			'elementType' => Booking::class,
		];

		return $behaviors;
	}

}