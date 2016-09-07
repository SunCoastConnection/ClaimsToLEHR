<?php

namespace SunCoastConnection\ClaimsToOEMR\Document;

use \Illuminate\Config\Repository;

class Options extends Repository {

	/**
	 * Get instance of options class with provided options
	 *
	 * @param  array   $options  Options to create store object with
	 *
	 * @return \SunCoastConnection\ClaimsToOEMR\Document\Options  Options object
	 */
	static public function getInstance(array $options) {
		return new static($options);
	}

}