<?php

namespace SunCoastConnection\ClaimsToEMR\X12N837\Segment;

use \SunCoastConnection\ClaimsToEMR\Document\Raw\Segment;

class CAS extends Segment {

	static protected $elementSequence = [
		['name' => 'CAS01', 'required' => true],
		['name' => 'CAS02', 'required' => true],
		['name' => 'CAS03', 'required' => true],
		['name' => 'CAS04', 'required' => false],
		['name' => 'CAS05', 'required' => false],
		['name' => 'CAS06', 'required' => false],
		['name' => 'CAS07', 'required' => false],
		['name' => 'CAS08', 'required' => false],
		['name' => 'CAS09', 'required' => false],
		['name' => 'CAS10', 'required' => false],
		['name' => 'CAS11', 'required' => false],
		['name' => 'CAS12', 'required' => false],
		['name' => 'CAS13', 'required' => false],
		['name' => 'CAS14', 'required' => false],
		['name' => 'CAS15', 'required' => false],
		['name' => 'CAS16', 'required' => false],
		['name' => 'CAS17', 'required' => false],
		['name' => 'CAS18', 'required' => false],
		['name' => 'CAS19', 'required' => false],
	];

	static protected $elementNames = [
		'CAS01' => 'Claim Adjustment Group Code',
		'CAS02' => 'Claim Adjustment Reason Code',
		'CAS03' => 'Monetary Amount',
		'CAS04' => 'Quantity',
		'CAS05' => 'Claim Adjustment Reason Code',
		'CAS06' => 'Monetary Amount',
		'CAS07' => 'Quantity',
		'CAS08' => 'Claim Adjustment Reason Code',
		'CAS09' => 'Monetary Amount',
		'CAS10' => 'Quantity',
		'CAS11' => 'Claim Adjustment Reason Code',
		'CAS12' => 'Monetary Amount',
		'CAS13' => 'Quantity',
		'CAS14' => 'Claim Adjustment Reason Code',
		'CAS15' => 'Monetary Amount',
		'CAS16' => 'Quantity',
		'CAS17' => 'Claim Adjustment Reason Code',
		'CAS18' => 'Monetary Amount',
		'CAS19' => 'Quantity',
	];

}