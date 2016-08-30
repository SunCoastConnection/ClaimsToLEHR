<?php

namespace SunCoastConnection\ClaimsToOEMR\X12N837\Segment;

use \SunCoastConnection\ClaimsToOEMR\Document\Raw\Segment;

class NM1 extends Segment {

	static protected $elementSequence = [
		['name' => 'NM101', 'required' => true],
		['name' => 'NM102', 'required' => true],
		['name' => 'NM103', 'required' => true],
		['name' => 'NM104', 'required' => true],
		['name' => 'NM105', 'required' => true],
		['name' => 'NM106', 'required' => true],
		['name' => 'NM107', 'required' => true],
		['name' => 'NM108', 'required' => true],
		['name' => 'NM109', 'required' => true],
		['name' => 'NM110', 'required' => true],
		['name' => 'NM111', 'required' => true],
		['name' => 'NM112', 'required' => true],
	];

	static protected $elementNames = [
		'NM101' => 'Entity Identifier Code',
		'NM102' => 'Entity Type Qualifier',
		'NM103' => 'Name Last or Organization Name',
		'NM104' => 'Name First',
		'NM105' => 'Name Middle',
		'NM106' => 'Name Prefix',
		'NM107' => 'Name Suffix',
		'NM108' => 'Identification Code Qualifier',
		'NM109' => 'Identification Code',
		'NM110' => 'Entity Relationship Code',
		'NM111' => 'Entity Identifier Code',
		'NM112' => 'Name Last or Organization Name',
	];

}