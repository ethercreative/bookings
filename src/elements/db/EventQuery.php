<?php
/**
 * Bookings for Craft CMS
 *
 * @link      https://ethercreative.co.uk
 * @copyright Copyright (c) 2019 Ether Creative
 */

namespace ether\bookings\elements\db;

use craft\db\Query;
use craft\db\QueryAbortedException;
use craft\db\Table;
use craft\elements\db\ElementQuery;
use craft\helpers\ArrayHelper;
use craft\helpers\Db;
use craft\models\UserGroup;
use ether\bookings\Bookings;
use ether\bookings\elements\Event;
use ether\bookings\models\EventType;
use ether\bookings\records\EventType as EventTypeRecord;

/**
 * Class EventQuery
 *
 * @author  Ether Creative
 * @package ether\bookings\elements\db
 */
class EventQuery extends ElementQuery
{

	// Properties
	// =========================================================================

	/**
	 * @var bool Whether to only return products that the user has permission to edit.
	 */
	public $editable = false;

	/**
	 * @var int|int[]|null The event type ID(s) that the resulting events must have.
	 */
	public $typeId;

	/**
	 * @var int|int[]|null The user ID(s) that the resulting events authors must have.
	 */
	public $authorId;

	/**
	 * @var int|int[]|null The user group ID(s) that the resulting events authors must be in.
	 */
	public $authorGroupId;

	/**
	 * @var mixed The post date of the resulting events
	 */
	public $postDate;

	/**
	 * @var mixed The max post date that the resulting events can have.
	 */
	public $before;

	/**
	 * @var mixed The min post date the resulting events can have.
	 */
	public $after;

	/**
	 * @var mixed The expiry date of the resulting events
	 */
	public $expiryDate;

	/**
	 * @inheritdoc
	 */
	protected $defaultOrderBy = ['events.postDate' => SORT_DESC];

	// Public Methods
	// =========================================================================

	/**
	 * @inheritdoc
	 */
	public function __construct ($elementType, array $config = [])
	{
		if (!isset($config['status']))
			$config['status'] = [Event::STATUS_LIVE, Event::STATUS_FULL];

		parent::__construct($elementType, $config);
	}

	public function __set ($name, $value)
	{
		switch ($name)
		{
			case 'type':
				$this->type($value);
				break;
			case 'authorGroup':
				$this->authorGroup($value);
				break;
			default:
				parent::__set($name, $value);
		}
	}

	public function editable (bool $value = true)
	{
		$this->editable = $value;

		return $this;
	}

	public function type ($value)
	{
		if ($value instanceof EventType)
			$this->typeId = $value->id;

		else if ($value !== null)
			$this->typeId = (new Query())
				->select(['id'])
				->from([EventTypeRecord::TableName])
				->where(Db::parseParam('handle', $value))
				->column();

		else
			$this->typeId = null;

		return $this;
	}

	public function typeId ($value)
	{
		$this->typeId = $value;

		return $this;
	}

	public function authorId ($value)
	{
		$this->authorId = $value;

		return $this;
	}

	public function authorGroup ($value)
	{
		if ($value instanceof UserGroup)
			$this->authorGroupId = $value->id;

		else if ($value !== null)
			$this->authorGroupId = (new Query())
				->select(['id'])
				->from([Table::USERGROUPS])
				->where(Db::parseParam('handle', $value))
				->column();

		else
			$this->authorGroupId = null;

		return $this;
	}

	public function authorGroupId ($value)
	{
		$this->authorGroupId = $value;

		return $this;
	}

	public function postDate ($value)
	{
		$this->postDate = $value;

		return $this;
	}

	public function before ($value)
	{
		$this->before = $value;

		return $this;
	}

	public function after ($value)
	{
		$this->after = $value;

		return $this;
	}

	public function expiryDate ($value)
	{
		$this->expiryDate = $value;

		return $this;
	}

	// Protected Methods
	// =========================================================================

