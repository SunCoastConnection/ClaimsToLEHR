<?php

namespace SunCoastConnection\ClaimsToOEMR\Document;

use \Countable;
use \Exception;
use \Iterator;
use \SunCoastConnection\ClaimsToOEMR\Document\Options;
use \SunCoastConnection\ClaimsToOEMR\Document\Raw\Segment;

class Raw implements Iterator, Countable {

	/**
	 * Array of segments from raw data
	 * @var array
	 */
	protected $segments = [];

	/**
	 * Get instance of raw class with provided options
	 *
	 * @param  \SunCoastConnection\ClaimsToOEMR\Document\Options  $options  Options to create raw object with
	 *
	 * @return \SunCoastConnection\ClaimsToOEMR\Document\Raw  Raw object
	 */
	static public function getInstance(Options $options) {
		return new static($options);
	}

	/**
	 * Create a new Raw
	 *
	 * @param \SunCoastConnection\ClaimsToOEMR\Document\Options  $options  Options to create raw object with
	 */
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

	/**
	 * Set raw options or retrieve raw options
	 *
	 * @param  \SunCoastConnection\ClaimsToOEMR\Document\Options|null  $setOptions  Options to set raw object with
	 *
	 * @return \SunCoastConnection\ClaimsToOEMR\Document\Options|null  Raw options or null when not set
	 */
	protected function options(Options $setOptions = null) {
		static $options = null;

		if(is_null($options) && !is_null($setOptions)) {
			$options = $setOptions;
		}

		return $options;
	}

	/**
	 * Parse the segments from an raw X12 file
	 *
	 * @param  string   $fileName    Path to file to parse
	 * @param  boolean  $singleMode  Parse file with single Transaction Set
	 */
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

	/**
	 * Parse the segments from an raw X12
	 *
	 * @param  string   $string      Raw X12 document
	 * @param  boolean  $singleMode  Parse file with single Transaction Set
	 */
	public function parse($string, $singleMode = false) {
		if(!is_string($string)) {
			// TODO: Replace exception
			throw new Exception('First paramiter should be a string: '.gettype($string).' passed');
		}

		$strig = $this->convertSimple837($string);

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

		$this->parseSegments();

		$this->rewind();
	}

	/**
	 * Convert a Simple837 format X12 document to a standard X12 document
	 *
	 * @param  string  $string  Simple X12N837 document
	 *
	 * @return string           Converted X12 document
	 */
	protected function convertSimple837($string) {
		if(substr($string, 0, 7) == 'CONTROL') {
			$string = array_filter(
				explode(
					"\n",
					str_replace(
						"\r",
						"\n",
						$string
					)
				)
			);

			foreach($string as $line => $segment) {
				$string[$line] = substr($segment, 20);
			}

			$string = implode('~', $string).'~';
		}

		return $string;
	}

	/**
	 * Detect X12 document delimiters
	 *
	 * @param string  $string  Raw X12 document
	 */
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

	/**
	 * Convert X12 document with single Transaction Set to multiple
	 *
	 * @param  string  $string  Raw X12 document
	 *
	 * @return string           Raw X12 document
	 */
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

	/**
	 * Wrap all segments with associated segment class
	 */
	protected function parseSegments() {
		$options = $this->options();

		array_walk($this->segments, function(&$segment) use ($options) {
			$segment = Segment::getInstance($options, $segment);
		});
	}

	/**
	 * Get string value of Raw X12 document
	 *
	 * @return string  Raw value of X12, containing all segments separated by configured delimiter
	 */
	public function __toString() {
		$segment = $this->options()->get('Document.delimiters.segment');

		return implode(
			$segment,
			$this->segments
		).$segment;
	}

	/**
	 * Return the key of the current segment
	 *
	 * @return string  Key of the current segment
	 */
	public function key() {
		return key($this->segments);
	}

	/**
	 * Checks if current position is valid
	 *
	 * @return boolean  Returns true on success or false on failure
	 */
	public function valid() {
		$key = key($this->segments);

		return ($key !== null && $key !== false);
	}

	/**
	 * Return the current segment
	 * @return \SunCoastConnection\ClaimsToOEMR\Document\Raw\Segment  Current segment
	 */
	public function current() {
		return current($this->segments);
	}

	/**
	 * Move forward to next segment
	 */
	public function next() {
		return next($this->segments);
	}

	/**
	 * Rewind back to the first segment
	 */
	public function rewind() {
		reset($this->segments);
	}

	/**
	 * Count segments
	 * @return integer  Count of segments
	 */
	public function count() {
		return count($this->segments);
	}

}