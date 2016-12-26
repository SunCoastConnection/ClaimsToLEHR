<?php

namespace SunCoastConnection\ClaimsToOEMR\Document\Raw;

use \Exception;
use \SunCoastConnection\ClaimsToOEMR\Document\Options;
use \SunCoastConnection\ClaimsToOEMR\Document\Raw\Element;

class Segment {

	/**
	 * Array of expected elements
	 * @var array
	 */
	static protected $elementSequence = [];

	/**
	 * Array of element names
	 * @var array
	 */
	static protected $elementNames = [];

	/**
	 * Segment parent name
	 * @var string
	 */
	protected $parentName;

	/**
	 * Array of segment elements
	 * @var array
	 */
	protected $elements = [];

	/**
	 * Get instance of segment class with provided options
	 *
	 * @param  \SunCoastConnection\ClaimsToOEMR\Document\Options  $options  Options to create store object with
	 * @param  string                                             $segment  Raw segment string including segment designator
	 *
	 * @return \SunCoastConnection\ClaimsToOEMR\Document\Raw\Segment  Segment object
	 */
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

		try {
			$object = $options->instanciateAlias(
				$designator,
				[
					$options
				]
			);
		} catch (Exception $e) {
			// TODO: Replace exception
			throw new Exception('Segment designator can not be found: '.$designator);
		}

		$object->parse($elements);

		return $object;
	}

	/**
	 * Get the set sequence of elements for segment
	 *
	 * @return array  Array of expected elements
	 */
	static public function getElementSequence() {
		return static::$elementSequence;
	}

	/**
	 * Get the set element names for segment
	 *
	 * @return array  Array of element names
	 */
	static public function getElementNames() {
		return static::$elementNames;
	}

	/**
	 * Create a new Segment
	 *
	 * @param \SunCoastConnection\ClaimsToOEMR\Document\Options  $options  Options to create store object with
	 */
	public function __construct(Options $options) {
		$this->options($options);
	}

	/**
	 * Set segment options or retrieve segment options
	 *
	 * @param  \SunCoastConnection\ClaimsToOEMR\Document\Options|null  $setOptions  Options to set segment object with
	 *
	 * @return \SunCoastConnection\ClaimsToOEMR\Document\Options|null  Segment options or null when not set
	 */
	protected function options(Options $setOptions = null) {
		static $options = null;

		if(is_null($options) && !is_null($setOptions)) {
			$options = $setOptions;
		}

		return $options;
	}

	/**
	 * Set the segments parent name
	 *
	 * @param string  $parentName  Name of segments parent
	 */
	public function setParentName($parentName = '/') {
		$this->parentName = $parentName;
	}

	/**
	 * Get segment designator with or without full parent name
	 *
	 * @param  boolean  $full  True to return full name or false to return just segment designator
	 *
	 * @return string  Segment designator with or without full parent name
	 */
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

	/**
	 * Parse the raw segment
	 *
	 * @param  string  $elements  Raw segment, not including segment designator
	 */
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

	/**
	 * Check if element exists
	 *
	 * @param  string  $element  Index of element to check
	 *
	 * @return boolean  True if element exists or false if not
	 */
	public function elementExists($element) {
		return array_key_exists($element, $this->elements);
	}

	/**
	 * Get element if exists
	 *
	 * @param  string  $element  Index of element to check
	 *
	 * @return string|null  Element value if it exists or null if not
	 */
	public function element($element) {
		if($this->elementExists($element)) {
			return $this->elements[$element];
		}
	}

	/**
	 * Check if specified element equals a provided value
	 *
	 * @param  string        $element     Index of element to check
	 * @param  string|array  $value       Value or array of values to check against
	 *
	 * @return boolean  True if specified sub-element matches provided value(s)
	 */
	public function elementEquals($element, $value) {
		if(!is_array($value)) {
			$value = [ $value ];
		}

		return $this->elementExists($element) &&
			in_array($this->element($element), $value);
	}

	/**
	 * Get string value of segment
	 *
	 * @return string  Raw value of segment, containing all elements separated by configured delimiter
	 */
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