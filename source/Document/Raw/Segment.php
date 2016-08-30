<?php

namespace SunCoastConnection\ClaimsToOEMR\Document\Raw;

use \SunCoastConnection\ClaimsToOEMR\Document\Options,
	\SunCoastConnection\ClaimsToOEMR\Document\Raw,
	\SunCoastConnection\ClaimsToOEMR\Document\Section;

class Segment extends Section {

	static protected $elementSequence = [];

	static protected $elementNames = [];

	static public function getElementNames() {
		return static::$elementNames;
	}

	protected $subSections = [
		'elements' => [],
	];

	public function __construct(Options $options, $parentName = '/') {
		$this->subSectionDelimiter = $options->get('Document.delimiters.data');

		parent::__construct($options, $parentName);
	}

	public function parse(Raw $raw) {
		$this->subSections['elements'] = [];

		$status = false;

		if($raw->getSegmentDesignator() === $this->getName()) {
			$status = true;

			$elements = $raw->getSegmentElements();

			$component = $this->options()->get('Document.delimiters.component');

			array_walk($elements, function(&$element) use ($component) {
				if(strpos($element, $component) !== false) {
					$element = explode($component, $element);
				}
			});

			$sequence = $this::getSequence('elementSequence');

			foreach($elements as $pos => $element) {
				if(array_key_exists($pos, $sequence)) {
					$this->subSections['elements'][$sequence[$pos]['name']] = $element;
				} else {
					$this->subSections['elements'][count($this->subSections['elements'])] = $element;
				}
			}

			$raw->next();
		}

		return $status;
	}

	public function elementExists($element) {
		return array_key_exists($element, $this->subSections['elements']);
	}

	public function element($element) {
		if($this->elementExists($element)) {
			return $this->subSections['elements'][$element];
		}
	}

	public function elementEquals($element, $value) {
		if(!is_array($value)) {
			$value = [ $value ];
		}

		return $this->elementExists($element) &&
			in_array($this->element($element), $value);
	}

	public function __toString() {
		$data = $this->subSections;

		array_walk($this->subSections['elements'], function(&$element) {
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