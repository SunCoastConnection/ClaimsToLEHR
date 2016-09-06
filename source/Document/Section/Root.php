<?php

namespace SunCoastConnection\ClaimsToOEMR\Document\Section;

use \SunCoastConnection\ClaimsToOEMR\Document\Raw,
	\SunCoastConnection\ClaimsToOEMR\Document\Section;

class Root extends Section {

	static protected $descendantSequence = [];

	protected $subSections = [
		'descendant' => [],
	];

	public function parse(Raw $raw) {
		$this->subSections['descendant'] = [];

		$raw->rewind();

		$status = $this->parseSequence(
			$this::getSequence('descendantSequence'),
			$raw,
			$this->subSections['descendant']
		);

		return $status;
	}

	public function getDescendant() {
		return $this->subSections['descendant'];
	}

}