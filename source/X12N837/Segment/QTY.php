<?php

namespace SunCoastConnection\ClaimsToOEMR\X12N837\Segment;

use \SunCoastConnection\ClaimsToOEMR\Document\Segment;

class QTY extends Segment {

	static protected $elementSequence = [
		['name' => 'QTY01', 'required' => true],
		['name' => 'QTY02', 'required' => false],
		['name' => 'QTY03', 'required' => false],
		['name' => 'QTY04', 'required' => false],
	];

	static protected $elementNames = [
		'QTY01' => 'Quantity Qualifier',
		'QTY02' => 'Quantity',
		'QTY03' => 'Composite Unit of Measure',
		'QTY04' => 'Free-form Information',
	];

}