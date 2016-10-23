<?php

namespace SunCoastConnection\ClaimsToOEMR\X12N837\Envelope;

use \SunCoastConnection\ClaimsToOEMR\Document\Section\Envelope;

class InterchangeControl extends Envelope {

	/**
	 * Interchange Control header sequence
	 * @var array
	 */
	static protected $headerSequence = [
		['name' => 'ISA', 'required' => true, 'repeat' => 1],
	];

	/**
	 * Interchange Control descendant sequence
	 * @var array
	 */
	static protected $descendantSequence = [
		['name' => 'FunctionalGroup', 'required' => true, 'repeat' => -1],
	];

	/**
	 * Interchange Control trailer sequence
	 * @var array
	 */
	static protected $trailerSequence = [
		['name' => 'IEA', 'required' => true, 'repeat' => 1],
	];

}