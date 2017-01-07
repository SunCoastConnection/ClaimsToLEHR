<?php

namespace SunCoastConnection\ClaimsToEMR\X12N837\Loop;

use \SunCoastConnection\ClaimsToEMR\Document\Section\Loop;

class Loop2400 extends Loop {

	static protected $headerSequence = [
		['name' => 'LX', 'required' => false, 'repeat' => 1],
		['name' => 'SV1', 'required' => false, 'repeat' => 1],
		['name' => 'SV2', 'required' => false, 'repeat' => 1],
		['name' => 'SV3', 'required' => false, 'repeat' => 1],
		['name' => 'TOO', 'required' => false, 'repeat' => 32],
		['name' => 'SV4', 'required' => false, 'repeat' => 1],
		['name' => 'SV5', 'required' => false, 'repeat' => 1],
		['name' => 'SV6', 'required' => false, 'repeat' => 1],
		['name' => 'SV7', 'required' => false, 'repeat' => 1],
		['name' => 'HI', 'required' => false, 'repeat' => 26],
		['name' => 'PWK', 'required' => false, 'repeat' => 10],
		['name' => 'CR1', 'required' => false, 'repeat' => 1],
		['name' => 'CR2', 'required' => false, 'repeat' => 5],
		['name' => 'CR3', 'required' => false, 'repeat' => 1],
		['name' => 'CR4', 'required' => false, 'repeat' => 3],
		['name' => 'CR5', 'required' => false, 'repeat' => 1],
		['name' => 'CRC', 'required' => false, 'repeat' => 3],
		['name' => 'DTP', 'required' => false, 'repeat' => 15],
		['name' => 'QTY', 'required' => false, 'repeat' => 5],
		['name' => 'MEA', 'required' => false, 'repeat' => 20],
		['name' => 'CN1', 'required' => false, 'repeat' => 1],
		['name' => 'REF', 'required' => false, 'repeat' => 30],
		['name' => 'AMT', 'required' => false, 'repeat' => 15],
		['name' => 'K3', 'required' => false, 'repeat' => 10],
		['name' => 'NTE', 'required' => false, 'repeat' => 10],
		['name' => 'PS1', 'required' => false, 'repeat' => 1],
		['name' => 'IMM', 'required' => false, 'repeat' => -1],
		['name' => 'HSD', 'required' => false, 'repeat' => 1],
		['name' => 'HCP', 'required' => false, 'repeat' => 1],
	];

	static protected $descendantSequence = [
		['name' => 'Loop2410', 'required' => false, 'repeat' => -1],
		['name' => 'Loop2420', 'required' => false, 'repeat' => 10],
		['name' => 'Loop2430', 'required' => false, 'repeat' => -1],
		['name' => 'Loop2440', 'required' => false, 'repeat' => -1],
	];

}