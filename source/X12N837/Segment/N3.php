<?php

namespace SunCoastConnection\ClaimsToOEMR\X12N837\Segment;

use \SunCoastConnection\ClaimsToOEMR\Document\Segment;

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