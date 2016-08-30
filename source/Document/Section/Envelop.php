<?php

namespace SunCoastConnection\ClaimsToOEMR\Document;

use \SunCoastConnection\ClaimsToOEMR\Document\Raw,
	\SunCoastConnection\ClaimsToOEMR\Document\Section;

class Envelop extends Section {

	static protected $headerSequence = [];
	static protected $descendantSequence = [];
	static protected $trailerSequence = [];

	protected $subSections = [
		'header' => [],
		'descendant' => [],
		'trailer' => [],
	];

	public function parse(Raw $raw) {
// echo $this->getName(true).' Envelop Parse'.PHP_EOL;
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

// echo 'Envelop Status: '.($status ? 'True' : 'False').PHP_EOL;
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