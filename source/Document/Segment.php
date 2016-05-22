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
// echo $this->getName(true).' Segment Parse'.PHP_EOL;
		$this->subSectionCount = 0;
		$this->data = [];

		$status = false;

		if($raw->getSegmentDesignator() === $this->getName()) {
// echo $this->getName(true).PHP_EOL;
			$this->subSectionCount = 1;
			$status = true;

			$elements = $raw->getSegmentElements();
			$sequence = $this::getSequence('elementSequence');

			foreach($elements as $pos => $element) {
				if(array_key_exists($pos, $sequence)) {
					$this->data[$sequence[$pos]['name']] = $element;
				} else {
					$this->data[count($this->data)] = $element;
				}
			}

// // echo 'Elements: '.serialize($elements).PHP_EOL;
// 			foreach($sequence as $pos => $element) {
// // echo 'Pos: '.$pos.' Element: '.json_encode($element).PHP_EOL;
// 				if(array_key_exists($pos, $elements)) {
// 					$this->data[$element['name']] = $elements[$pos];
// 				} else {
// 					$this->data[$element['name']] = '';
// 				}
// 			}

			$raw->next();
		}

// echo 'Segment Status: '.($status ? 'True' : 'False').PHP_EOL;
		return $status;
	}

	public function __toString() {
		$return = $this->data;

		array_unshift($return, $this->getName());

		return implode('*', $return).'~';
	}

}