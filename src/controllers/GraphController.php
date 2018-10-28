<?php
/**
 * Bookings plugin for Craft CMS 3.x
 *
 * @link      https://ethercreative.co.uk
 * @copyright Copyright (c) 2018 Ether Creative
 */

namespace ether\bookings\controllers;

use craft\web\Controller;
use ether\bookings\graph\Types;
use GraphQL\GraphQL;
use GraphQL\Type\Schema;

/**
 * Class GraphController
 *
 * @author  Ether Creative
 * @package ether\bookings\controllers
 */
class GraphController extends Controller
{

	protected $allowAnonymous = true;

	public function actionIndex ()
	{
		$types = new Types();

		$schema = new Schema([
			'query' => $types->get('query'),
		]);

		$query = '{ events { id title tickets { id } } }';

		$result = GraphQL::executeQuery(
			$schema,
			$query,
			null,
			null,
			[]
		);

		\Craft::dd($result);
	}

}