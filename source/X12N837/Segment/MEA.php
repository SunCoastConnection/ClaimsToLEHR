<?php

namespace SunCoastConnection\ClaimsToOEMR\X12N837\Segment;

use \SunCoastConnection\ClaimsToOEMR\Document\Raw\Segment;

class MEA extends Segment {

	static protected $elementSequence = [
		['name' => 'MEA01', 'required' => false],
		['name' => 'MEA02', 'required' => false],
		['name' => 'MEA03', 'required' => false],
		['name' => 'MEA04', 'required' => false],
		['name' => 'MEA05', 'required' => false],
		['name' => 'MEA06', 'required' => false],
		['name' => 'MEA07', 'required' => false],
		['name' => 'MEA08', 'required' => false],
		['name' => 'MEA09', 'required' => false],
		['name' => 'MEA10', 'required' => false],
		['name' => 'MEA11', 'required' => false],
		['name' => 'MEA12', 'required' => false],
	];

	static protected $elementNames = [
		'MEA01' => 'Measurement Reference ID Code',
		'MEA02' => 'Measurement Qualifier',
		'MEA03' => 'Measurement Value',
		'MEA04' => 'Composite Unit of Measure',
		'MEA05' => 'Range Minimum',
		'MEA06' => 'Range Maximum',
		'MEA07' => 'Measurement Significance Code',
		'MEA08' => 'Measurement Attribute Code',
		'MEA09' => 'Surface/Layer/Position Code',
		'MEA10' => 'Measurement Method or Device',
		'MEA11' => 'Code List Qualifier Code',
		'MEA12' => 'Industry Code',
	];

}