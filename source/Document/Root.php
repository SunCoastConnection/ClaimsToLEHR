<?php

namespace SunCoastConnection\ClaimsToOEMR\Document;

use \SunCoastConnection\ClaimsToOEMR\Document\Raw,
	\SunCoastConnection\ClaimsToOEMR\Document\Section;

class Root extends Section {

	static protected $descendantSequence = [];

	protected $subSections = [
		'descendant' => [],
	];

	public function parse(Raw $raw) {
// echo $this->getName(true).' Root Parse'.PHP_EOL;
		$this->subSections['descendant'] = [];

		$raw->rewind();

		$status = $this->parseSequence(
			$this::getSequence('descendantSequence'),
			$raw,
			$this->subSections['descendant']
		);

// echo 'Root Status: '.($status ? 'True' : 'False').PHP_EOL;
		return $status;
	}

	public function getDescendant() {
		return $this->subSections['descendant'];
	}

}