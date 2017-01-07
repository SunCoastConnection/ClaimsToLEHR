<?php

namespace SunCoastConnection\ClaimsToEMR\Tests\Document;

use \SunCoastConnection\ClaimsToEMR\Document\Raw;
use \SunCoastConnection\ClaimsToEMR\Document\Section;

class SectionMock extends Section {

	static public $testSequence = [
		'A',
		1,
		'B',
		2,
		'C',
		3,
	];

	public function parse(Raw $raw) {}

	public function __toString() {}

	// public function __parseSequence(array $sequence, Raw $raw, &$objects) {
	// 	return $this->parseSequence($sequence, $raw, $objects);
	// }
}