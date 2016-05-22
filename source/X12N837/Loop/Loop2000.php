<?php

namespace SunCoastConnection\ClaimsToOEMR\X12N837\Loop;

use \SunCoastConnection\ClaimsToOEMR\Document\Loop;

class Loop2000 extends Loop {

	static protected $headerSequence = [
		['name' => 'HL', 'required' => true, 'repeat' => 1],
		['name' => 'PRV', 'required' => false, 'repeat' => 1],
		['name' => 'SBR', 'required' => false, 'repeat' => 1],
		['name' => 'PAT', 'required' => false, 'repeat' => 1],
		['name' => 'DTP', 'required' => false, 'repeat' => 5],
		['name' => 'CUR', 'required' => false, 'repeat' => 1],
	];

	static protected $descendantSequence = [
		['name' => 'Loop2010', 'required' => false, 'repeat' => 10],
		['name' => 'Loop2300', 'required' => false, 'repeat' => 100],
	];

}