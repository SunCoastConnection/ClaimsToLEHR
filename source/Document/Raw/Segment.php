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

	protected $subSections = [
		'data' => [],
	];

	public function __construct(Options $options, $parentName = '/') {
		$this->subSectionDelimiter = $options->get('Document.delimiters.data');

		parent::__construct($options, $parentName);
	}

	public function parse(Raw $raw) {
		$this->subSections['data'] = [];

		$status = false;

		if($raw->getSegmentDesignator() === $this->getName()) {
			$status = true;

			$elements = $raw->getSegmentElements();

			array_walk($elements, function(&$element) {
				$component = $this->options()->get('Document.delimiters.component');

				if(strpos($element, $component) !== false) {
					$element = explode($component, $element);
				}
			});

			$sequence = $this::getSequence('elementSequence');

			foreach($elements as $pos => $element) {
				if(array_key_exists($pos, $sequence)) {
					$this->subSections['data'][$sequence[$pos]['name']] = $element;
				} else {
					$this->subSections['data'][count($this->subSections['data'])] = $element;
				}
			}

			$raw->next();
		}

		return $status;
	}

	public function elementExists($element) {
		return array_key_exists($element, $this->subSections['data']);
	}

	public function subElementExists($element, $subElement) {
		return $this->elementExists($element) &&
			is_array($this->subSections['data'][$element]) &&
			array_key_exists($subElement, $this->subSections['data'][$element]);
	}

	public function element($element) {
		if($this->elementExists($element)) {
			return $this->subSections['data'][$element];
		}
	}

	public function subElement($element, $subElement) {
		if($this->subElementExists($element, $subElement)) {
			return $this->subSections['data'][$element][$subElement];
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
		$data = $this->subSections;

		array_walk($this->subSections['data'], function(&$element) {
			if(is_array($element)) {
				$element = implode(
					$this->options()->get('Document.delimiters.component'),
					$element
				);
			}
		});

		$return = $this->getName().
			$this->subSectionDelimiter.
			parent::__toString().
			$this->options()->get('Document.delimiters.segment');

		$this->subSections = $data;

		return $return;
	}

}