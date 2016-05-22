<?php

namespace SunCoastConnection\ClaimsToOEMR\X12N837\Loop;

use \SunCoastConnection\ClaimsToOEMR\Document\Loop;

class Loop2410 extends Loop {

	static protected $headerSequence = [
		['name' => 'LIN', 'required' => false, 'repeat' => 1],
		['name' => 'CTP', 'required' => false, 'repeat' => 1],
		['name' => 'REF', 'required' => false, 'repeat' => 1],
	];

}