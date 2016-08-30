<?php

namespace SunCoastConnection\ClaimsToOEMR\X12N837\Loop;

use \SunCoastConnection\ClaimsToOEMR\Document\Section\Loop;

class Loop2430 extends Loop {

	static protected $headerSequence = [
		['name' => 'SVD', 'required' => false, 'repeat' => 1],
		['name' => 'CAS', 'required' => false, 'repeat' => 99],
		['name' => 'DTP', 'required' => false, 'repeat' => 9],
		['name' => 'AMT', 'required' => false, 'repeat' => 20],
	];

}