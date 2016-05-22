<?php

namespace SunCoastConnection\ClaimsToOEMR\Document;

use \SunCoastConnection\ClaimsToOEMR\Document\Raw,
	\SunCoastConnection\ClaimsToOEMR\Document\Section;

class Root extends Section {

	static protected $descendantSequence = [];

	protected $descendant = [];

	public function parse(Raw $raw) {
// echo $this->getName(true).' Root Parse'.PHP_EOL;
		$this->subSectionCount = 0;
		$this->descendant = [];

		$raw->rewind();

		$status = $this->parseSequence(
			$this::getSequence('descendantSequence'),
			$raw,
			$this->descendant
		);

		$this->subSectionCount = count($this->descendant);

// echo 'Root Status: '.($status ? 'True' : 'False').PHP_EOL;
		return $status;
	}

	public function __toString() {
		return implode('', $this->descendant);
	}

}