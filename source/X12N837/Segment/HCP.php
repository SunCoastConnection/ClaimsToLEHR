<?php

namespace SunCoastConnection\ClaimsToOEMR\X12N837\Segment;

use \SunCoastConnection\ClaimsToOEMR\Document\Segment;

class HCP extends Segment {

	static protected $elementSequence = [
		['name' => 'HCP01', 'required' => false],
		['name' => 'HCP02', 'required' => false],
		['name' => 'HCP03', 'required' => false],
		['name' => 'HCP04', 'required' => false],
		['name' => 'HCP05', 'required' => false],
		['name' => 'HCP06', 'required' => false],
		['name' => 'HCP07', 'required' => false],
		['name' => 'HCP08', 'required' => false],
		['name' => 'HCP09', 'required' => false],
		['name' => 'HCP10', 'required' => false],
		['name' => 'HCP11', 'required' => false],
		['name' => 'HCP12', 'required' => false],
		['name' => 'HCP13', 'required' => false],
		['name' => 'HCP14', 'required' => false],
		['name' => 'HCP15', 'required' => false],
	];

	static protected $elementNames = [
		'HCP01' => 'Pricing Methodology',
		'HCP02' => 'Monetary Amount',
		'HCP03' => 'Monetary Amount',
		'HCP04' => 'Reference Identification',
		'HCP05' => 'Rate',
		'HCP06' => 'Reference Identification',
		'HCP07' => 'Monetary Amount',
		'HCP08' => 'Product/Service ID',
		'HCP09' => 'Product/Service ID Qualifier',
		'HCP10' => 'Product/Service ID',
		'HCP11' => 'Unit or Basis for Measurement Code',
		'HCP12' => 'Quantity',
		'HCP13' => 'Reject Reason Code',
		'HCP14' => 'Policy Compliance Code',
		'HCP15' => 'Exception Code',
	];

}