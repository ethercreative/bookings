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
use craft\models\FieldLayout;
use ether\bookings\Bookings;
use ether\bookings\elements\BookedTicket;
use ether\bookings\models\Ticket;
use ether\bookings\web\assets\ticketfield\TicketFieldAsset;


/**
 * Class TicketField
 *
 * @author  Ether Creative
 * @package ether\bookings\fields
 * @since   1.0.0
 */
class TicketField extends Field
{

	// Properties
	// =========================================================================

	/** @var array */
	public $fieldLayout;

	/** @var array */
	public $requiredFields;

	/** @var int|null */
	public $fieldLayoutId;

	// Methods
	// =========================================================================

	public static function displayName (): string
	{
		return \Craft::t('bookings', 'Bookable Ticket');
	}

	public static function hasContentColumn (): bool
	{
		return false;
	}

	public static function supportedTranslationMethods (): array
	{
		return [
			self::TRANSLATION_METHOD_NONE,
		];
	}

	/**
	 * @param                       $value
	 * @param ElementInterface|null $element
	 *
	 * @return string
	 * @throws \Twig_Error_Loader
	 * @throws \yii\base\Exception
	 */
	public function getInputHtml ($value, ElementInterface $element = null): string
	{
		$view = \Craft::$app->view;

		$view->registerAssetBundle(TicketFieldAsset::class);

		return $view->renderTemplate('bookings/fields/ticket-field', [
			'handle'=> $this->handle,
			'field' => $value,
		]);
	}

	/**
	 * @return null|string
	 * @throws \Twig_Error_Loader
	 * @throws \yii\base\Exception
	 */
	public function getSettingsHtml ()
	{
		$craft = \Craft::$app;

		$fieldLayout = null;

		if ($this->fieldLayoutId)
			$fieldLayout = $craft->fields->getLayoutById($this->fieldLayoutId);

		if (!$fieldLayout)
			$fieldLayout = new FieldLayout();

		return $craft->view->renderTemplate(
			'bookings/fields/ticket-settings',
			compact('fieldLayout')
		);
	}

	/**
	 * @param                       $value
	 * @param ElementInterface|null $element
	 *
	 * @return Ticket|mixed
	 */
	public function normalizeValue ($value, ElementInterface $element = null)
	{
		return Bookings::getInstance()->field->getTicketField(
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
		Bookings::getInstance()->field->modifyTicketFieldQuery($query, $value);
		return null;
	}

	/**
	 * @param bool $isNew
	 *
	 * @return bool
	 * @throws \yii\base\Exception
	 */
	public function beforeSave (bool $isNew): bool
	{
		if ($this->fieldLayout)
		{
			$fieldLayout = \Craft::$app->getFields()->assembleLayout(
				$this->fieldLayout,
				$this->requiredFields ?: []
			);
			$fieldLayout->type = BookedTicket::class;

			if ($this->fieldLayoutId !== null)
				$fieldLayout->id = $this->fieldLayoutId;

			\Craft::$app->getFields()->saveLayout($fieldLayout);

			$this->fieldLayoutId = $fieldLayout->id;
		}

		return parent::beforeSave($isNew);
	}

	/**
	 * @param ElementInterface $element
	 * @param bool             $isNew
	 */
	public function afterElementSave (ElementInterface $element, bool $isNew)
	{
		Bookings::getInstance()->field->saveTicketField($this, $element, $isNew);
		parent::afterElementSave($element, $isNew);
	}

}