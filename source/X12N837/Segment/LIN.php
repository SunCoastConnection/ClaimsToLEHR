<?php

namespace SunCoastConnection\ClaimsToEMR\X12N837\Segment;

use \SunCoastConnection\ClaimsToEMR\Document\Raw\Segment;

class LIN extends Segment {

	static protected $elementSequence = [
		['name' => 'LIN01', 'required' => false],
		['name' => 'LIN02', 'required' => true],
		['name' => 'LIN03', 'required' => true],
		['name' => 'LIN04', 'required' => false],
		['name' => 'LIN05', 'required' => false],
		['name' => 'LIN06', 'required' => false],
		['name' => 'LIN07', 'required' => false],
		['name' => 'LIN08', 'required' => false],
		['name' => 'LIN09', 'required' => false],
		['name' => 'LIN10', 'required' => false],
		['name' => 'LIN11', 'required' => false],
		['name' => 'LIN12', 'required' => false],
		['name' => 'LIN13', 'required' => false],
		['name' => 'LIN14', 'required' => false],
		['name' => 'LIN15', 'required' => false],
		['name' => 'LIN16', 'required' => false],
		['name' => 'LIN17', 'required' => false],
		['name' => 'LIN18', 'required' => false],
		['name' => 'LIN19', 'required' => false],
		['name' => 'LIN20', 'required' => false],
		['name' => 'LIN21', 'required' => false],
		['name' => 'LIN22', 'required' => false],
		['name' => 'LIN23', 'required' => false],
		['name' => 'LIN24', 'required' => false],
		['name' => 'LIN25', 'required' => false],
		['name' => 'LIN26', 'required' => false],
		['name' => 'LIN27', 'required' => false],
		['name' => 'LIN28', 'required' => false],
		['name' => 'LIN29', 'required' => false],
		['name' => 'LIN30', 'required' => false],
		['name' => 'LIN31', 'required' => false],
	];

	static protected $elementNames = [
		'LIN01' => 'Assigned Identification',
		'LIN02' => 'Product/Service ID Qualifier',
		'LIN03' => 'Product/Service ID',
		'LIN04' => 'Product/Service ID Qualifier',
		'LIN05' => 'Product/Service ID',
		'LIN06' => 'Product/Service ID Qualifier',
		'LIN07' => 'Product/Service ID',
		'LIN08' => 'Product/Service ID Qualifier',
		'LIN09' => 'Product/Service ID',
		'LIN10' => 'Product/Service ID Qualifier',
		'LIN11' => 'Product/Service ID',
		'LIN12' => 'Product/Service ID Qualifier',
		'LIN13' => 'Product/Service ID',
		'LIN14' => 'Product/Service ID Qualifier',
		'LIN15' => 'Product/Service ID',
		'LIN16' => 'Product/Service ID Qualifier',
		'LIN17' => 'Product/Service ID',
		'LIN18' => 'Product/Service ID Qualifier',
		'LIN19' => 'Product/Service ID',
		'LIN20' => 'Product/Service ID Qualifier',
		'LIN21' => 'Product/Service ID',
		'LIN22' => 'Product/Service ID Qualifier',
		'LIN23' => 'Product/Service ID',
		'LIN24' => 'Product/Service ID Qualifier',
		'LIN25' => 'Product/Service ID',
		'LIN26' => 'Product/Service ID Qualifier',
		'LIN27' => 'Product/Service ID',
		'LIN28' => 'Product/Service ID Qualifier',
		'LIN29' => 'Product/Service ID',
		'LIN30' => 'Product/Service ID Qualifier',
		'LIN31' => 'Product/Service ID',
	];

}