<?php

namespace SunCoastConnection\ClaimsToEMR\Document\Raw;

use \SunCoastConnection\ClaimsToEMR\Document\Options;

class Element {

	/**
	 * Array of sub elements of element
	 * @var array
	 */
	protected $subElements = [];

	/**
	 * Get instance of store class with provided options
	 *
	 * @param  \SunCoastConnection\ClaimsToEMR\Document\Options   $options  Options to create store object with
	 * @param  string                                             $element  Raw element string
	 *
	 * @return \SunCoastConnection\ClaimsToEMR\Document\Raw\Element  Element object
	 */
	static public function getInstance(Options $options, $element) {
		$object = new static($options);

		$object->parse($element);

		return $object;
	}

	/**
	 * Create a new Element
	 *
	 * @param \SunCoastConnection\ClaimsToEMR\Document\Options  $options  Options to create store object with
	 */
	public function __construct(Options $options) {
		$this->options($options);
	}

	/**
	 * Set element options or retrieve element options
	 *
	 * @param  \SunCoastConnection\ClaimsToEMR\Document\Options|null  $setOptions  Options to set element object with
	 *
	 * @return \SunCoastConnection\ClaimsToEMR\Document\Options|null  Element options or null when not set
	 */
	protected function options(Options $setOptions = null) {
		static $options = null;

		if(is_null($options) && !is_null($setOptions)) {
			$options = $setOptions;
		}

		return $options;
	}

	/**
	 * Parse the raw element
	 *
	 * @param  string  element  Raw element string
	 */
	public function parse($element) {
		$this->subElements = explode(
			$this->options()->get('Document.delimiters.component'),
			$element
		);
	}

	/**
	 * Check if sub-element exists
	 *
	 * @param  integer  $subElement  Index position of sub-element to check
	 *
	 * @return boolean  True if sub-element exists or false if not
	 */
	public function subElementExists($subElement) {
		return array_key_exists($subElement, $this->subElements);
	}

	/**
	 * Get sub-element if exists
	 *
	 * @param  integer  $subElement  Index position of sub-element to check
	 *
	 * @return string|null  Sub-element value if it exists or null if not
	 */
	public function subElement($subElement) {
		if($this->subElementExists($subElement)) {
			return $this->subElements[$subElement];
		}
	}

	/**
	 * Check if specified sub-element equals a provided value
	 *
	 * @param  integer       $subElement  Index position of sub-element to check
	 * @param  string|array  $value       Value or array of values to check against
	 *
	 * @return boolean  True if specified sub-element matches provided value(s)
	 */
	public function subElementEquals($subElement, $value) {
		if(!is_array($value)) {
			$value = [ $value ];
		}

		return in_array($this->subElement($subElement), $value);
	}

	/**
	 * Get count of sub-elements
	 *
	 * @return integer  Count of sub-elements
	 */
	public function subElementCount() {
		return count($this->subElements);
	}

	/**
	 * Get string value of element
	 *
	 * @return string  Raw value of element, containing all sub-elements separated by configured delimiter
	 */
	public function __toString() {
		return implode(
			$this->options()->get('Document.delimiters.component'),
			$this->subElements
		);
	}

}