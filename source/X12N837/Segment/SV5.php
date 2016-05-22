<?php

namespace SunCoastConnection\ClaimsToOEMR\X12N837\Segment;

use \SunCoastConnection\ClaimsToOEMR\Document\Segment;

class SV5 extends Segment {

	static protected $elementSequence = [
		['name' => 'SV501', 'required' => true],
		['name' => 'SV502', 'required' => true],
		['name' => 'SV503', 'required' => true],
		['name' => 'SV504', 'required' => false],
		['name' => 'SV505', 'required' => false],
		['name' => 'SV506', 'required' => false],
		['name' => 'SV507', 'required' => false],
	];

	static protected $elementNames = [
		'SV501' => 'Composite Medical Procedure Identifier',
		'SV502' => 'Unit or Basis for Measurement Code',
		'SV503' => 'Quantity',
		'SV504' => 'Monetary Amount',
		'SV505' => 'Monetary Amount',
		'SV506' => 'Frequency Code',
		'SV507' => 'Prognosis Code',
	];

}