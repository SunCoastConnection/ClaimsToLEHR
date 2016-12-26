<?php

namespace SunCoastConnection\ClaimsToOEMR;

use \SunCoastConnection\ClaimsToOEMR\Document\Options;

class X12N837 {

	/**
	 * Options object
	 * @var \SunCoastConnection\ClaimsToOEMR\Document\Options
	 */
	protected $options;

	public function setOptions(Options $options) {
		$this->options = $options;
	}

	public function getOptions() {
		return $this->options;
	}

	public function parseClaim($claim, $fromFile = false) {
		$rawClaim = $this->getOptions()->instanciateAlias(
			'Raw',
			[
				$this->getOptions()
			]
		);

		if($fromFile) {
			$rawClaim->parseFromFile($claim);
		} else {
			$rawClaim->parse($claim);
		}

		return $rawClaim;
	}

	public function disposeClaim($rawClaim) {
		$document = $this->getOptions()->instanciateAlias(
			'Document',
			[
				$this->getOptions()
			]
		);

		$document->parse($rawClaim);

		return $document;
	}

	public function cacheClaim($document) {
		$cache = $this->getOptions()->instanciateAlias(
			'Cache',
			[
				$this->getOptions()->get('App.store')
			]
		);

		$cache->processDocument($document);

		return $cache;
	}
}