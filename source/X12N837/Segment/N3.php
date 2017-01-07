<?php

namespace SunCoastConnection\ClaimsToEMR\X12N837\Segment;

use \SunCoastConnection\ClaimsToEMR\Document\Raw\Segment;

class N3 extends Segment {

	static protected $elementSequence = [
		['name' => 'N301', 'required' => true],
		['name' => 'N302', 'required' => false],
	];

	static protected $elementNames = [
		'N301' => 'Address Information',
		'N302' => 'Address Information',
	];

}