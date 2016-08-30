<?php

namespace SunCoastConnection\ClaimsToOEMR\X12N837\Segment;

use \SunCoastConnection\ClaimsToOEMR\Document\Raw\Segment;

class PWK extends Segment {

	static protected $elementSequence = [
		['name' => 'PWK01', 'required' => true],
		['name' => 'PWK02', 'required' => false],
		['name' => 'PWK03', 'required' => false],
		['name' => 'PWK04', 'required' => false],
		['name' => 'PWK05', 'required' => false],
		['name' => 'PWK06', 'required' => false],
		['name' => 'PWK07', 'required' => false],
		['name' => 'PWK08', 'required' => false],
		['name' => 'PWK09', 'required' => false],
	];

	static protected $elementNames = [
		'PWK01' => 'Report Type Code',
		'PWK02' => 'Report Transmission Code',
		'PWK03' => 'Report Copies Needed',
		'PWK04' => 'Entity Identifier Code',
		'PWK05' => 'Identification Code Qualifier',
		'PWK06' => 'Identification Code',
		'PWK07' => 'Description',
		'PWK08' => 'Actions Indicated',
		'PWK09' => 'Request Category Code',
	];

}