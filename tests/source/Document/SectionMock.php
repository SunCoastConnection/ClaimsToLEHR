<?php

namespace SunCoastConnection\ClaimsToOEMR\Tests\Document;

use \SunCoastConnection\ClaimsToOEMR\Document\Raw;
use \SunCoastConnection\ClaimsToOEMR\Document\Section;

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