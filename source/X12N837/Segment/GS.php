<?php

namespace SunCoastConnection\ClaimsToOEMR\X12N837\Segment;

use \SunCoastConnection\ClaimsToOEMR\Document\Segment;

class GS extends Segment {

	static protected $elementSequence = [
		['name' => 'GS01', 'required' => true],
		['name' => 'GS02', 'required' => true],
		['name' => 'GS03', 'required' => true],
		['name' => 'GS04', 'required' => true],
		['name' => 'GS05', 'required' => true],
		['name' => 'GS06', 'required' => true],
		['name' => 'GS07', 'required' => true],
		['name' => 'GS08', 'required' => true],
	];

	static protected $elementNames = [
		'GS01' => 'Functional Identifier Code',
		'GS02' => 'Application Senders Code',
		'GS03' => 'Application Receivers Code',
		'GS04' => 'Date',
		'GS05' => 'Time',
		'GS06' => 'Group Control Number',
		'GS07' => 'Responsible Agency Code',
		'GS08' => 'Version Release Industry Identifier Code',
	];

}