<?php

namespace SunCoastConnection\ClaimsToOEMR\X12N837\Segment;

use \SunCoastConnection\ClaimsToOEMR\Document\Raw\Segment;

class DMG extends Segment {

	static protected $elementSequence = [
		['name' => 'DMG01', 'required' => false],
		['name' => 'DMG02', 'required' => false],
		['name' => 'DMG03', 'required' => false],
		['name' => 'DMG04', 'required' => false],
		['name' => 'DMG05', 'required' => false],
		['name' => 'DMG06', 'required' => false],
		['name' => 'DMG07', 'required' => false],
		['name' => 'DMG08', 'required' => false],
		['name' => 'DMG09', 'required' => false],
		['name' => 'DMG10', 'required' => false],
		['name' => 'DMG11', 'required' => false],
	];

	static protected $elementNames = [
		'DMG01' => 'Date Time Period Format Qualifier',
		'DMG02' => 'Date Time Period',
		'DMG03' => 'Gender Code',
		'DMG04' => 'Marital Status Code',
		'DMG05' => 'Composite Race or Ethnicity Information',
		'DMG06' => 'Citizenship Status Code',
		'DMG07' => 'Country Code',
		'DMG08' => 'Basis of Verification Code',
		'DMG09' => 'Quantity',
		'DMG10' => 'Code List Qualifier Code',
		'DMG11' => 'Industry Code',
	];

}