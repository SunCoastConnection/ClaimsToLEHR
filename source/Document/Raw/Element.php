<?php

namespace SunCoastConnection\ClaimsToOEMR\Document\Raw;

use \SunCoastConnection\ClaimsToOEMR\Document\Options;

class Element {

	protected $subElements = [];

	static public function getInstance(Options $options, $element) {
		$object = new static($options);

		$object->parse($element);

		return $object;
	}

	public function __construct(Options $options) {
		$this->options($options);
	}

	protected function options(Options $setOptions = null) {
		static $options = null;

		if(is_null($options) && !is_null($setOptions)) {
			$options = $setOptions;
		}

		return $options;
	}

	public function parse($element) {
		$this->subElements = explode(
			$this->options()->get('Document.delimiters.component'),
			$element
		);
	}

	public function subElementExists($subElement) {
		return array_key_exists($subElement, $this->subElements);
	}

	public function subElement($subElement) {
		if($this->subElementExists($subElement)) {
			return $this->subElements[$subElement];
		}
	}

	public function subElementEquals($subElement, $value) {
		if(!is_array($value)) {
			$value = [ $value ];
		}

		return in_array($this->subElement($subElement), $value);
	}

	public function subElementCount() {
		return count($this->subElements);
	}

	public function __toString() {
		return implode(
			$this->options()->get('Document.delimiters.component'),
			$this->subElements
		);
	}

}