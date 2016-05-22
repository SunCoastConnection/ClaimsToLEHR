<?php

namespace SunCoastConnection\ClaimsToOEMR\X12N837\Segment;

use \SunCoastConnection\ClaimsToOEMR\Document\Segment;

class CR3 extends Segment {

	static protected $elementSequence = [
		['name' => 'CR301', 'required' => false],
		['name' => 'CR302', 'required' => false],
		['name' => 'CR303', 'required' => false],
		['name' => 'CR304', 'required' => false],
		['name' => 'CR305', 'required' => false],
	];

	static protected $elementNames = [
		'CR301' => 'Certification Type Code',
		'CR302' => 'Unit or Basis for Measurement Code',
		'CR303' => 'Quantity',
		'CR304' => 'Insulin Dependent Code',
		'CR305' => 'Description',
	];

}