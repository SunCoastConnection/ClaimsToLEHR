<?php

namespace SunCoastConnection\ClaimsToOEMR\X12N837\Loop;

use \SunCoastConnection\ClaimsToOEMR\Document\Loop;

class Loop2320 extends Loop {

	static protected $headerSequence = [
		['name' => 'SBR', 'required' => false, 'repeat' => 1],
		['name' => 'CAS', 'required' => false, 'repeat' => 99],
		['name' => 'AMT', 'required' => false, 'repeat' => 15],
		['name' => 'DMG', 'required' => false, 'repeat' => 1],
		['name' => 'OI', 'required' => false, 'repeat' => 1],
		['name' => 'MIA', 'required' => false, 'repeat' => 1],
		['name' => 'MOA', 'required' => false, 'repeat' => 1],
	];

	static protected $descendantSequence = [
		['name' => 'Loop2330', 'required' => false, 'repeat' => 10],
	];

}