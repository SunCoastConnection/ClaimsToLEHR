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

    /**
     * Get the specified configuration subset.
     *
     * @param  string  $key
     * @param  mixed   $default
     *
	 * @return \SunCoastConnection\ClaimsToOEMR\Document\Options  Subset of configurations wrapped in Options object
     */
	public function getSubset($key, $default = []) {
		return static::getInstance($this->get($key, $default));
	}
}