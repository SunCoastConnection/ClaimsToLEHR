<?php

namespace SunCoastConnection\ClaimsToEMR\X12N837\Segment;

use \SunCoastConnection\ClaimsToEMR\Document\Raw\Segment;

class PER extends Segment {

	static protected $elementSequence = [
		['name' => 'PER01', 'required' => true],
		['name' => 'PER02', 'required' => false],
		['name' => 'PER03', 'required' => false],
		['name' => 'PER04', 'required' => false],
		['name' => 'PER05', 'required' => false],
		['name' => 'PER06', 'required' => false],
		['name' => 'PER07', 'required' => false],
		['name' => 'PER08', 'required' => false],
		['name' => 'PER09', 'required' => false],
	];

	static protected $elementNames = [
		'PER01' => 'Contact Function Code',
		'PER02' => 'Name',
		'PER03' => 'Communication Number Qualifier',
		'PER04' => 'Communication Number',
		'PER05' => 'Communication Number Qualifier',
		'PER06' => 'Communication Number',
		'PER07' => 'Communication Number Qualifier',
		'PER08' => 'Communication Number',
		'PER09' => 'Contact Inquiry Reference',
	];

}