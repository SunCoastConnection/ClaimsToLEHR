<?php

namespace SunCoastConnection\ClaimsToOEMR\X12N837\Segment;

use \SunCoastConnection\ClaimsToOEMR\Document\Raw\Segment;

class N4 extends Segment {

	static protected $elementSequence = [
		['name' => 'N401', 'required' => false],
		['name' => 'N402', 'required' => false],
		['name' => 'N403', 'required' => false],
		['name' => 'N404', 'required' => false],
		['name' => 'N405', 'required' => false],
		['name' => 'N406', 'required' => false],
		['name' => 'N407', 'required' => false],
	];

	static protected $elementNames = [
		'N401' => 'City Name',
		'N402' => 'State or Province Code',
		'N403' => 'Postal Code',
		'N404' => 'Country Code',
		'N405' => 'Location Qualifier',
		'N406' => 'Location Identifier',
		'N407' => 'Country Subdivision Code',
	];

}