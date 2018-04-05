<?php
/**
 * Bookings plugin for Craft CMS 3.x
 *
 * @link      https://ethercreative.co.uk
 * @copyright Copyright (c) Ether Creative
 */

namespace ether\bookings\models;

use craft\base\Model;
use craft\behaviors\FieldLayoutBehavior;
use craft\commerce\models\TaxCategory;
use craft\helpers\ArrayHelper;
use craft\helpers\UrlHelper;
use craft\models\FieldLayout;
use craft\validators\HandleValidator;
use ether\bookings\Bookings;
use ether\bookings\elements\Bookable;
use ether\bookings\elements\Variant;

/**
 * Class BookableType
 *
 * @author  Ether Creative
 * @package ether\bookings\models
 * @since   1.0.0
 */
class BookableType extends Model
{

	// Properties
	// =========================================================================

	/** @var int */
	public $id;

	/** @var string */
	public $name;

	/** @var $handle */
	public $handle;

	/** @var bool */
	public $hasUrls;

	/** @var string */
	public $titleFormat = '{bookable.title}';

	/** @var string */
	public $skuFormat;

	/** @var string */
	public $descriptionFormat;

	/** @var string */
	public $lineItemFormat;

	/** @var string */
	public $template;

	/** @var int */
	public $fieldLayoutId;

	/** @var int */
	public $variantFieldLayoutId;

	/** @var TaxCategory[] */
	private $_taxCategories;

	/** @var BookableTypeSite[] */
	private $_siteSettings;

	// Public Methods
	// =========================================================================

	/** @return null|string */
	public function __toString ()
	{
		return $this->handle;
	}

	public function rules ()
	{
		return [
			[
				['id', 'fieldLayoutId', 'variantFieldLayoutId'],
				'number', 'integerOnly' => true,
			],
			[
				['name', 'handle', 'titleFormat'],
				'required',
			],
			[
				['name', 'handle'],
				'string', 'max' => 255,
			],
			[
				['handle'],
				HandleValidator::class,
				'reservedWords' => ['id', 'dateCreated', 'dateUpdated', 'uid', 'title'],
			],
		];
	}

	public function getCpEditUrl (): string
	{
		return UrlHelper::cpUrl(
			'bookings/settings/bookabletypes/' . $this->id
		);
	}

	public function getCpEditVariantUrl (): string
	{
		return UrlHelper::cpUrl(
			'bookings/settings/bookabletypes/' . $this->id . '/variant'
		);
	}

	// Settings
	// -------------------------------------------------------------------------

	/**
	 * Returns the bookable type's site-specific settings
	 *
	 * @return array
	 */
	public function getSiteSettings (): array
	{
		if ($this->_siteSettings !== null)
			return $this->_siteSettings;

		if (!$this->id)
			return [];

		$this->setSiteSettings(
			ArrayHelper::index(
				Bookings::getInstance()
					->getBookableTypes()
					->getBookableTypeSites($this->id),
				'siteId'
			)
		);

		return $this->_siteSettings;
	}

	/**
	 * Sets the bookable type's site-specific settings
	 *
	 * @param array $siteSettings
	 */
	public function setSiteSettings (array $siteSettings)
	{
		$this->_siteSettings = $siteSettings;

		foreach ($this->_siteSettings as $setting)
			$setting->setBookableType($this);
	}

	// Tax
	// -------------------------------------------------------------------------

	/**
	 * @return TaxCategory[]
	 */
	public function getTaxCategories (): array
	{
		if (!$this->_taxCategories)
			$this->_taxCategories = Bookings::getInstance()
				->getTaxCategories()
				->getTaxCategoriesByBookableTypeId($this->id);

		return $this->_taxCategories;
	}

	/**
	 * @param int[]|TaxCategory[] $taxCategories
	 */
	public function setTaxCategories ($taxCategories)
	{
		$categories = [];

		$tC = Bookings::getInstance()->getTaxCategories();

		foreach ($taxCategories as $category)
		{
			if (is_numeric($category))
				if ($category = $tC->getTaxCategoryById($category))
					$categories[$category->id] = $category;

			elseif ($category instanceof TaxCategory)
				if ($category = $tC->getTaxCategoryById($category))
					$categories[$category->id] = $category;
		}

		$this->_taxCategories = $categories;
	}

	// Field Layouts
	// -------------------------------------------------------------------------

	/**
	 * @return FieldLayout
	 * @throws \yii\base\InvalidConfigException
	 */
	public function getBookableFieldLayout (): FieldLayout
	{
		/** @var FieldLayoutBehavior $behaviour */
		$behaviour = $this->getBehavior('bookableFieldLayout');
		return $behaviour->getFieldLayout();
	}

	/**
	 * @return FieldLayout
	 * @throws \yii\base\InvalidConfigException
	 */
	public function getVariantFieldLayout (): FieldLayout
	{
		/** @var FieldLayoutBehavior $behaviour */
		$behaviour = $this->getBehavior('variantFieldLayout');
		return $behaviour->getFieldLayout();
	}

	// Behaviours
	// -------------------------------------------------------------------------

	public function behaviors (): array
	{
		return [
			'bookableFieldLayout' => [
				'class'       => FieldLayoutBehavior::class,
				'elementType' => Bookable::class,
				'idAttribute' => 'fieldLayoutId',
			],
			'variantFieldLayout'  => [
				'class'       => FieldLayoutBehavior::class,
				'elementType' => Variant::class,
				'idAttribute' => 'variantFieldLayoutId',
			],
		];
	}

}