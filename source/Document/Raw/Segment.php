<?php

namespace SunCoastConnection\ClaimsToOEMR\Document\Raw;

use \Exception,
	\SunCoastConnection\ClaimsToOEMR\Document\Options,
	\SunCoastConnection\ClaimsToOEMR\Document\Raw\Element;

class Segment {

	static protected $elementSequence = [];

	static protected $elementNames = [];

	protected $parentName;

	protected $elements = [];

	static public function getInstance(Options $options, $segment) {
		$delimiterPos = strpos(
			$segment,
			$options->get('Document.delimiters.data')
		);

		if($delimiterPos) {
			$designator = substr($segment, 0, $delimiterPos);
			$elements = substr($segment, $delimiterPos + 1);
		} else {
			$designator = $segment;
			$elements = '';
		}

		$class = $options->get('Aliases.'.$designator);

		if($class === null) {
			// TODO: Replace exception
			throw new Exception('Segment designator can not be found: '.$designator);
		}

		$object = new $class($options);

		$object->parse($elements);

		return $object;
	}

	static public function getElementSequence() {
		return static::$elementSequence;
	}

	static public function getElementNames() {
		return static::$elementNames;
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

	public function setParentName($parentName = '/') {
		$this->parentName = $parentName;
	}

	public function getName($full = false) {
		$name = explode('\\', static::class);
		$name = array_pop($name);

		if($full) {
			$name = ($this->parentName === '/' ?
				'' :
				$this->parentName
			).'/'.$name;
		}

		return $name;
	}

	public function parse($elements) {
		if($elements) {
			$options = $this->options();

			$elements = explode(
				$options->get('Document.delimiters.data'),
				$elements
			);

			array_walk($elements, function(&$element) use ($options) {
				$element = Element::getInstance($options, $element);
			});

			$sequence = $this::getElementSequence();

			foreach($elements as $pos => $element) {
				if(array_key_exists($pos, $sequence)) {
					$this->elements[$sequence[$pos]['name']] = $element;
				} else {
					$this->elements[count($this->elements)] = $element;
				}
			}
		}
	}

	public function elementExists($element) {
		return array_key_exists($element, $this->elements);
	}

	public function element($element) {
		if($this->elementExists($element)) {
			return $this->elements[$element];
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
		$data = $this->options()->get('Document.delimiters.data');

		return $this->getName().
			$data.
			implode(
				$data,
				$this->elements
			);
	}

}