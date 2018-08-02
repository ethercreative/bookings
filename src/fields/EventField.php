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
use craft\helpers\Json;
use craft\i18n\Locale;
use ether\bookings\Bookings;
use ether\bookings\models\Event;
use ether\bookings\web\assets\ui\UIAsset;


/**
 * Class EventField
 *
 * @author  Ether Creative
 * @package ether\bookings\fields
 * @since   1.0.0
 */
class EventField extends Field
{

	// Methods
	// =========================================================================

	public static function displayName (): string
	{
		return \Craft::t('bookings', 'Bookable Event');
	}

	public static function hasContentColumn (): bool
	{
		return false;
	}

	public static function supportedTranslationMethods (): array
	{
		return [self::TRANSLATION_METHOD_NONE];
	}

	/**
	 * @param                       $value
	 * @param ElementInterface|null $element
	 *
	 * @return string
	 * @throws \yii\base\InvalidConfigException
	 */
	public function getInputHtml ($value, ElementInterface $element = null): string
	{
		$craft = \Craft::$app;
		$view = $craft->view;

		$handle = $view->namespaceInputName($this->handle);
		/** @var Event|mixed $value */
		$value = $value->asArray();
		$timezone = $craft->getTimeZone();
		$dateFormat = [
			'date' => $craft->locale->getDateFormat(
				Locale::LENGTH_SHORT,
				Locale::FORMAT_PHP
			),
			'time' => $craft->locale->getTimeFormat(
				Locale::LENGTH_SHORT,
				Locale::FORMAT_PHP
			),
		];

		if (getenv('ETHER_ENVIRONMENT'))
		{
			$view->registerJsFile(
				'https://localhost:8080/bundle.js',
				['async' => true]
			);
		}
		else
		{
			$view->registerAssetBundle(UIAsset::class);
		}

		$props = Json::encode(array_merge(
			compact('handle', 'timezone', 'dateFormat'),
			$value
		));

		$markup = <<<HTML
<craft-bookings>
	<script type="text/props">$props</script>
	<div class="spinner"></div>
</craft-bookings>
HTML;

		return new \Twig_Markup($markup, 'utf-8');
	}

	/**
	 * @param                       $value
	 * @param ElementInterface|null $element
	 *
	 * @return Event|mixed
	 */
	public function normalizeValue ($value, ElementInterface $element = null)
	{
		return Bookings::getInstance()->field->getEventField(
			$this,
			$element,
			$value
		);
	}

	/**
	 * @param ElementQueryInterface $query
	 * @param                       $value
	 *
	 * @return bool|false|null
	 */
	public function modifyElementsQuery (ElementQueryInterface $query, $value)
	{
		Bookings::getInstance()->field->modifyEventFieldQuery($query, $value);
		return null;
	}

	/**
	 * @param ElementInterface $element
	 * @param bool             $isNew
	 */
	public function afterElementSave (ElementInterface $element, bool $isNew)
	{
		Bookings::getInstance()->field->saveEventField($this, $element, $isNew);
		parent::afterElementSave($element, $isNew);
	}

}