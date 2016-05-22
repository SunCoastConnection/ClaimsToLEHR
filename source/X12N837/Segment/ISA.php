<?php

namespace SunCoastConnection\ClaimsToOEMR\X12N837\Segment;

use \SunCoastConnection\ClaimsToOEMR\Document\Segment;

class ISA extends Segment {

	static protected $elementSequence = [
		['name' => 'ISA01', 'required' => true],
		['name' => 'ISA02', 'required' => true],
		['name' => 'ISA03', 'required' => true],
		['name' => 'ISA04', 'required' => true],
		['name' => 'ISA05', 'required' => true],
		['name' => 'ISA06', 'required' => true],
		['name' => 'ISA07', 'required' => true],
		['name' => 'ISA08', 'required' => true],
		['name' => 'ISA09', 'required' => true],
		['name' => 'ISA10', 'required' => true],
		['name' => 'ISA11', 'required' => true],
		['name' => 'ISA12', 'required' => true],
		['name' => 'ISA13', 'required' => true],
		['name' => 'ISA14', 'required' => true],
		['name' => 'ISA15', 'required' => true],
		['name' => 'ISA16', 'required' => true],
	];

	static protected $elementNames = [
		'ISA01' => 'Authorization Information Qualifier',
		'ISA02' => 'Authorization Information',
		'ISA03' => 'Security Information Qualifier',
		'ISA04' => 'Security Information',
		'ISA05' => 'Interchange ID Qualifier',
		'ISA06' => 'Interchange Sender ID',
		'ISA07' => 'Interchange ID Qualifier',
		'ISA08' => 'Interchange Receiver ID',
		'ISA09' => 'Interchange Date',
		'ISA10' => 'Interchange Time',
		'ISA11' => 'Repetition Separator',
		'ISA12' => 'Interchange Control Version Number',
		'ISA13' => 'Interchange Control Number',
		'ISA14' => 'Acknowledgment Requested',
		'ISA15' => 'Interchange Usage Indicator',
		'ISA16' => 'Component Element Separator',
	];

}