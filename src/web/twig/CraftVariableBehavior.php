<?php
/**
 * Bookings plugin for Craft CMS 3.x
 *
 * @link      https://ethercreative.co.uk
 * @copyright Copyright (c) Ether Creative
 */

namespace ether\bookings\web\twig;

use ether\bookings\common\Availability;
use ether\bookings\models\Event;
use yii\base\Behavior;


/**
 * Class CraftVariableBehavior
 *
 * @author  Ether Creative
 * @package ether\bookings\web\twig
 * @since   1.0.0
 */
class CraftVariableBehavior extends Behavior
{

	/**
	 * {{ craft.availability(myBookableField) }}
	 *
	 * @param Event $event
	 *
	 * @return Availability
	 */
	public function availability (Event $event): Availability
	{
		return new Availability($event);
	}

}