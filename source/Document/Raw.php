<?php

namespace SunCoastConnection\ClaimsToOEMR\Document;

use \Countable,
	\Iterator,
	\Exception;

class Raw implements Iterator, Countable {

	static public function getNew($string, $singleMode = false) {
		return new static($string, $singleMode);
	}

	protected $chunks = [];

	public function __construct($string, $singleMode = false) {
		if(!is_string($string)) {
			// TODO: Replace exception
			throw new Exception('First paramiter should be a string: '.gettype($string).' passed.');
		}

		if($singleMode) {
			$string = str_replace('HL*1**20*1', '@@@@@@@@', $string);
			$string = str_replace('**20*1~', '~SE*28*0003~ST*837*0004*005010X222A1~', $string);
			$string = str_replace('@@@@@@@@', 'HL*1**20*1', $string);
			$string = str_replace('\'', '\\\'', $string);
		}

		$this->chunks = array_filter(explode('~', $string));
	}

	public function __toString() {
		return implode('~', $this->chunks).'~';
	}

	public function key() {
		return key($this->chunks);
	}

	public function valid() {
		$key = key($this->chunks);

		return ($key !== null && $key !== false);
	}

	public function current() {
		return current($this->chunks);
	}

	public function next() {
		return next($this->chunks);
	}

	public function rewind() {
		reset($this->chunks);
	}

	public function count() {
		return count($this->chunks);
	}

	public function getSegments() {
		return explode('*', $this->current());
	}

	public function getSegmentDesignator() {
		$elements = $this->getSegments();

		return $elements[0];
	}

	public function getSegmentElements() {
		$elements = $this->getSegments();
		array_shift($elements);

		return $elements;
	}

}