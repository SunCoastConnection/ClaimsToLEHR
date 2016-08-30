<?php

namespace SunCoastConnection\ClaimsToOEMR\Document\Section;

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

		return $status;
	}

	public function getHeader() {
		return $this->subSections['header'];
	}

	public function getDescendant() {
		return $this->subSections['descendant'];
	}

}