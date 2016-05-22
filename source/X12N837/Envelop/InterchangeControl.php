<?php

namespace SunCoastConnection\ClaimsToOEMR\X12N837\Envelop;

use \SunCoastConnection\ClaimsToOEMR\Document\Envelop;

class InterchangeControl extends Envelop {

	static protected $headerSequence = [
		['name' => 'ISA', 'required' => true, 'repeat' => 1],
	];

	static protected $descendantSequence = [
		['name' => 'FunctionalGroup', 'required' => true, 'repeat' => -1],
	];

	static protected $trailerSequence = [
		['name' => 'IEA', 'required' => true, 'repeat' => 1],
	];

}