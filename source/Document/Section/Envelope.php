<?php

namespace SunCoastConnection\ClaimsToEMR\Document\Section;

use \SunCoastConnection\ClaimsToEMR\Document\Raw;
use \SunCoastConnection\ClaimsToEMR\Document\Section;

class Envelope extends Section {

	/**
	 * Envelope header sequence
	 * @var array
	 */
	static protected $headerSequence = [];

	/**
	 * Envelope descendant sequence
	 * @var array
	 */
	static protected $descendantSequence = [];

	/**
	 * Envelope trailer sequence
	 * @var array
	 */
	static protected $trailerSequence = [];

	/**
	 * Envelope sub-sections
	 * @var array
	 */
	protected $subSections = [
		'header' => [],
		'descendant' => [],
		'trailer' => [],
	];

	/**
	 * Parse envelope sub-sections
	 *
	 * @param  \SunCoastConnection\ClaimsToEMR\Document\Raw  $raw  Raw X12 document object
	 *
	 * @return boolean  True if envelope was parsable or false otherwise
	 */
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

	/**
	 * Return envelope header sub-section
	 * @return array  Header sub-section
	 */
	public function getHeader() {
		return $this->subSections['header'];
	}

	/**
	 * Return envelope descendant sub-section
	 * @return array  Descendant sub-section
	 */
	public function getDescendant() {
		return $this->subSections['descendant'];
	}

	/**
	 * Return envelope trailer sub-section
	 * @return array  Trailer sub-section
	 */
	public function getTrailer() {
		return $this->subSections['trailer'];
	}

}