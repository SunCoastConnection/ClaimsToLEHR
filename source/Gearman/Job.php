<?php

namespace SunCoastConnection\ClaimsToOEMR\Gearman;

use \Kicken\Gearman\Job\WorkerJob;
use \SunCoastConnection\ClaimsToOEMR\Document\Options;

abstract class Job {

	/**
	 * Get instance of Job class with provided options
	 *
	 * @param  \SunCoastConnection\ClaimsToOEMR\Document\Options  $options  Options to create Job object with
	 *
	 * @return \SunCoastConnection\ClaimsToOEMR\Gearman\Job  Job object
	 */
	static public function getInstance(Options $options) {
		return new static($options);
	}

	/**
	 * Create a new Job
	 *
	 * @param \SunCoastConnection\ClaimsToOEMR\Document\Options  $options  Options to create Job object with
	 */
	public function __construct(Options $options) {
		$this->options($options);
	}

	/**
	 * Set Job options or retrieve Job options
	 *
	 * @param  \SunCoastConnection\ClaimsToOEMR\Document\Options|null  $setOptions  Options to set Job object with
	 *
	 * @return \SunCoastConnection\ClaimsToOEMR\Document\Options|null  Job options or null when not set
	 */
	protected function options(Options $setOptions = null) {
		static $options = null;

		if(is_null($options) && !is_null($setOptions)) {
			$options = $setOptions;
		}

		return $options;
	}

	abstract public function run(WorkerJob $job, &$log);
}