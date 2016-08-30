<?php

namespace SunCoastConnection\ClaimsToOEMR\X12N837\Segment;

use \SunCoastConnection\ClaimsToOEMR\Document\Raw\Segment;

class HL extends Segment {

	static protected $elementSequence = [
		['name' => 'HL01', 'required' => true],
		['name' => 'HL02', 'required' => false],
		['name' => 'HL03', 'required' => true],
		['name' => 'HL04', 'required' => false],
	];

	static protected $elementNames = [
		'HL01' => 'Hierarchical ID Number',
		'HL02' => 'Hierarchical Parent ID Number',
		'HL03' => 'Hierarchical Level Code',
		'HL04' => 'Hierarchical Child Code',
	];

}