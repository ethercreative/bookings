<?php
/**
 * Bookings plugin for Craft CMS 3.x
 *
 * @link      https://ethercreative.co.uk
 * @copyright Copyright (c) 2018 Ether Creative
 */

namespace ether\bookings\common\scheduling;

/**
 * Class Schedule
 *
 * Loosely based off Illuminate\Console\Scheduling.
 *
 * @author  Ether Creative
 * @package ether\bookings\common
 */
class Schedule
{

	// Properties
	// =========================================================================

	/** @var Job[] $jobs */
	protected $jobs = [];

	// Methods
	// =========================================================================

	public function queue ($callback, array $arguments = [])
	{
		$this->jobs[] = $job = new Job($callback, $arguments);

		return $job;
	}

	public function run ()
	{
		/** @var Job $job */
		foreach ($this->jobs as $job)
			if ($job->isDue())
				$job->run();
	}

}