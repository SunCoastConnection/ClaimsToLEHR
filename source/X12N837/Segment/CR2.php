<?php

namespace SunCoastConnection\ClaimsToEMR\X12N837\Segment;

use \SunCoastConnection\ClaimsToEMR\Document\Raw\Segment;

class CR2 extends Segment {

	static protected $elementSequence = [
		['name' => 'CR201', 'required' => false],
		['name' => 'CR202', 'required' => false],
		['name' => 'CR203', 'required' => false],
		['name' => 'CR204', 'required' => false],
		['name' => 'CR205', 'required' => false],
		['name' => 'CR206', 'required' => false],
		['name' => 'CR207', 'required' => false],
		['name' => 'CR208', 'required' => false],
		['name' => 'CR209', 'required' => false],
		['name' => 'CR210', 'required' => false],
		['name' => 'CR211', 'required' => false],
		['name' => 'CR212', 'required' => false],
	];

	static protected $elementNames = [
		'CR201' => 'Count',
		'CR202' => 'Quantity',
		'CR203' => 'Subluxation Level Code',
		'CR204' => 'Subluxation Level Code',
		'CR205' => 'Unit or Basis for Measurement Code',
		'CR206' => 'Quantity',
		'CR207' => 'Quantity',
		'CR208' => 'Nature of Condition Code',
		'CR209' => 'Yes/No Condition or Response Code',
		'CR210' => 'Description',
		'CR211' => 'Description',
		'CR212' => 'Yes/No Condition or Response Code',
	];

}