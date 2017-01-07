<?php

namespace SunCoastConnection\ClaimsToEMR\X12N837\Segment;

use \SunCoastConnection\ClaimsToEMR\Document\Raw\Segment;

class SBR extends Segment {

	static protected $elementSequence = [
		['name' => 'SBR01', 'required' => true],
		['name' => 'SBR02', 'required' => false],
		['name' => 'SBR03', 'required' => false],
		['name' => 'SBR04', 'required' => false],
		['name' => 'SBR05', 'required' => false],
		['name' => 'SBR06', 'required' => false],
		['name' => 'SBR07', 'required' => false],
		['name' => 'SBR08', 'required' => false],
		['name' => 'SBR09', 'required' => false],
	];

	static protected $elementNames = [
		'SBR01' => 'Payer Responsibility Sequence Number Code',
		'SBR02' => 'Individual Relationship Code',
		'SBR03' => 'Reference Identification',
		'SBR04' => 'Name',
		'SBR05' => 'Insurance Type Code',
		'SBR06' => 'Coordination of Benefits Code',
		'SBR07' => 'Yes/No Condition or Response Code',
		'SBR08' => 'Employment Status Code',
		'SBR09' => 'Claim Filing Indicator Code',
	];

}