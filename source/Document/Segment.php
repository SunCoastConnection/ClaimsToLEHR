<?php

namespace SunCoastConnection\ClaimsToOEMR\Document;

use \SunCoastConnection\ClaimsToOEMR\Document\Raw,
	\SunCoastConnection\ClaimsToOEMR\Document\Section;

class Segment extends Section {

	static protected $elementSequence = [];

	static protected $elementNames = [];

	static public function getElementNames() {
		return static::$elementNames;
	}

	protected $data = [];

	public function parse(Raw $raw) {
		$this->subSectionCount = 0;
		$this->data = [];

		$status = false;

		if($raw->getSegmentDesignator() === $this->getName()) {
			$this->subSectionCount = 1;
			$status = true;

			$elements = $raw->getSegmentElements();

			array_walk($elements, function(&$element) {
				if(strpos($element, ':') !== false) {
					$element = explode(':', $element);
				}
			});

			$sequence = $this::getSequence('elementSequence');

			foreach($elements as $pos => $element) {
				if(array_key_exists($pos, $sequence)) {
					$this->data[$sequence[$pos]['name']] = $element;
				} else {
					$this->data[count($this->data)] = $element;
				}
			}

			$raw->next();
		}

		return $status;
	}

	public function elementExists($element) {
		return array_key_exists($element, $this->data);
	}

	public function subElementExists($element, $subElement) {
		return $this->elementExists($element) &&
			is_array($this->data[$element]) &&
			array_key_exists($subElement, $this->data[$element]);
	}

	public function element($element) {
		if($this->elementExists($element)) {
			return $this->data[$element];
		}
	}

	public function subElement($element, $subElement) {
		if($this->subElementExists($element, $subElement)) {
			return $this->data[$element][$subElement];
		}
	}

	public function elementEquals($element, $value) {
		if(!is_array($value)) {
			$value = [ $value ];
		}

		return $this->elementExists($element) &&
			in_array($this->element($element), $value);
	}

	public function subElementEquals($element, $subElement, $value) {
		if(!is_array($value)) {
			$value = [ $value ];
		}

		return $this->subElementExists($element, $subElement) &&
			in_array($this->subElement($element, $subElement), $value);
	}

	public function subElementCount($element) {
		$element = $this->element($element);

		if(is_array($element)) {
			return count($element);
		}

		return 0;
	}

	public function __toString() {
		$return = $this->data;

		array_walk($return, function(&$element) {
			if(is_array($element)) {
				$element = implode(':', $element);
			}
		});

		array_unshift($return, $this->getName());

		return implode('*', $return).'~';
	}

}