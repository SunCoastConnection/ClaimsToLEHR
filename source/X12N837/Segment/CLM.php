<?php

namespace SunCoastConnection\ClaimsToOEMR\X12N837\Segment;

use \SunCoastConnection\ClaimsToOEMR\Document\Segment;

class CLM extends Segment {

	static protected $elementSequence = [
		['name' => 'CLM01', 'required' => true],
		['name' => 'CLM02', 'required' => false],
		['name' => 'CLM03', 'required' => false],
		['name' => 'CLM04', 'required' => false],
		['name' => 'CLM05', 'required' => false],
		['name' => 'CLM06', 'required' => false],
		['name' => 'CLM07', 'required' => false],
		['name' => 'CLM08', 'required' => false],
		['name' => 'CLM09', 'required' => false],
		['name' => 'CLM10', 'required' => false],
		['name' => 'CLM11', 'required' => false],
		['name' => 'CLM12', 'required' => false],
		['name' => 'CLM13', 'required' => false],
		['name' => 'CLM14', 'required' => false],
		['name' => 'CLM15', 'required' => false],
		['name' => 'CLM16', 'required' => false],
		['name' => 'CLM17', 'required' => false],
		['name' => 'CLM18', 'required' => false],
		['name' => 'CLM19', 'required' => false],
		['name' => 'CLM20', 'required' => false],
	];

	static protected $elementNames = [
		'CLM01' => 'Claim Submitter’s Identifier',
		'CLM02' => 'Monetary Amount',
		'CLM03' => 'Claim Filing Indicator Code',
		'CLM04' => 'Non-Institutional Claim Type Code',
		'CLM05' => 'Health Care Service Location Information',
		'CLM06' => 'Yes/No Condition or Response Code',
		'CLM07' => 'Provider Accept Assignment Code',
		'CLM08' => 'Yes/No Condition or Response Code',
		'CLM09' => 'Release of Information Code',
		'CLM10' => 'Patient Signature Source Code',
		'CLM11' => 'Related Causes Information',
		'CLM12' => 'Special Program Code',
		'CLM13' => 'Yes/No Condition or Response Code',
		'CLM14' => 'Level of Service Code',
		'CLM15' => 'Yes/No Condition or Response Code',
		'CLM16' => 'Provider Agreement Code',
		'CLM17' => 'Claim Status Code',
		'CLM18' => 'Yes/No Condition or Response Code',
		'CLM19' => 'Claim Submission Reason Code',
		'CLM20' => 'Delay Reason Code',
	];

}