<?php

namespace SunCoastConnection\ClaimsToOEMR\Document;

use \SunCoastConnection\ClaimsToOEMR\Document\Raw,
	\SunCoastConnection\ClaimsToOEMR\Document\Section;

class Loop extends Section {

	static protected $headerSequence = [];
	static protected $descendantSequence = [];

	protected $header = [];
	protected $descendant = [];

	public function parse(Raw $raw) {
// echo $this->getName(true).' Loop Parse'.PHP_EOL;
		$this->subSectionCount = 0;
		$this->header = [];
		$this->descendant = [];

		$status = $this->parseSequence(
			$this::getSequence('headerSequence'),
			$raw,
			$this->header
		);

		$this->subSectionCount = count($this->header);

		if($status) {
			$this->parseSequence(
				$this::getSequence('descendantSequence'),
				$raw,
				$this->descendant
			);

			$this->subSectionCount += count($this->descendant);
		}

// echo 'Loop Status: '.($status ? 'True' : 'False').PHP_EOL;
		return $status;
	}

	public function __toString() {
		return implode('', array_merge(
			$this->header,
			$this->descendant
		));
	}

}