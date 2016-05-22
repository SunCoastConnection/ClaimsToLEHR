<?php

namespace SunCoastConnection\ClaimsToOEMR\X12N837\Segment;

use \SunCoastConnection\ClaimsToOEMR\Document\Segment;

class BHT extends Segment {

	static protected $elementSequence = [
		['name' => 'BHT01', 'required' => true],
		['name' => 'BHT02', 'required' => true],
		['name' => 'BHT03', 'required' => false],
		['name' => 'BHT04', 'required' => false],
		['name' => 'BHT05', 'required' => false],
		['name' => 'BHT06', 'required' => false],
	];

	static protected $elementNames = [
		'BHT01' => 'Hierarchical Structure Code',
		'BHT02' => 'Transaction Set Purpose Code',
		'BHT03' => 'Reference Identification',
		'BHT04' => 'Date',
		'BHT05' => 'Time',
		'BHT06' => 'Transaction Type Code',
	];

}