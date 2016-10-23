<?php

namespace SunCoastConnection\ClaimsToOEMR\Document;

use \SunCoastConnection\ClaimsToOEMR\Document\Options;
use \SunCoastConnection\ClaimsToOEMR\Document\Raw;
use \SunCoastConnection\ClaimsToOEMR\Document\Raw\Segment;

abstract class Section {

	/**
	 * Name of section
	 * @var string
	 */
	static protected $name;

	/**
	 * Section parent name
	 * @var string
	 */
	protected $parentName;

	/**
	 * Sub-sections of section
	 * @var array
	 */
	protected $subSections = [];

	/**
	 * Delimiter to separate sub-sections
	 * @var string
	 */
	protected $subSectionDelimiter = '';

	/**
	 * Parse sub-section
	 *
	 * @param  \SunCoastConnection\ClaimsToOEMR\Document\Raw  $raw  Raw X12 document object
	 *
	 * @return boolean  True if section was parsable or false otherwise
	 */
	abstract public function parse(Raw $raw);

	/**
	 * Get instance of section class with provided options
	 *
	 * @param  \SunCoastConnection\ClaimsToOEMR\Document\Options  $options     Options to create section object with
	 * @param  string                                             $parentName  Section parent name
	 *
	 * @return \SunCoastConnection\ClaimsToOEMR\Document\Raw  Raw object
	 */
	static public function getInstance(Options $options, $parentName = '/') {
		return new static($options, $parentName);
	}

	/**
	 * Return section named sequence
	 *
	 * @param  string  $sequence  Name of sequence
	 *
	 * @return array  Named sequence
	 */
	static public function getSequence($sequence) {
		if(property_exists(get_called_class(), $sequence)) {
			return static::$$sequence;
		}
	}

	/**
	 * Create a new Section
	 *
	 * @param  \SunCoastConnection\ClaimsToOEMR\Document\Options  $options     Options to create section object with
	 * @param  string                                             $parentName  Section parent name
	 */
	public function __construct(Options $options, $parentName = '/') {
		$this->options($options);

		$this->parentName = $parentName;
	}

	/**
	 * Return section name, optionally with full parent name
	 *
	 * @param  boolean  $full  Set to true to return section name with full parent name
	 *
	 * @return string   Section name
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
	 * Return counts from sub-sections
	 *
	 * @return integer  Count from sub-sections
	 */
	public function getSubSectionCount() {
		$return = 0;

		foreach($this->subSections as $section) {
			$return += count($section);
		}

		return $return;
	}

	/**
	 * Set section options or retrieve section options
	 *
	 * @param  \SunCoastConnection\ClaimsToOEMR\Document\Options|null  $setOptions  Options to set section object with
	 *
	 * @return \SunCoastConnection\ClaimsToOEMR\Document\Options|null  Section options or null when not set
	 */
	protected function options(Options $setOptions = null) {
		static $options = null;

		if(is_null($options) && !is_null($setOptions)) {
			$options = $setOptions;
		}

		return $options;
	}

	/**
	 * Resolve alias name to class name
	 *
	 * @param  string  $alias  Alias name
	 *
	 * @return string  Class name
	 */
	protected function resolveAlias($alias) {
		return $this->options()->get('Aliases.'.$alias);
	}

	/**
	 * Find segments and add to sub-sections
	 *
	 * @param  array                                          $sequence  Sequence array for section
	 * @param  \SunCoastConnection\ClaimsToOEMR\Document\Raw  $raw       Raw object containing segments
	 * @param  array                                          &$objects  Array of sub-sections to add to
	 *
	 * @return boolean  True if sub-section added to objects, false otherwise
	 */
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

	/**
	 * Find segments and add to section
	 *
	 * @param  array                                          $sectionData  Sub-section data
	 * @param  \SunCoastConnection\ClaimsToOEMR\Document\Raw  $raw          Raw object containing segments
	 * @param  array                                          &$objects     Array of sub-sections to add to
	 *
	 * @return boolean  True if sub-section added to objects, false otherwise
	 */
	protected function parseSection(array $sectionData, Raw $raw, array &$objects) {
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

	/**
	 * Get string value of section
	 *
	 * @return string  Raw value of section, containing all sub-sections separated by configured delimiter
	 */
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