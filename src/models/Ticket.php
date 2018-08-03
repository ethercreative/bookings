<?php
/**
 * Bookings plugin for Craft CMS 3.x
 *
 * @link      https://ethercreative.co.uk
 * @copyright Copyright (c) Ether Creative
 */

namespace ether\bookings\models;

use craft\helpers\Template;
use ether\bookings\base\Model;
use ether\bookings\Bookings;
use ether\bookings\fields\TicketField;
use ether\bookings\records\TicketRecord;


/**
 * Class Ticket
 *
 * @author  Ether Creative
 * @package ether\bookings\models
 * @since   1.0.0
 */
class Ticket extends Model
{

	// Properties
	// =========================================================================

	// Properties: Public
	// -------------------------------------------------------------------------

	/** @var int */
	public $id;

	/** @var int */
	public $eventId;

	/** @var int */
	public $elementId;

	/** @var int */
	public $fieldId;

	/**
	 * @var int - The max number of this type of ticket that can be sold per
	 * slot (or selected slot range if flexible)
	 */
	public $capacity = 1;

	/**
	 * @var int - The maximum number of this type of ticket that can be booked
	 * per booking
	 */
	public $maxQty;

	// Properties: Private
	// -------------------------------------------------------------------------

	/** @var Event */
	private $_event;
	/** @var TicketField */
	private $_field;

	// Methods
	// =========================================================================

	public static function fromRecord (TicketRecord $record)
	{
		$model = new Ticket();

		$model->id        = $record->id;
		$model->eventId   = $record->eventId;
		$model->elementId = $record->elementId;
		$model->fieldId   = $record->fieldId;
		$model->capacity  = $record->capacity;
		$model->maxQty    = $record->maxQty;

		return $model;
	}

	public function rules ()
	{
		$rules = parent::rules();

		$rules[] = [
			['capacity', 'maxQty'],
			'number'
		];

		return $rules;
	}

	/**
	 * Generates the ticket input
	 *
	 * @return string|\Twig_Markup
	 * @throws \yii\base\Exception
	 * @throws \yii\base\InvalidConfigException
	 */
	public function input ()
	{
		$value = \Craft::$app->security->hashData($this->id);

		return Template::raw('<input type="hidden" name="ticketId" value="' . $value . '" />');
	}

	// Getter
	// -------------------------------------------------------------------------

	/**
	 * @return Event|null
	 */
	public function getEvent ()
	{
		if ($this->_event)
			return $this->_event;

		return $this->_event = Bookings::getInstance()->events->getEventById($this->eventId);
	}

	/**
	 * @return \craft\base\FieldInterface|TicketField|null
	 */
	public function getField ()
	{
		if ($this->_field)
			return $this->_field;

		return $this->_field = \Craft::$app->fields->getFieldById($this->fieldId);
	}

}