<?php

namespace ether\bookings\graph;

use GraphQL\Deferred;
use GraphQL\GraphQL;
use GraphQL\Type\Schema;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;

class Example
{

	static function run ()
	{
		$types = new self();

		$schema = new Schema([
			'query' => $types->_get('query'),
		]);

		$query = '{ events { id title __typename } }';

		return GraphQL::executeQuery(
			$schema,
			$query,
			null,
			null,
			[]
		);
	}

	// Graph
	// =========================================================================

	private $_types = [];

	public function _get ($type)
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
				'events' => $this->_get('events'),
			],
		]);
	}

	public function events ()
	{
		$eventsType = new ObjectType([
			'name' => 'Events',
			'fields' => [
				'id' => Type::id(),
				'title' => Type::string(),
			],
			'resolveField' => function (
				$source, $args, $context, ResolveInfo $info
			) {
				$field = $info->fieldName;

				self::add('events', $field);
				return $source[$field];
			},
		]);

		return [
			'type' => Type::listOf($eventsType),
			'resolve' => function ($source, $args, $context, $info) {
				if ($source !== null)
					return $source[$info->fieldName];

				return new Deferred(
					function () {
						return self::load();
					}
				);
			},
		];
	}

	// Buffer
	// =========================================================================

	private static $_root    = null;
	private static $_fields  = [];

	static function add ($table, $field)
	{
		if (self::$_root === null)
			self::$_root = $table;

		self::$_fields[] = $field;
	}

	static function load ()
	{
		// TODO: Build & execute the query

		$dummy = [
			[ 'id' => 1, 'title' => 'a' ],
			[ 'id' => 2, 'title' => 'b' ],
			[ 'id' => 3, 'title' => 'c' ],
			[ 'id' => 4, 'title' => 'd' ],
		];

		return array_map(function ($row) {
			return array_filter($row, function ($key) {
				return in_array($key, self::$_fields);
			}, ARRAY_FILTER_USE_KEY);
		}, $dummy);
	}

}