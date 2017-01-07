<?php

namespace SunCoastConnection\ClaimsToEMR\X12N837\Segment;

use \SunCoastConnection\ClaimsToEMR\Document\Raw\Segment;

class LX extends Segment {

	static protected $elementSequence = [
		['name' => 'LX01', 'required' => true],
	];

	static protected $elementNames = [
		'LX01' => 'Assigned Number',
	];

}