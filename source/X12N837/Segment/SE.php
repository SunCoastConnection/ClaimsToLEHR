<?php

namespace SunCoastConnection\ClaimsToOEMR\X12N837\Segment;

use \SunCoastConnection\ClaimsToOEMR\Document\Raw\Segment;

class SE extends Segment {

	static protected $elementSequence = [
		['name' => 'SE01', 'required' => true],
		['name' => 'SE02', 'required' => true],
	];

	static protected $elementNames = [
		'SE01' => 'Number of Included Segments',
		'SE02' => 'Transaction Set Control Number',
	];

}