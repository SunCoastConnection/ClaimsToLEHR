<?php

namespace SunCoastConnection\ClaimsToEMR\X12N837\Segment;

use \SunCoastConnection\ClaimsToEMR\Document\Raw\Segment;

class OI extends Segment {

	static protected $elementSequence = [
		['name' => 'OI01', 'required' => false],
		['name' => 'OI02', 'required' => false],
		['name' => 'OI03', 'required' => false],
		['name' => 'OI04', 'required' => false],
		['name' => 'OI05', 'required' => false],
		['name' => 'OI06', 'required' => false],
	];

	static protected $elementNames = [
		'OI01' => 'Claim Filing Indicator Code',
		'OI02' => 'Claim Submission Reason Code',
		'OI03' => 'Yes/No Condition or Response Code',
		'OI04' => 'Patient Signature Source Code',
		'OI05' => 'Provider Agreement Code',
		'OI06' => 'Release of Information Code',
	];

}