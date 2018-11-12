<?php
/**
 * Bookings plugin for Craft CMS 3.x
 *
 * @link      https://ethercreative.co.uk
 * @copyright Copyright (c) 2018 Ether Creative
 */

namespace ether\bookings\common\scheduling;

use Cron\CronExpression;

/**
 * Class Job
 *
 * @author  Ether Creative
 * @package ether\bookings\common\scheduling
 */
class Job
{

	use ManagesFrequencies;

	// Properties
	// =========================================================================

	/**
	 * The cron expression representing the job's frequency.
	 *
	 * @var string
	 */
	public $expression = '* * * * *';

	/**
	 * The timezone the date should be evaluated on.
	 *
	 * @var \DateTimeZone|string
	 */
	public $timezone;

	/**
	 * The callback to call.
	 *
	 * @var string
	 */
	protected $callback;

	/**
	 * The arguments to pass to the method.
	 *
	 * @var array
	 */
	protected $arguments;

	// Methods
	// =========================================================================

	/**
	 * Job constructor.
	 *
	 * @param $callback
	 * @param $arguments
	 */
	public function __construct ($callback, $arguments)
	{
		$this->callback = $callback;
		$this->arguments = $arguments;
	}

	/**
	 * Run this job
	 *
	 * @return mixed
	 */
	public function run ()
	{
		return call_user_func_array($this->callback, $this->arguments);
	}

	/**
	 * Is the job due to be run?
	 *
	 * @param string $currentTime
	 *
	 * @return bool
	 */
	public function isDue ($currentTime = 'now')
	{
		return CronExpression::factory(
			$this->expression
		)->isDue($currentTime, $this->timezone);
	}

}