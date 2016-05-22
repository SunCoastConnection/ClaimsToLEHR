<?php

namespace SunCoastConnection\ClaimsToOEMR\X12N837\Segment;

use \SunCoastConnection\ClaimsToOEMR\Document\Segment;

class CRC extends Segment {

	static protected $elementSequence = [
		['name' => 'CRC01', 'required' => true],
		['name' => 'CRC02', 'required' => true],
		['name' => 'CRC03', 'required' => true],
		['name' => 'CRC04', 'required' => false],
		['name' => 'CRC05', 'required' => false],
		['name' => 'CRC06', 'required' => false],
		['name' => 'CRC07', 'required' => false],
	];

	static protected $elementNames = [
		'CRC01' => 'Code Category',
		'CRC02' => 'Yes/No Condition or Response Code',
		'CRC03' => 'Condition Indicator',
		'CRC04' => 'Condition Indicator',
		'CRC05' => 'Condition Indicator',
		'CRC06' => 'Condition Indicator',
		'CRC07' => 'Condition Indicator',
	];

}