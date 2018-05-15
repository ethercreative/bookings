<?php
/**
 * Bookings plugin for Craft CMS 3.x
 *
 * @link      https://ethercreative.co.uk
 * @copyright Copyright (c) Ether Creative
 */

namespace ether\bookings\fields;

use craft\base\ElementInterface;
use craft\base\Field;
use craft\elements\db\ElementQueryInterface;
use craft\helpers\Html;
use ether\bookings\Bookings;
use ether\bookings\enums\BookableType;
use ether\bookings\models\Bookable;
use ether\bookings\models\ExRule;
use ether\bookings\models\RecursionRule;
use ether\bookings\web\assets\ui\UIAsset;

/**
 * Class BookableField
 *
 * @author  Ether Creative
 * @package ether\bookings\fields
 * @since   1.0.0
 */
class BookableField extends Field
{

	// Properties
	// =========================================================================

	// Properties: Public
	// -------------------------------------------------------------------------

	/**
	 * TODO: is this needed? Why not just use $acceptsRange?
	 *
	 * @var string The type of bookable
	 * @see BookableType
	 */
	public $bookableType;

	/**
	 * @var bool If true, the bookable will accept a range of slots
	 *           TRUE  = Flexible
	 *           FALSE = Fixed
	 */
	public $acceptsRange = false;

	/**
	 * @var int|null The maximum capacity, per-slot, for this bookable
	 */
	public $maxCapacity;

	/**
	 * @var int The number of times each slot is available
	 */
	public $slotMultiplier = 1;

	/**
	 * @var int The duration of each slot in the same unit as the base
	 *          rule's frequency
	 */
	public $slotDuration;

	/**
	 * @var RecursionRule The base RRule
	 */
	public $baseRule;

	/**
	 * @var ExRule[] An array of exceptions to the base rule
	 */
	public $exRules = [];

	// Public Methods
	// =========================================================================

	// Public Methods: Static
	// -------------------------------------------------------------------------

	public static function displayName (): string
	{
		return \Craft::t('bookings', 'Bookable');
	}

	public static function hasContentColumn (): bool
	{
		return false;
	}

	// Public Methods: Instance
	// -------------------------------------------------------------------------

	public function rules ()
	{
		$rules = parent::rules();

//		$rules[] = [
//			['bookableType', 'slotMultiplier', 'baseRule'],
//			'required',
//		];

		return $rules;
	}

	/**
	 * @param Bookable              $value
	 * @param ElementInterface|null $element
	 *
	 * @return string
	 * @throws \yii\base\InvalidConfigException
	 */
	public function getInputHtml ($value, ElementInterface $element = null): string {
		$view = \Craft::$app->view;

		$id           = $view->formatInputId($this->id);
		$handle       = $view->namespaceInputName($this->handle);
		$namespacedId = $view->namespaceInputId($id);

		$value = json_encode($value->asArray());

		$view->registerAssetBundle(UIAsset::class);
		$view->registerJs("new window.__BookingsUI('field', '#$namespacedId', { handle: '$handle', value: $value })");

		return Html::encodeParams(
			'<div id="{id}"></div>',
			[ 'id' => $this->id ]
		);
	}

	/**
	 * @param                       $value
	 * @param ElementInterface|null $element
	 *
	 * @return \ether\bookings\models\Bookable|mixed
	 */
	public function normalizeValue ($value, ElementInterface $element = null)
	{
		return Bookings::getInstance()->field->getField($this, $element, $value);
	}

	/**
	 * @param ElementQueryInterface $query
	 * @param                       $value
	 *
	 * @return bool|false|null
	 */
	public function modifyElementsQuery (ElementQueryInterface $query, $value)
	{
		Bookings::getInstance()->field->modifyElementsQuery($query, $value);
		return null;
	}

	// Public Methods: Events
	// -------------------------------------------------------------------------

	/**
	 * @param ElementInterface $element
	 * @param bool             $isNew
	 */
	public function afterElementSave (ElementInterface $element, bool $isNew)
	{
		Bookings::getInstance()->field->saveField($this, $element);
		parent::afterElementSave($element, $isNew);
	}

}