<?php
/**
 * Bookings plugin for Craft CMS 3.x
 *
 * @link      https://ethercreative.co.uk
 * @copyright Copyright (c) Ether Creative
 */

namespace ether\bookings\elements;

use craft\commerce\base\Purchasable;
use ether\bookings\Bookings;
use yii\base\InvalidConfigException;

/**
 * Class Variant
 *
 * @author  Ether Creative
 * @package ether\bookings\elements
 * @since   1.0.0
 */
class Variant extends Purchasable
{

	// Properties
	// =========================================================================

	/** @var int */
	public $id;

	/** @var int */
	public $bookableId;

	/** @var bool */
	public $isDefault;

	/** @var string */
	public $sku;

	/** @var float */
	public $price;

	/** @var int */
	public $sortOrder;

	/** @var int */
	public $stock;

	/** @var int */
	public $hasUnlimitedStock;

	/** @var int */
	public $minQty;

	/** @var int */
	public $maxQty;

	/** @var Bookable */
	private $_bookable;

	// Public Methods
	// =========================================================================

	public static function displayName (): string
	{
		return \Craft::t('bookings', 'Bookable Variant');
	}

	public static function refHandle ()
	{
		return 'variant';
	}

	public function rules (): array
	{
		$rules = parent::rules();

		$rules[] = [['sku'], 'string'];
		$rules[] = [['sku', 'price'], 'required'];
		$rules[] = [['price'], 'number'];
		$rules[] = [
			['stock'], 'required', 'when' => function ($model) {
				/** @var Variant $model */
				return !$model->hasUnlimitedStock;
			}
		];
		$rules[] = [
			['stock'], 'number', 'when' => function ($model) {
				/** @var Variant $model */
				return !$model->hasUnlimitedStock;
			}
		];

		return $rules;
	}

	public function extraAttributes (): array
	{
		$names = parent::extraAttributes();

		$names[] = 'bookable';

		return $names;
	}

	/**
	 * @return \craft\models\FieldLayout|null
	 * @throws InvalidConfigException
	 */
	public function getFieldLayout ()
	{
		return parent::getFieldLayout()
			?? $this->getBookable()->getType()->getVariantFieldLayout();
	}

	/**
	 * @return Bookable|null
	 * @throws InvalidConfigException
	 */
	public function getBookable ()
	{
		if ($this->_bookable !== null)
			return $this->_bookable;

		if ($this->bookableId === null)
			throw new InvalidConfigException('Variant is missing its bookable');

		if (
			($bookable = Bookings::getInstance()->getBookables()->getBookableById(
				$this->bookableId,
				$this->siteId
			)) === null
		) {
			throw new InvalidConfigException(
				'Invalid bookable ID: ' .  $this->bookableId
			);
		}

		return $this->_bookable = $bookable;
	}

}