	/**
	 * @inheritdoc
	 * @throws QueryAbortedException
	 */
	protected function beforePrepare (): bool
	{
		// See if 'type' or 'authorGroup' were set to invalid handles
		if ($this->typeId === [] || $this->authorGroupId === [])
			return false;

		$this->joinElementTable('bookings_events');

		$this->query->select([
			'bookings_events.typeId',
			'bookings_events.authorId',
			'bookings_events.postDate',
			'bookings_events.expiryDate',
		]);

		if ($this->typeId)
			$this->subQuery->andWhere(Db::parseParam('bookings_events.typeId', $this->typeId));

		if ($this->authorId)
			$this->subQuery->andWhere(Db::parseParam('bookings_events.authorId', $this->authorId));

		if ($this->authorGroupId)
			$this->subQuery
				->innerJoin(
					'{{%usergroups_users}} usergroups_users',
					'[[usergroups_users.userId]] = [[bookings_events.authorId]]'
				)
				->andWhere(
					Db::parseParam('usergroups_users.groupId', $this->authorGroupId)
				);

		if ($this->postDate)
		{
			$this->subQuery->andWhere(
				Db::parseParam('bookings_events.postDate', $this->postDate)
			);
		}
		else
		{
			if ($this->before)
				$this->subQuery->andWhere(
					Db::parseParam('bookings_events.postDate', $this->before, '<')
				);

			if ($this->after)
				$this->subQuery->andWhere(
					Db::parseParam('bookings_events.postDate', $this->after, '>=')
				);
		}

		if ($this->expiryDate)
			$this->subQuery->andWhere(Db::parseParam('bookings_events.expiryDate', $this->expiryDate));

		$this->_applyEditableParam();
		$this->_applyRefParam();

		return parent::beforePrepare();
	}

	/**
	 * @inheritdoc
	 * @throws \Exception
	 */
	protected function statusCondition (string $status)
	{
		$currentTimeDb = Db::prepareDateForDb(new \DateTime());

		switch ($status)
		{
			case Event::STATUS_LIVE:
				return [
					'and',
					[
						'elements.enabled'       => true,
						'elements_sites.enabled' => true
					],
					['<=', 'bookings_events.postDate', $currentTimeDb],
					[
						'or',
						['bookings_events.expiryDate' => null],
						['>', 'bookings_events.expiryDate', $currentTimeDb]
					]
				];
			case Event::STATUS_PENDING:
				return [
					'and',
					[
						'elements.enabled'       => true,
						'elements_sites.enabled' => true,
					],
					['>', 'bookings_events.postDate', $currentTimeDb]
				];
			case Event::STATUS_EXPIRED:
				return [
					'and',
					[
						'elements.enabled'       => true,
						'elements_sites.enabled' => true
					],
					['not', ['bookings_events.expiryDate' => null]],
					['<=', 'bookings_events.expiryDate', $currentTimeDb]
				];
			default:
				return parent::statusCondition($status);
		}
	}

	// Private Methods
	// =========================================================================

	/**
	 * @throws QueryAbortedException
	 */
	private function _applyEditableParam ()
	{
		if (!$this->editable)
			return;

		$user = \Craft::$app->getUser()->getIdentity();

		if (!$user)
			throw new QueryAbortedException();

		$this->subQuery->andWhere([
			'bookings_events.typeId' => Bookings::$i->eventTypes->getEditableEventTypeIds(),
		]);
	}

	/**
	 * Applies the 'ref' param to the query being prepared.
	 */
	private function _applyRefParam ()
	{
		if (!$this->ref)
			return;

		$refs         = ArrayHelper::toArray($this->ref);
		$joinSections = false;
		$condition    = ['or'];

		foreach ($refs as $ref)
		{
			$parts = array_filter(explode('/', $ref));

			if (empty($parts))
				continue;

			if (count($parts) == 1)
			{
				$condition[] =
					Db::parseParam('elements_sites.slug', $parts[0]);
			}
			else
			{
				$condition[]  = [
					'and',
					Db::parseParam('bookings_eventtypes.handle', $parts[0]),
					Db::parseParam('elements_sites.slug', $parts[1])
				];

				$joinSections = true;
			}
		}

		$this->subQuery->andWhere($condition);

		if (!$joinSections)
			return;

		$this->subQuery->innerJoin(
			'{{%bookings_eventtypes}} bookings_eventtypes',
			'[[bookings_eventtypes.id]] = [[bookings_events.typeId]]'
		);
	}

}
