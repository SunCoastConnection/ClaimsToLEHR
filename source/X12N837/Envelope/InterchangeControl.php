<?php

namespace SunCoastConnection\ClaimsToOEMR\X12N837\Envelope;

use \SunCoastConnection\ClaimsToOEMR\Document\Section\Envelope;

class InterchangeControl extends Envelope {

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