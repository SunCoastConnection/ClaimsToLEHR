<?php

namespace SunCoastConnection\ClaimsToOEMR\X12N837\Segment;

use \SunCoastConnection\ClaimsToOEMR\Document\Raw\Segment;

class CUR extends Segment {

	static protected $elementSequence = [
		['name' => 'CUR01', 'required' => true],
		['name' => 'CUR02', 'required' => true],
		['name' => 'CUR03', 'required' => false],
		['name' => 'CUR04', 'required' => false],
		['name' => 'CUR05', 'required' => false],
		['name' => 'CUR06', 'required' => false],
		['name' => 'CUR07', 'required' => false],
		['name' => 'CUR08', 'required' => false],
		['name' => 'CUR09', 'required' => false],
		['name' => 'CUR10', 'required' => false],
		['name' => 'CUR11', 'required' => false],
		['name' => 'CUR12', 'required' => false],
		['name' => 'CUR13', 'required' => false],
		['name' => 'CUR14', 'required' => false],
		['name' => 'CUR15', 'required' => false],
		['name' => 'CUR16', 'required' => false],
		['name' => 'CUR17', 'required' => false],
		['name' => 'CUR18', 'required' => false],
		['name' => 'CUR19', 'required' => false],
		['name' => 'CUR20', 'required' => false],
		['name' => 'CUR21', 'required' => false],
	];

	static protected $elementNames = [
		'CUR01' => 'Entity Identifier Code',
		'CUR02' => 'Currency Code',
		'CUR03' => 'Exchange Rate',
		'CUR04' => 'Entity Identifier Code',
		'CUR05' => 'Currency Code',
		'CUR06' => 'Currency Market/Exchange Code',
		'CUR07' => 'Date/Time Qualifier',
		'CUR08' => 'Date',
		'CUR09' => 'Time',
		'CUR10' => 'Date/Time Qualifier',
		'CUR11' => 'Date',
		'CUR12' => 'Time',
		'CUR13' => 'Date/Time Qualifier',
		'CUR14' => 'Date',
		'CUR15' => 'Time',
		'CUR16' => 'Date/Time Qualifier',
		'CUR17' => 'Date',
		'CUR18' => 'Time',
		'CUR19' => 'Date/Time Qualifier',
		'CUR20' => 'Date',
		'CUR21' => 'Time',
	];

}