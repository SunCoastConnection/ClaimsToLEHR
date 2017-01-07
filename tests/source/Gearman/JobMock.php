<?php

namespace SunCoastConnection\ClaimsToEMR\Tests\Gearman;

use \Kicken\Gearman\Job\WorkerJob;
use \SunCoastConnection\ClaimsToEMR\Gearman\Job;

class JobMock extends Job {

	public function run(WorkerJob $job, &$log) {}

}