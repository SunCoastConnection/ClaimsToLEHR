<?php

namespace SunCoastConnection\ClaimsToOEMR\X12N837\Segment;

use \SunCoastConnection\ClaimsToOEMR\Document\Segment;

class FRM extends Segment {

	static protected $elementSequence = [
		['name' => 'FRM01', 'required' => true],
		['name' => 'FRM02', 'required' => false],
		['name' => 'FRM03', 'required' => false],
		['name' => 'FRM04', 'required' => false],
		['name' => 'FRM05', 'required' => false],
	];

	static protected $elementNames = [
		'FRM01' => 'Assigned Identification',
		'FRM02' => 'Yes/No Condition or Response Code',
		'FRM03' => 'Reference Identification',
		'FRM04' => 'Date',
		'FRM05' => 'Percent, Decimal Format',
	];

}