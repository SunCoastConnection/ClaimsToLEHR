<?php

namespace SunCoastConnection\ClaimsToOEMR\X12N837\Envelope;

use \SunCoastConnection\ClaimsToOEMR\Document\Section\Envelope;

class FunctionalGroup extends Envelope {

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