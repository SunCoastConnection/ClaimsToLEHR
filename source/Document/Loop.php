<?php

namespace SunCoastConnection\ClaimsToOEMR\Document;

use \SunCoastConnection\ClaimsToOEMR\Document\Raw,
	\SunCoastConnection\ClaimsToOEMR\Document\Section;

class Loop extends Section {

	static protected $headerSequence = [];
	static protected $descendantSequence = [];

	protected $subSections = [
		'header' => [],
		'descendant' => [],
	];

	public function parse(Raw $raw) {
// echo $this->getName(true).' Loop Parse'.PHP_EOL;
		$this->subSections = [
			'header' => [],
			'descendant' => [],
		];

		$status = $this->parseSequence(
			$this::getSequence('headerSequence'),
			$raw,
			$this->subSections['header']
		);

		if($status) {
			$this->parseSequence(
				$this::getSequence('descendantSequence'),
				$raw,
				$this->subSections['descendant']
			);
		}

// echo 'Loop Status: '.($status ? 'True' : 'False').PHP_EOL;
		return $status;
	}

	public function getHeader() {
		return $this->subSections['header'];
	}

	public function getDescendant() {
		return $this->subSections['descendant'];
	}

}