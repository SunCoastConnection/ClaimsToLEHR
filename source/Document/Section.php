<?php

namespace SunCoastConnection\ClaimsToOEMR\Document;

use \SunCoastConnection\ClaimsToOEMR\Document\Options,
	\SunCoastConnection\ClaimsToOEMR\Document\Raw,
	\SunCoastConnection\ClaimsToOEMR\Document\Raw\Segment;

abstract class Section {

	static protected $name;

	protected $parentName;

	protected $subSections = [];

	protected $subSectionDelimiter = '';

	abstract public function parse(Raw $raw);

	static public function getNew(Options $options, $parentName = '/') {
		return new static($options, $parentName);
	}

	static public function getSequence($sequence) {
		if(property_exists(get_called_class(), $sequence)) {
			return static::$$sequence;
		}
	}

	public function __construct(Options $options, $parentName = '/') {
		$this->options($options);

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

	public function getSubSectionCount() {
		$return = 0;

		foreach($this->subSections as $section) {
			$return += count($section);
		}

		return $return;
	}

	protected function options(Options $setOptions = null) {
		static $options = null;

		if(is_null($options) && !is_null($setOptions)) {
			$options = $setOptions;
		}

		return $options;
	}

	protected function resolveAlias($alias) {
		return $this->options()->get('Aliases.'.$alias);
	}

	protected function parseSequence(array $sequence, Raw $raw, array &$objects) {
// echo $this->getName(true).' Parse Sequence'.PHP_EOL;
		$sectionDataTemplate = [
			'name' => '',
			'required' => true,
			'repeat' => 1,
		];

		$status = false;

		foreach($sequence as $sectionData) {
			$sectionData = array_merge($sectionDataTemplate, $sectionData);

			$sectionData['class'] = $this->resolveAlias($sectionData['name']);

			if(get_parent_class($sectionData['class']) !== Segment::class ||
				$raw->getSegmentDesignator() === $sectionData['name']
			) {
				$parsed = $this->parseSection($sectionData, $raw, $objects);

				if($parsed) {
					$status = true;
				}
			}
		}

// echo 'Parse Segment Status: '.($status ? 'True' : 'False').PHP_EOL;
		return $status;
	}

	protected function parseSection($sectionData, Raw $raw, array &$objects) {
// echo $this->getName(true).' Parse Section'.PHP_EOL;
		$options = $this->options();
		$name = $this->getName(true);

		$status = false;

		do {
			$section = $sectionData['class']::getNew(
				$options,
				$name
			);

			$parsed = $section->parse($raw);

			if($parsed) {
				$status = true;
// echo "Name:\t".$section->getName(true).PHP_EOL;
				$objects[] = $section;
			}

			$sectionData['repeat']--;

		} while($sectionData['repeat'] !== 0 && $parsed);

// echo 'Parse Section Status: '.($status ? 'True' : 'False').PHP_EOL;
		return $status;
	}

	public function __toString() {
		$return = '';

		foreach($this->subSections as $section) {
			$return .= implode(
				$this->subSectionDelimiter,
				$section
			);
		}

		return $return;
	}

}