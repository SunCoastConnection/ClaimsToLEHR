<?php

namespace SunCoastConnection\ClaimsToOEMR\X12N837\Segment;

use \SunCoastConnection\ClaimsToOEMR\Document\Segment;

class LX extends Segment {

	static protected $elementSequence = [
		['name' => 'LX01', 'required' => true],
	];

	static protected $elementNames = [
		'LX01' => 'Assigned Number',
	];

}