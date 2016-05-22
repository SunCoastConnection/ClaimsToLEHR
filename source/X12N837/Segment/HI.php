<?php

namespace SunCoastConnection\ClaimsToOEMR\X12N837\Segment;

use \SunCoastConnection\ClaimsToOEMR\Document\Segment;

class HI extends Segment {

	static protected $elementSequence = [
		['name' => 'HI01', 'required' => true],
		['name' => 'HI02', 'required' => false],
		['name' => 'HI03', 'required' => false],
		['name' => 'HI04', 'required' => false],
		['name' => 'HI05', 'required' => false],
		['name' => 'HI06', 'required' => false],
		['name' => 'HI07', 'required' => false],
		['name' => 'HI08', 'required' => false],
		['name' => 'HI09', 'required' => false],
		['name' => 'HI10', 'required' => false],
		['name' => 'HI11', 'required' => false],
		['name' => 'HI12', 'required' => false],
	];

	static protected $elementNames = [
		'HI01' => 'Health Care Code Information',
		'HI02' => 'Health Care Code Information',
		'HI03' => 'Health Care Code Information',
		'HI04' => 'Health Care Code Information',
		'HI05' => 'Health Care Code Information',
		'HI06' => 'Health Care Code Information',
		'HI07' => 'Health Care Code Information',
		'HI08' => 'Health Care Code Information',
		'HI09' => 'Health Care Code Information',
		'HI10' => 'Health Care Code Information',
		'HI11' => 'Health Care Code Information',
		'HI12' => 'Health Care Code Information',
	];

}