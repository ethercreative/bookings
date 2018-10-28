<?php
/**
 * Bookings plugin for Craft CMS 3.x
 *
 * @link      https://ethercreative.co.uk
 * @copyright Copyright (c) 2018 Ether Creative
 */

namespace ether\bookings\graph;

use craft\db\Query;
use ether\bookings\records\BookingRecord;
use ether\bookings\records\EventRecord;
use ether\bookings\records\TicketRecord;

/**
 * Class Buffer
 *
 * @author  Ether Creative
 * @package ether\bookings\graph
 */
class Buffer
{

	private static $_root = null;
	private static $_fields = [];
	private static $_results = null;

	public static function add ($table, $field)
	{
		if (self::$_root === null)
			self::$_root = $table;

		if (!isset(self::$_fields[$table]))
			self::$_fields[$table] = [];

		self::$_fields[$table][] = $field;
	}

	public static function load ()
	{
		if (self::$_results !== null)
			return;

		// Each table and how it joins to other tables
		// tableA => [ tableB => [ tableACol, tableBCol ] ]
		$pivots = [
			EventRecord::$tableName => [
				TicketRecord::$tableName => ['id', 'eventId'],
				BookingRecord::$tableName => ['id', 'eventId'],
				'{{%content}}' => ['elementId', 'elementId'],
			],
			BookingRecord::$tableName => [
				EventRecord::$tableName => ['eventId', 'id'],
				'{{%content}}' => ['elementId', 'elementId'],
			],
			TicketRecord::$tableName => [
				EventRecord::$tableName => ['eventId', 'id'],
				'{{%content}}' => ['elementId', 'elementId'],
			],
		];

		$select = [];
		$joins = [];
		$on = [];

		foreach (self::$_fields as $table => $fields)
		{
			if ($table !== self::$_root)
			{
				$joins[] = $table;

				$pivot = $pivots[self::$_root][$table];
				$on[] =
					self::$_root . '.[[' . $pivot[0] . ']] = ' .
					$table . '.[[' . $pivot[1] .  ']]';
			}

			foreach ($fields as $field)
			{
				$column = $table . '.[[' . $field . ']]';
				$select[] = $column . ' as ' . self::_columnHandle($table, $field);
			}
		}

		$query = (new Query())
			->select($select)
			->from(self::$_root);

		for ($i = 0, $l = count($joins); $i < $l; ++$i)
			$query->leftJoin($joins[$i], $on[$i]);

//		\Craft::dd($query->getRawSql());

		self::$_results = $query->all();

//		\Craft::dd(self::$_results);
	}

	public static function get ($table, $field)
	{
		$handle = self::_columnHandle($table, $field);
		return array_map(function ($result) use ($handle) {
			return $result[$handle];
		}, self::$_results);
	}

	// Helpers
	// =========================================================================

	private static function _columnHandle ($table, $field)
	{
		$prefix = preg_replace('/[{{%?|}}]/', '', $table);
		return $prefix . '_' . $field;
	}

}