<?php

namespace SunCoastConnection\ClaimsToOEMR\X12N837\Envelop;

use \SunCoastConnection\ClaimsToOEMR\Document\Section\Envelop;

class FunctionalGroup extends Envelop {

	static protected $headerSequence = [
		['name' => 'GS', 'required' => true, 'repeat' => 1],
	];

	static protected $descendantSequence = [
		['name' => 'TransactionSet', 'required' => true, 'repeat' => -1],
	];

	static protected $trailerSequence = [
		['name' => 'GE', 'required' => true, 'repeat' => 1],
	];

}