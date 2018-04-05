<?php
/**
 * Bookings plugin for Craft CMS 3.x
 *
 * @link      https://ethercreative.co.uk
 * @copyright Copyright (c) Ether Creative
 */

namespace ether\bookings\models;

use craft\base\Model;
use craft\models\Site;
use ether\bookings\Bookings;
use yii\base\InvalidConfigException;

/**
 * Class BookableTypeSite
 *
 * @author  Ether Creative
 * @package ether\bookings\models
 * @since   1.0.0
 */
class BookableTypeSite extends Model
{

	// Properties
	// =========================================================================

	/** @var int */
	public $id;

	/** @var int */
	public $bookableTypeId;

	/** @var int */
	public $siteId;

	/** @var bool */
	public $hasUrls;

	/** @var string */
	public $uriFormat;

	/** @var string */
	public $template;

	/** @var bool */
	public $uriFormatIsRequired = true;

	/** @var BookableType */
	private $_bookableType;

	/** @var Site */
	private $_site;

	// Public Methods
	// =========================================================================

	/**
	 * Returns the Bookable Type
	 *
	 * @return BookableType
	 * @throws InvalidConfigException
	 */
	public function getBookableType (): BookableType
	{
		if ($this->_bookableType !== null)
			return $this->_bookableType;

		if (!$this->bookableTypeId)
			throw new InvalidConfigException(
				'Bookable type site is missing its bookable type ID'
			);

		if (
			(
				$this->_bookableType =
					Bookings::getInstance()->getBookableTypes()
						->getBookableTypeById($this->bookableTypeId)
			) === null
		) {
			throw new InvalidConfigException(
				'Invalid bookable type ID: ' . $this->bookableTypeId
			);
		}

		return $this->_bookableType;
	}

	/**
	 * Sets the Bookable Type
	 *
	 * @param BookableType $bookableType
	 */
	public function setBookableType (BookableType $bookableType)
	{
		$this->_bookableType = $bookableType;
	}

	/**
	 * @return Site
	 * @throws InvalidConfigException
	 */
	public function getSite (): Site
	{
		if ($this->_site !== null)
			return $this->_site;

		if (!$this->siteId)
			throw new InvalidConfigException(
				'Bookable type site is missing its site ID'
			);

		if (
			(
				$this->_site = \Craft::$app->getSites()->getSiteById($this->siteId)
			) === null
		) {
			throw new InvalidConfigException(
				'Invalid site ID: ' . $this->siteId
			);
		}

		return $this->_site;
	}

	public function rules (): array
	{
		$rules = parent::rules();

		if ($this->uriFormatIsRequired)
			$rules[] = ['uriFormat', 'required'];

		return $rules;
	}

}