<?php

namespace SunCoastConnection\ClaimsToEMR\Document\Section;

use \SunCoastConnection\ClaimsToEMR\Document\Raw;
use \SunCoastConnection\ClaimsToEMR\Document\Section;

class Loop extends Section {

	/**
	 * Loop header sequence
	 * @var array
	 */
	static protected $headerSequence = [];

	/**
	 * Loop descendant sequence
	 * @var array
	 */
	static protected $descendantSequence = [];

	/**
	 * Loop sub-sections
	 * @var array
	 */
	protected $subSections = [
		'header' => [],
		'descendant' => [],
	];

	/**
	 * Parse loop sub-sections
	 *
	 * @param  \SunCoastConnection\ClaimsToEMR\Document\Raw  $raw  Raw X12 document object
	 *
	 * @return boolean  True if loop was parsable or false otherwise
	 */
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

	/**
	 * Return loop header sub-section
	 * @return array  Header sub-section
	 */
	public function getHeader() {
		return $this->subSections['header'];
	}

	/**
	 * Return loop descendant sub-section
	 * @return array  Descendant sub-section
	 */
	public function getDescendant() {
		return $this->subSections['descendant'];
	}

}