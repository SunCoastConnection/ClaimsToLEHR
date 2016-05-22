<?php

namespace SunCoastConnection\ClaimsToOEMR\X12N837\Segment;

use \SunCoastConnection\ClaimsToOEMR\Document\Segment;

class SV1 extends Segment {

	static protected $elementSequence = [
		['name' => 'SV101', 'required' => true],
		['name' => 'SV102', 'required' => false],
		['name' => 'SV103', 'required' => false],
		['name' => 'SV104', 'required' => false],
		['name' => 'SV105', 'required' => false],
		['name' => 'SV106', 'required' => false],
		['name' => 'SV107', 'required' => false],
		['name' => 'SV108', 'required' => false],
		['name' => 'SV109', 'required' => false],
		['name' => 'SV110', 'required' => false],
		['name' => 'SV111', 'required' => false],
		['name' => 'SV112', 'required' => false],
		['name' => 'SV113', 'required' => false],
		['name' => 'SV114', 'required' => false],
		['name' => 'SV115', 'required' => false],
		['name' => 'SV116', 'required' => false],
		['name' => 'SV117', 'required' => false],
		['name' => 'SV118', 'required' => false],
		['name' => 'SV119', 'required' => false],
		['name' => 'SV120', 'required' => false],
		['name' => 'SV121', 'required' => false],
	];

	static protected $elementNames = [
		'SV101' => 'Composite Medical Procedure Identifier',
		'SV102' => 'Monetary Amount',
		'SV103' => 'Unit or Basis for Measurement Code',
		'SV104' => 'Quantity',
		'SV105' => 'Facility Code Value',
		'SV106' => 'Service Type Code',
		'SV107' => 'Composite Diagnosis Code Pointer',
		'SV108' => 'Monetary Amount',
		'SV109' => 'Yes/No Condition or Response Code',
		'SV110' => 'Multiple Procedure Code',
		'SV111' => 'Yes/No Condition or Response Code',
		'SV112' => 'Yes/No Condition or Response Code',
		'SV113' => 'Review Code',
		'SV114' => 'National or Local Assigned Review Value',
		'SV115' => 'Copay Status Code',
		'SV116' => 'Health Care Professional Shortage Area Code',
		'SV117' => 'Reference Identification',
		'SV118' => 'Postal Code',
		'SV119' => 'Monetary Amount',
		'SV120' => 'Level of Care Code',
		'SV121' => 'Provider Agreement Code',
	];

}