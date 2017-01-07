<?php

namespace SunCoastConnection\ClaimsToEMR\X12N837\Segment;

use \SunCoastConnection\ClaimsToEMR\Document\Raw\Segment;

class SVD extends Segment {

	static protected $elementSequence = [
		['name' => 'SVD01', 'required' => true],
		['name' => 'SVD02', 'required' => true],
		['name' => 'SVD03', 'required' => false],
		['name' => 'SVD04', 'required' => false],
		['name' => 'SVD05', 'required' => false],
		['name' => 'SVD06', 'required' => false],
	];

	static protected $elementNames = [
		'SVD01' => 'Identification Code',
		'SVD02' => 'Monetary Amount',
		'SVD03' => 'Composite Medical Procedure Identifier',
		'SVD04' => 'Product/Service ID',
		'SVD05' => 'Quantity',
		'SVD06' => 'Assigned Number',
	];

}