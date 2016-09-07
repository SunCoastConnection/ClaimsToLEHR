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

	static public function getInstance(Options $options, $parentName = '/') {
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
				($raw->valid() && $raw->current()->getName() === $sectionData['name'])
			) {
				$parsed = $this->parseSection($sectionData, $raw, $objects);

				if($parsed) {
					$status = true;
				}
			}
		}

		return $status;
	}

	protected function parseSection($sectionData, Raw $raw, array &$objects) {
		$options = $this->options();

		$parentName = $this->getName(true);

		$status = false;

		do {
			$sectionData['repeat']--;

			if(get_parent_class($sectionData['class']) !== Segment::class) {
				$section = $sectionData['class']::getInstance(
					$options,
					$parentName
				);

				$parsed = $section->parse($raw);
			} elseif($raw->valid() && $raw->current()->getName() === $sectionData['name']) {
				// If current segment is matches current section name
				$section = $raw->current();

				$section->setParentName($parentName);

				$raw->next();

				$parsed = true;
			} else {
				$parsed = false;
			}

			if($parsed) {
				$status = true;
				$objects[] = $section;
			}
		} while($sectionData['repeat'] != 0 && $parsed);

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