<?php

namespace SunCoastConnection\ClaimsToOEMR\X12N837\Segment;

use \SunCoastConnection\ClaimsToOEMR\Document\Segment;

class ST extends Segment {

	static protected $elementSequence = [
		['name' => 'ST01', 'required' => true],
		['name' => 'ST02', 'required' => true],
		['name' => 'ST03', 'required' => false],
	];

	static protected $elementNames = [
		'ST01' => 'Transaction Set Identifier Code',
		'ST02' => 'Transaction Set Control Numbe',
		'ST03' => 'Implementation Convention Reference',
	];

}