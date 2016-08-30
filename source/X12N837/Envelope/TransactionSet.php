<?php

namespace SunCoastConnection\ClaimsToOEMR\X12N837\Envelope;

use \SunCoastConnection\ClaimsToOEMR\Document\Section\Envelope;

class TransactionSet extends Envelope {

	static protected $headerSequence = [
		['name' => 'ST', 'required' => true, 'repeat' => 1],
		['name' => 'BHT', 'required' => true, 'repeat' => 1],
		['name' => 'REF', 'required' => false, 'repeat' => 3],
	];

	static protected $descendantSequence = [
		['name' => 'Loop1000', 'required' => true, 'repeat' => 10],
		['name' => 'Loop2000', 'required' => true, 'repeat' => -1],
	];

	static protected $trailerSequence = [
		['name' => 'SE', 'required' => true, 'repeat' => 1],
	];

}