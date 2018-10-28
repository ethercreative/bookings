<?php
/**
 * Bookings plugin for Craft CMS 3.x
 *
 * @link      https://ethercreative.co.uk
 * @copyright Copyright (c) 2018 Ether Creative
 */

namespace ether\bookings\graph;

use ether\bookings\records\BookingRecord;
use ether\bookings\records\EventRecord;
use ether\bookings\records\TicketRecord;
use GraphQL\Deferred;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;

/**
 * Class Types
 *
 * @author  Ether Creative
 * @package ether\bookings\graph
 */
class Types
{

	private $_types = [];

	public function get ($type)
	{
		if (!isset($this->_types[$type]))
			$this->_types[$type] = $this->{$type}();

		return $this->_types[$type];
	}

	public function query ()
	{
		return new ObjectType([
			'name' => 'Query',
			'fields' => [
				'events' => Type::listOf($this->get('events')),
//				'bookings' => Type::listOf($this->get('bookings')),
//				'events'   => $this->get('events'),
				'bookings' => $this->get('bookings'),
				'tickets' => $this->get('tickets'),
			],
			'resolveField' => function ($source, $args, $context, ResolveInfo $info) {
				return $this->{$info->fieldName}($source, $args, $context, $info);
			},
		]);
	}

	public function events ()
	{
		$resolve = $this->_resolve(EventRecord::$tableName);
		$content = $this->_resolve('{{%content}}');

		return new ObjectType([
			'name' => 'Events',
			'description' => 'Queries all events',
			'fields' => [
				'id' => Type::id(),
				'title' => [
					'type'    => Type::string(),
					'resolve' => $content,
				],
//				'bookings' => Type::listOf($this->get('bookings')),
				'bookings' => $this->get('bookings'),
				'tickets' => $this->get('tickets'),
			],
			'resolveField' => $resolve,
		]);
	}

	public function tickets ()
	{
		$resolve = $this->_resolve(TicketRecord::$tableName);

		return new ObjectType([
			'name' => 'Tickets',
			'description' => 'Queries all tickets',
			'fields' => [
				'id' => Type::id(),
			],
			'resolveField' => $resolve,
		]);
	}

	public function bookings ()
	{
		$resolve = $this->_resolve(BookingRecord::$tableName);

		return new ObjectType([
			'name' => 'Bookings',
			'description' => 'Queries all bookings',
			'fields' => [
				'id' => Type::id(),
			],
			'resolveField' => $resolve,
		]);
	}

	// Helpers
	// =========================================================================

	private function _resolve ($table)
	{
		return function ($source, $args, $context, ResolveInfo $info) use ($table) {
			$field = $info->fieldName;

			if (method_exists($this, $field))
				return $this->{$field}($source, $args, $context, $info);

			Buffer::add($table, $field);

			return new Deferred(function () use ($table, $field) {
				Buffer::load();
				return Buffer::get($table, $field);
			});
		};
	}

}