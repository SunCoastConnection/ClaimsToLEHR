<?php

namespace SunCoastConnection\ClaimsToOEMR\X12N837\Segment;

use \SunCoastConnection\ClaimsToOEMR\Document\Segment;

class CR1 extends Segment {

	static protected $elementSequence = [
		['name' => 'CR101', 'required' => false],
		['name' => 'CR102', 'required' => false],
		['name' => 'CR103', 'required' => false],
		['name' => 'CR104', 'required' => false],
		['name' => 'CR105', 'required' => false],
		['name' => 'CR106', 'required' => false],
		['name' => 'CR107', 'required' => false],
		['name' => 'CR108', 'required' => false],
		['name' => 'CR109', 'required' => false],
		['name' => 'CR110', 'required' => false],
	];

	static protected $elementNames = [
		'CR101' => 'Unit or Basis for Measurement Code',
		'CR102' => 'Weight',
		'CR103' => 'Ambulance Transport Code',
		'CR104' => 'Ambulance Transport Reason Code',
		'CR105' => 'Unit or Basis for Measurement Code',
		'CR106' => 'Quantity',
		'CR107' => 'Address Information',
		'CR108' => 'Address Information',
		'CR109' => 'Description',
		'CR110' => 'Description',
	];

}