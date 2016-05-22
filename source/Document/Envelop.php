<?php

namespace SunCoastConnection\ClaimsToOEMR\Document;

use \SunCoastConnection\ClaimsToOEMR\Document\Raw,
	\SunCoastConnection\ClaimsToOEMR\Document\Section;

class Envelop extends Section {

	static protected $headerSequence = [];
	static protected $descendantSequence = [];
	static protected $trailerSequence = [];

	protected $header = [];
	protected $descendant = [];
	protected $trailer = [];

	public function parse(Raw $raw) {
// echo $this->getName(true).' Envelop Parse'.PHP_EOL;
		$this->subSectionCount = 0;
		$this->header = [];
		$this->descendant = [];
		$this->trailer = [];

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

			$this->parseSequence(
				$this::getSequence('trailerSequence'),
				$raw,
				$this->trailer
			);

			$this->subSectionCount += count($this->trailer);
		}

// echo 'Envelop Status: '.($status ? 'True' : 'False').PHP_EOL;
		return $status;
	}

	public function __toString() {
		return implode('', array_merge(
			$this->header,
			$this->descendant,
			$this->trailer
		));
	}

}