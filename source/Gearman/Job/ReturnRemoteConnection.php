<?php

namespace SunCoastConnection\ClaimsToEMR\Gearman\Job;

use \Kicken\Gearman\Job\WorkerJob;
use \SunCoastConnection\ClaimsToEMR\Gearman\Job;

class ReturnRemoteConnection extends Job {

	/**
	 * Run the ReturnRemoteConnection job
	 *
	 * @param  \Kicken\Gearman\Job\WorkerJob  $job  Job request to perform run on
	 *
	 * @return mixed  Saved Configuration or return code (1 = credentials file missing, 2 = failed to read configuration file)
	 */
	public function run(WorkerJob $job, &$log) {
		$credentialsPath = $this->options()->get('Credentials.path');

		$workload = json_decode($job->getWorkload(), true);

		// $workload = [
		// 	'client' => 'tokenName'
		// ];

		$remoteConfigurationPath = $credentialsPath.'/'.$workload['client'].'.json';

		if(!file_exists($remoteConfigurationPath)) {
			// Configuration file does not exists
			return 1;
		} elseif(!is_readable($remoteConfigurationPath)) {
			// Configuration file exists but is not readable
			return 2;
		}

		return file_get_contents($remoteConfigurationPath);
	}
}