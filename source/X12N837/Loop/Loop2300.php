<?php

namespace SunCoastConnection\ClaimsToOEMR\X12N837\Loop;

use \SunCoastConnection\ClaimsToOEMR\Document\Section\Loop;

class Loop2300 extends Loop {

	static protected $headerSequence = [
		['name' => 'CLM', 'required' => false, 'repeat' => 1],
		['name' => 'DTP', 'required' => false, 'repeat' => 150],
		['name' => 'CL1', 'required' => false, 'repeat' => 1],
		['name' => 'DN1', 'required' => false, 'repeat' => 1],
		['name' => 'DN2', 'required' => false, 'repeat' => 35],
		['name' => 'PWK', 'required' => false, 'repeat' => 10],
		['name' => 'CN1', 'required' => false, 'repeat' => 1],
		['name' => 'DSB', 'required' => false, 'repeat' => 1],
		['name' => 'UR', 'required' => false, 'repeat' => 1],
		['name' => 'AMT', 'required' => false, 'repeat' => 40],
		['name' => 'REF', 'required' => false, 'repeat' => 30],
		['name' => 'K3', 'required' => false, 'repeat' => 10],
		['name' => 'NTE', 'required' => false, 'repeat' => 20],
		['name' => 'CR1', 'required' => false, 'repeat' => 1],
		['name' => 'CR2', 'required' => false, 'repeat' => 1],
		['name' => 'CR3', 'required' => false, 'repeat' => 1],
		['name' => 'CR4', 'required' => false, 'repeat' => 3],
		['name' => 'CR5', 'required' => false, 'repeat' => 1],
		['name' => 'CR6', 'required' => false, 'repeat' => 1],
		['name' => 'CR8', 'required' => false, 'repeat' => 9],
		['name' => 'CRC', 'required' => false, 'repeat' => 100],
		['name' => 'HI', 'required' => false, 'repeat' => 25],
		['name' => 'QTY', 'required' => false, 'repeat' => 10],
		['name' => 'HCP', 'required' => false, 'repeat' => 1],
	];

	static protected $descendantSequence = [
		['name' => 'Loop2305', 'required' => true, 'repeat' => 6],
		['name' => 'Loop2310', 'required' => true, 'repeat' => 9],
		['name' => 'Loop2320', 'required' => true, 'repeat' => 10],
		['name' => 'Loop2400', 'required' => true, 'repeat' => -1],
	];

}