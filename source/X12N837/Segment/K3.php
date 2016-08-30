<?php

namespace SunCoastConnection\ClaimsToOEMR\X12N837\Segment;

use \SunCoastConnection\ClaimsToOEMR\Document\Raw\Segment;

class K3 extends Segment {

	static protected $elementSequence = [
		['name' => 'K301', 'required' => true],
		['name' => 'K302', 'required' => false],
		['name' => 'K303', 'required' => false],
	];

	static protected $elementNames = [
		'K301' => 'Fixed Format Information',
		'K302' => 'Record Format Code',
		'K303' => 'Composite Unit of Measure',
	];

}