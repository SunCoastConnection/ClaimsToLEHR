<?php

namespace SunCoastConnection\ClaimsToOEMR\X12N837\Segment;

use \SunCoastConnection\ClaimsToOEMR\Document\Segment;

class CN1 extends Segment {

	static protected $elementSequence = [
		['name' => 'CN101', 'required' => true],
		['name' => 'CN102', 'required' => false],
		['name' => 'CN103', 'required' => false],
		['name' => 'CN104', 'required' => false],
		['name' => 'CN105', 'required' => false],
		['name' => 'CN106', 'required' => false],
	];

	static protected $elementNames = [
		'CN101' => 'Contract Type Code',
		'CN102' => 'Monetary Amount',
		'CN103' => 'Percent, Decimal Format',
		'CN104' => 'Reference Identification',
		'CN105' => 'Terms Discount Percent',
		'CN106' => 'Version Identifier',
	];

}