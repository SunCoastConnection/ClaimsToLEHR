<?php

namespace SunCoastConnection\ClaimsToEMR\Document;

use \Exception;
use \Illuminate\Config\Repository;
use \ReflectionClass;

class Options extends Repository {

	/**
	 * Get instance of options class with provided options
	 *
	 * @param  array  $options  Options to create store object with
	 *
	 * @return \SunCoastConnection\ClaimsToEMR\Document\Options  Options object
	 */
	static public function getInstance(array $options) {
		return new static($options);
	}

	/**
	 * Resolve alias name to class name
	 *
	 * @param  string  $alias  Alias name
	 *
	 * @return string  Class name
	 */
	public function resolveAlias($alias) {
		return $this->get('Aliases.'.$alias);
	}

	/**
	 * Instanciate class by alias name
	 *
	 * @param  string  $alias       Alias name
	 * @param  array   $paramiters  Paramiters used to instanciate class
	 *
	 * @return mixed   Instanciated object
	 */
	public function instanciateAlias($alias, $paramiters = []) {
		$className = $this->resolveAlias($alias);

		if(is_null($className)) {
			throw new Exception('Alias not found: '.$alias);
		}

		$reflectionClass = new ReflectionClass($className);

		return $reflectionClass->newInstanceArgs($paramiters);
	}

	/**
	 * Get the specified configuration subset.
	 *
	 * @param  string  $key      Key of subset to return
	 * @param  mixed   $default  Default value to use for subset if key not found
	 *
	 * @return \SunCoastConnection\ClaimsToEMR\Document\Options  Subset of configurations wrapped in Options object
	 */
	public function getSubset($key, $default = []) {
		$subset = $this->get($key, $default);

		if($subset !== null) {
			return static::getInstance($subset);
		}
	}
}