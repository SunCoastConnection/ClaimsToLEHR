<?php

namespace SunCoastConnection\ClaimsToOEMR\X12N837\Segment;

use \SunCoastConnection\ClaimsToOEMR\Document\Segment;

class MOA extends Segment {

	static protected $elementSequence = [
		['name' => 'MOA01', 'required' => false],
		['name' => 'MOA02', 'required' => false],
		['name' => 'MOA03', 'required' => false],
		['name' => 'MOA04', 'required' => false],
		['name' => 'MOA05', 'required' => false],
		['name' => 'MOA06', 'required' => false],
		['name' => 'MOA07', 'required' => false],
		['name' => 'MOA08', 'required' => false],
		['name' => 'MOA09', 'required' => false],
	];

	static protected $elementNames = [
		'MOA01' => 'Percentage as Decimal',
		'MOA02' => 'Monetary Amount',
		'MOA03' => 'Reference Identification',
		'MOA04' => 'Reference Identification',
		'MOA05' => 'Reference Identification',
		'MOA06' => 'Reference Identification',
		'MOA07' => 'Reference Identification',
		'MOA08' => 'Monetary Amount',
		'MOA09' => 'Monetary Amount',
	];

}