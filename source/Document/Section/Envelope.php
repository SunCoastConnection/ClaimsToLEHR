<?php

namespace SunCoastConnection\ClaimsToOEMR\Document\Section;

use \SunCoastConnection\ClaimsToOEMR\Document\Raw,
	\SunCoastConnection\ClaimsToOEMR\Document\Section;

class Envelope extends Section {

	static protected $headerSequence = [];
	static protected $descendantSequence = [];
	static protected $trailerSequence = [];

	protected $subSections = [
		'header' => [],
		'descendant' => [],
		'trailer' => [],
	];

	public function parse(Raw $raw) {
		$this->subSections = [
			'header' => [],
			'descendant' => [],
			'trailer' => [],
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

			$this->parseSequence(
				$this::getSequence('trailerSequence'),
				$raw,
				$this->subSections['trailer']
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

	public function getTrailer() {
		return $this->subSections['trailer'];
	}

}