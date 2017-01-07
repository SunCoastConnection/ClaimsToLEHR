<?php

namespace SunCoastConnection\ClaimsToEMR\X12N837\Loop;

use \SunCoastConnection\ClaimsToEMR\Document\Section\Loop;

class Loop2305 extends Loop {

	static protected $headerSequence = [
		['name' => 'CR7', 'required' => false, 'repeat' => 1],
		['name' => 'HSD', 'required' => false, 'repeat' => 12],
	];

}