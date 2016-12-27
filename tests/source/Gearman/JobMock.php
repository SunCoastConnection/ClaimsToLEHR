<?php

namespace SunCoastConnection\ClaimsToOEMR\Tests\Gearman;

use \Kicken\Gearman\Job\WorkerJob;
use \SunCoastConnection\ClaimsToOEMR\Gearman\Job;

class JobMock extends Job {

	public function run(WorkerJob $job, &$log) {}

}