<?php

namespace SunCoastConnection\ClaimsToEMR\X12N837\Segment;

use \SunCoastConnection\ClaimsToEMR\Document\Raw\Segment;

class REF extends Segment {

	static protected $elementSequence = [
		['name' => 'REF01', 'required' => true],
		['name' => 'REF02', 'required' => true],
		['name' => 'REF03', 'required' => false],
		['name' => 'REF04', 'required' => false],
	];

	static protected $elementNames = [
		'REF01' => 'Reference Identification Qualifier',
		'REF02' => 'Reference Identification',
		'REF03' => 'Description',
		'REF04' => 'Reference Identifier',
	];

}