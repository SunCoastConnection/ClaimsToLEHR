<?php

namespace SunCoastConnection\ClaimsToEMR\X12N837\Loop;

use \SunCoastConnection\ClaimsToEMR\Document\Section\Loop;

class Loop2330 extends Loop {

	static protected $headerSequence = [
		['name' => 'NM1', 'required' => false, 'repeat' => 1],
		['name' => 'N2', 'required' => false, 'repeat' => 2],
		['name' => 'N3', 'required' => false, 'repeat' => 2],
		['name' => 'N4', 'required' => false, 'repeat' => 1],
		['name' => 'PER', 'required' => false, 'repeat' => 2],
		['name' => 'DTP', 'required' => false, 'repeat' => 9],
		['name' => 'REF', 'required' => false, 'repeat' => -1],
	];

}