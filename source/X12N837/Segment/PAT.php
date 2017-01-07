<?php

namespace SunCoastConnection\ClaimsToEMR\X12N837\Segment;

use \SunCoastConnection\ClaimsToEMR\Document\Raw\Segment;

class PAT extends Segment {

	static protected $elementSequence = [
		['name' => 'PAT01', 'required' => false],
		['name' => 'PAT02', 'required' => false],
		['name' => 'PAT03', 'required' => false],
		['name' => 'PAT04', 'required' => false],
		['name' => 'PAT05', 'required' => false],
		['name' => 'PAT06', 'required' => false],
		['name' => 'PAT07', 'required' => false],
		['name' => 'PAT08', 'required' => false],
		['name' => 'PAT09', 'required' => false],
	];

	static protected $elementNames = [
		'PAT01' => 'Individual Relationship Code',
		'PAT02' => 'Patient Location Code',
		'PAT03' => 'Employment Status Code',
		'PAT04' => 'Student Status Code',
		'PAT05' => 'Date Time Period Format Qualifier',
		'PAT06' => 'Date Time Period',
		'PAT07' => 'Unit or Basis for Measurement Code',
		'PAT08' => 'Weight',
		'PAT09' => 'Yes/No Condition or Response Code',
	];

}