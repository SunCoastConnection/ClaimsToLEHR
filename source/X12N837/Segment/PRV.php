<?php

namespace SunCoastConnection\ClaimsToOEMR\X12N837\Segment;

use \SunCoastConnection\ClaimsToOEMR\Document\Segment;

class PRV extends Segment {

	static protected $elementSequence = [
		['name' => 'PRV01', 'required' => true],
		['name' => 'PRV02', 'required' => false],
		['name' => 'PRV03', 'required' => false],
		['name' => 'PRV04', 'required' => false],
		['name' => 'PRV05', 'required' => false],
		['name' => 'PRV06', 'required' => false],
	];

	static protected $elementNames = [
		'PRV01' => 'Provider Code',
		'PRV02' => 'Reference Identification Qualifier',
		'PRV03' => 'Reference Identification',
		'PRV04' => 'State or Province Code',
		'PRV05' => 'Provider Specialty Information',
		'PRV06' => 'Provider Organization Code',
	];

}