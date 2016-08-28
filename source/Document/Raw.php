<?php

namespace SunCoastConnection\ClaimsToOEMR\Document;

use \Countable,
	\Exception,
	\Iterator,
	\SunCoastConnection\ClaimsToOEMR\Document\Options;

class Raw implements Iterator, Countable {

	static public function getNew(Options $options) {
		return new static($options);
	}

	protected $segments = [];

	public function __construct(Options $options) {
		$delimiters = $options->get('Document.delimiters');

		if(!is_array($delimiters)) {
			$delimiters = [];
		}

		$options->set(
			'Document.delimiters',
			array_merge(
				[
					'data'			=> '*',
					'repetition'	=> '^',
					'component'		=> ':',
					'segment'		=> '~',
				],
				$delimiters
			)
		);

		$this->options($options);
	}

	protected function options(Options $setOptions = null) {
		static $options = null;

		if(is_null($options) && !is_null($setOptions)) {
			$options = $setOptions;
		}

		return $options;
	}

	public function parseFromFile($fileName, $singleMode = false) {
		if(!is_string($fileName)) {
			// TODO: Replace exception
			throw new Exception('First paramiter should be a string: '.gettype($fileName).' passed');
		} elseif(!is_readable($fileName)) {
			// TODO: Replace exception
			throw new Exception('Filename provided is not readable: '.$fileName);
		}

		$this->parse(
			file_get_contents($fileName),
			$singleMode
		);
	}

	public function parse($string, $singleMode = false) {
		if(!is_string($string)) {
			// TODO: Replace exception
			throw new Exception('First paramiter should be a string: '.gettype($string).' passed');
		}

		$string = str_replace([ "\r", "\n" ], '', $string);

		if($this->options()->get('Document.autodetect')) {
			$this->setInterchangeData($string);
		}

		if($singleMode) {
			$string = $this->correctSingleMode($string);
		}

		$this->segments = array_filter(
			explode(
				$this->options()->get('Document.delimiters.segment'),
				$string
			)
		);
	}

	protected function setInterchangeData($string) {
		if(substr($string, 0, 3) !== 'ISA') {
			// TODO: Replace exception
			throw new Exception('ISA segment not provided as first segment');
		} else {
			$this->options()->set(
				'Document.delimiters',
				[
					'data' => $string[3],
					'repetition' => $string[82],
					'component' => $string[104],
					'segment' => $string[105],
				]
			);

		}
	}

	protected function correctSingleMode($string) {
		$delimiters = $this->options()->get('Document.delimiters');

		$match1 = implode(
			$delimiters['data'],
			[ 'HL', '1', '', '20', '1' ]
		);

		$replace1 = '{{@@@@@@@@}}';

		$match2 = implode(
			$delimiters['data'],
			[ '', '', '20', '1' ]
		).$delimiters['segment'];

		$replace2 = $delimiters['segment'].
			implode(
				$delimiters['data'],
				[ 'SE', '28', '0003' ]
			).$delimiters['segment'].
			implode(
				$delimiters['data'],
				[ 'ST', '837', '0004', '005010X222A1' ]
			).$delimiters['segment'];

		return str_replace(
			$replace1,
			$match1,
			str_replace(
				$match2,
				$replace2,
				str_replace(
					$match1,
					$replace1,
					$string
				)
			)
		);
	}

	public function __toString() {
		$segment = $this->options()->get('Document.delimiters.segment');

		return implode(
			$segment,
			$this->segments
		).$segment;
	}

	public function key() {
		return key($this->segments);
	}

	public function valid() {
		$key = key($this->segments);

		return ($key !== null && $key !== false);
	}

	public function current() {
		return current($this->segments);
	}

	public function next() {
		return next($this->segments);
	}

	public function rewind() {
		reset($this->segments);
	}

	public function count() {
		return count($this->segments);
	}

	public function getSegment() {
		return explode(
			$this->options()->get('Document.delimiters.data'),
			$this->current()
		);
	}

	public function getSegmentDesignator() {
		$segment = $this->getSegment();

		return $segment[0];
	}

	public function getSegmentElements() {
		$elements = $this->getSegment();
		array_shift($elements);

		return $elements;
	}

}