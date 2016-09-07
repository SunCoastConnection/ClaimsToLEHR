<?php

namespace SunCoastConnection\ClaimsToOEMR\Document\Section;

use \SunCoastConnection\ClaimsToOEMR\Document\Raw;
use \SunCoastConnection\ClaimsToOEMR\Document\Section;

class Root extends Section {

	/**
	 * Root descendant sequence
	 * @var array
	 */
	static protected $descendantSequence = [];

	/**
	 * Root sub-sections
	 * @var array
	 */
	protected $subSections = [
		'descendant' => [],
	];

	/**
	 * Parse root sub-sections
	 *
	 * @param  \SunCoastConnection\ClaimsToOEMR\Document\Raw  $raw  Raw X12 document object
	 *
	 * @return boolean  True if root was parsable or false otherwise
	 */
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

	/**
	 * Return root descendant sub-section
	 * @return array  Descendant sub-section
	 */
	public function getDescendant() {
		return $this->subSections['descendant'];
	}

}