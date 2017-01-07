<?php

namespace SunCoastConnection\ClaimsToEMR\X12N837\Loop;

use \SunCoastConnection\ClaimsToEMR\Document\Section\Loop;

class Loop2440 extends Loop {

	static protected $headerSequence = [
		['name' => 'LQ', 'required' => false, 'repeat' => 1],
		['name' => 'FRM', 'required' => true, 'repeat' => 99],
	];

}