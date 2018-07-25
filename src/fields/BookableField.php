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
use craft\i18n\Locale;
use ether\bookings\Bookings;
use ether\bookings\models\Bookable;
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

	//

	// Properties: Craft
	// -------------------------------------------------------------------------

	public $translationMethod = self::TRANSLATION_METHOD_NONE;

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

	public static function supportedTranslationMethods (): array
	{
		return [self::TRANSLATION_METHOD_NONE];
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

		$handle       = $view->namespaceInputName($this->handle);

		$timezone = \Craft::$app->getTimeZone();
		$dateFormat = [
			'date' => \Craft::$app->locale->getDateFormat(
				Locale::LENGTH_SHORT, Locale::FORMAT_PHP
			),
			'time' => \Craft::$app->locale->getTimeFormat(
				Locale::LENGTH_SHORT, Locale::FORMAT_PHP
			),
		];
		$value = $value->asArray();

		if (getenv('ETHER_ENVIRONMENT'))
		{
			$view->registerJsFile('https://localhost:8080/bundle.js', [
				'async' => true,
			]);
		}
		else
		{
			$view->registerAssetBundle(UIAsset::class);
		}

		$props = json_encode(array_merge(
			compact('handle', 'timezone', 'dateFormat'),
			$value
		));

		return new \Twig_Markup(
			'<craft-bookings><script type="text/props">' . $props . '</script><div class="spinner"></div></craft-bookings>',
			'utf-8'
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

	// Public Methods: Settings
	// -------------------------------------------------------------------------

	/**
	 * @return null|string
	 * @throws \Twig_Error_Loader
	 * @throws \yii\base\Exception
	 */
	public function getSettingsHtml ()
	{
		return \Craft::$app->view->renderTemplate(
			'bookings/field/_settings'
		);
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