<?php

namespace SunCoastConnection\ClaimsToOEMR\X12N837\Segment;

use \SunCoastConnection\ClaimsToOEMR\Document\Raw\Segment;

class CTP extends Segment {

	static protected $elementSequence = [
		['name' => 'CTP01', 'required' => true],
		['name' => 'CTP02', 'required' => true],
		['name' => 'CTP03', 'required' => true],
		['name' => 'CTP04', 'required' => true],
		['name' => 'CTP05', 'required' => true],
		['name' => 'CTP06', 'required' => true],
		['name' => 'CTP07', 'required' => true],
		['name' => 'CTP08', 'required' => true],
		['name' => 'CTP09', 'required' => true],
		['name' => 'CTP10', 'required' => true],
		['name' => 'CTP11', 'required' => true],
	];

	static protected $elementNames = [
		'CTP01' => 'Class of Trade Code',
		'CTP02' => 'Price Identifier Code',
		'CTP03' => 'Unit Price',
		'CTP04' => 'Quantity',
		'CTP05' => 'Composite Unit of Measure',
		'CTP06' => 'Price Multiplier Qualifier',
		'CTP07' => 'Multiplier',
		'CTP08' => 'Monetary Amount',
		'CTP09' => 'Basis of Unit Price Code',
		'CTP10' => 'Condition Value',
		'CTP11' => 'Multiple Price Quantity',
	];

}