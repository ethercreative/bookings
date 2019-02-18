<?php
/**
 * Bookings for Craft CMS
 *
 * @link      https://ethercreative.co.uk
 * @copyright Copyright (c) 2019 Ether Creative
 */

namespace ether\bookings\elements\actions;

use craft\elements\actions\Delete;
use craft\elements\db\ElementQueryInterface;
use ether\bookings\Bookings;

/**
 * Class DeleteEvent
 *
 * @author  Ether Creative
 * @package ether\bookings\elements\actions
 */
class DeleteEvent extends Delete
{

	/**
	 * @inheritdoc
	 * @throws \Throwable
	 */
	public function performAction (ElementQueryInterface $query): bool
	{
		if (!$query)
			return false;

		foreach ($query->all() as $event)
			\Craft::$app->getElements()->deleteElement($event);

		$this->setMessage(Bookings::t('Events deleted.'));

		return true;
	}

}
