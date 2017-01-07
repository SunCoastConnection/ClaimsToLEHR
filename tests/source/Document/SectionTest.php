<?php

namespace SunCoastConnection\ClaimsToEMR\Tests\Document;

use \SunCoastConnection\ClaimsToEMR\Tests\BaseTestCase;
use \SunCoastConnection\ClaimsToEMR\Tests\Document\SectionMock;
use \SunCoastConnection\ClaimsToEMR\Document\Options;
use \SunCoastConnection\ClaimsToEMR\Document\Raw;
use \SunCoastConnection\ClaimsToEMR\Document\Section;
use \SunCoastConnection\ClaimsToEMR\Document\Raw\Segment;

class SectionTest extends BaseTestCase {

	protected $section;

	public function setUp() {
		parent::setUp();

		$this->section = $this->getMockery(
			SectionMock::class
		)->makePartial();
	}

	public function tearDown() {

		parent::tearDown();
	}

	/**
	 * @covers SunCoastConnection\ClaimsToEMR\Document\Section::getInstance()
	 */
	public function testGetInstance() {
		$options = $this->getMockery(
			Options::class
		);

		$section = SectionMock::getInstance($options);

		$this->assertInstanceOf(
			Section::class,
			$section,
			'Expected new instance of '.Section::class.'.'
		);
	}

	/**
	 * @covers SunCoastConnection\ClaimsToEMR\Document\Section::getSequence()
	 */
	public function testGetSequence() {
		$this->assertEquals(
			SectionMock::$testSequence,
			SectionMock::getSequence('testSequence'),
			'Returned sequence incorrect.'
		);
	}

	/**
	 * @covers SunCoastConnection\ClaimsToEMR\Document\Section::getSequence()
	 */
	public function testGetSequenceWithMissing() {
		$this->assertEquals(
			null,
			SectionMock::getSequence('testMissingSequence'),
			'Returned sequence incorrect.'
		);
	}

	/**
	 * @covers SunCoastConnection\ClaimsToEMR\Document\Section::__construct()
	 */
	public function testConstruct() {
		$options = $this->getMockery(
			Options::class
		);

		$parentName = '/ROOT';

		$this->section->shouldAllowMockingProtectedMethods()
			->shouldReceive('options')
			->once()
			->with($options);

		$this->section->__construct($options, $parentName);

		$this->assertEquals(
			$parentName,
			$this->getProtectedProperty(
				$this->section,
				'parentName'
			),
			'Parent name not set correctly.'
		);
	}

	/**
	 * @covers SunCoastConnection\ClaimsToEMR\Document\Section::getName()
	 */
	public function testGetName() {
		$this->assertEquals(
			get_class($this->section),
			$this->section->getName(),
			'Name not returned correctly.'
		);
	}

	/**
	 * @covers SunCoastConnection\ClaimsToEMR\Document\Section::getName()
	 */
	public function testGetNameWithFull() {
		$this->setProtectedProperty(
			$this->section,
			'parentName',
			'/ROOT'
		);

		$this->assertEquals(
			'/ROOT/'.get_class($this->section),
			$this->section->getName(true),
			'Full name not returned correctly.'
		);
	}

	/**
	 * @covers SunCoastConnection\ClaimsToEMR\Document\Section::getName()
	 */
	public function testGetNameWithFullAndNoNameParrent() {
		$this->setProtectedProperty(
			$this->section,
			'parentName',
			'/'
		);

		$this->assertEquals(
			'/'.get_class($this->section),
			$this->section->getName(true),
			'Full name not returned correctly.'
		);
	}

	/**
	 * @covers SunCoastConnection\ClaimsToEMR\Document\Section::getSubSectionCount()
	 */
	public function testGetSubSectionCount() {
		$this->setProtectedProperty(
			$this->section,
			'subSections',
			[1, 2, 3]
		);

		$this->assertEquals(
			3,
			$this->section->getSubSectionCount(),
			'Sub-section count returned incorrectly.'
		);
	}

	/**
	 * @covers SunCoastConnection\ClaimsToEMR\Document\Section::options()
	 */
	public function testOptions() {
		$this->assertNull(
			$this->section->options(),
			'Options should return null when empty.'
		);

		$options = $this->getMockery(
			Options::class
		);

		$this->assertSame(
			$options,
			$this->section->options($options),
			'Options should return set option object when setting value.'
		);

		$this->assertSame(
			$options,
			$this->section->options(),
			'Options should return set option object after setting value.'
		);
	}

	/**
	 * @covers SunCoastConnection\ClaimsToEMR\Document\Section::parseSequence()
	 */
	public function testParseSequenceWithEmptySequence() {
		$sequence = [];

		$raw = $this->getMockery(
			Raw::class
		);

		$objects = [];

		$this->assertFalse(
			$this->callProtectedMethod(
				$this->section,
				'parseSequence',
				[
					$sequence,
					$raw,
					&$objects
				]
			),
			'Process did not succeed.'
		);

		$this->assertCount(
			0,
			$this->getProtectedProperty(
				$this->section,
				'subSections'
			),
			'Sub-section count set incorrectly.'
		);
	}

	/**
	 * @covers SunCoastConnection\ClaimsToEMR\Document\Section::parseSequence()
	 */
	public function testParseSequenceWithSegmentSectionNotSameAsDesignator() {
		$sequence = [
			['name' => 'AB'],
		];

		$raw = $this->getMockery(
			Raw::class
		);

		$objects = [];

		$class = get_class($this->getMockery(
			Segment::class
		));

		$this->section->shouldAllowMockingProtectedMethods();

		$this->section->shouldReceive('options->resolveAlias')
			->once()
			->with($sequence[0]['name'])
			->andReturn($class);

		$raw->shouldReceive('valid')
			->once()
			->andReturn(false);

		$this->assertFalse(
			$this->callProtectedMethod(
				$this->section,
				'parseSequence',
				[
					$sequence,
					$raw,
					&$objects
				]
			),
			'Process did not succeed.'
		);
	}

	/**
	 * @covers SunCoastConnection\ClaimsToEMR\Document\Section::parseSequence()
	 */
	public function testParseSequenceWithSegmentSectionSameAsDesignator() {
		$sequence = [
			['name' => 'AB'],
		];

		$raw = $this->getMockery(
			Raw::class
		);

		$objects = [];

		$class = get_class($this->getMockery(
			Segment::class
		));

		$this->section->shouldAllowMockingProtectedMethods();

		$this->section->shouldReceive('options->resolveAlias')
			->once()
			->with($sequence[0]['name'])
			->andReturn($class);

		$raw->shouldReceive('valid')
			->once()
			->andReturn(true);

		$raw->shouldReceive('current->getName')
			->once()
			->andReturn('AB');

		$this->section->shouldReceive('parseSection')
			->once()
			->andReturn(true);

		$this->assertTrue(
			$this->callProtectedMethod(
				$this->section,
				'parseSequence',
				[
					$sequence,
					$raw,
					&$objects
				]
			),
			'Process did not succeed.'
		);
	}

	/**
	 * @covers SunCoastConnection\ClaimsToEMR\Document\Section::parseSection()
	 */
	public function testParseSectionWithSegment() {
		$options = $this->getMockery(
			Options::class
		);

		$segment = $this->getMockery(
			Segment::class
		);

		$sectionData = [
			'name' => 'AB',
			'repeat' => 2,
			'class' => get_class($segment)
		];

		$raw = $this->getMockery(
			Raw::class
		);

		$objects = [];

		$this->section->shouldAllowMockingProtectedMethods();

		$this->section->shouldReceive('options')
			->once()
			->andReturn($options);

		$raw->shouldReceive('valid')
			->andReturn(true, false);

		$raw->shouldReceive('current')
			->andReturn($segment);

		$segment->shouldReceive('getName')
			->once()
			->andReturn('AB');

		$segment->shouldReceive('setParentName')
			->once()
			->andReturn('AB');

		$raw->shouldReceive('next')
			->once();

		$this->assertTrue(
			$this->callProtectedMethod(
				$this->section,
				'parseSection',
				[
					$sectionData,
					$raw,
					&$objects
				]
			),
			'Process did not succeed'
		);

		$this->assertEquals(
			[
				$segment,
			],
			$objects,
			'Segment not returned in objects'
		);
	}

	/**
	 * @covers SunCoastConnection\ClaimsToEMR\Document\Section::parseSection()
	 */
	public function testParseSectionWithNonSegmentFailedParse() {
		$options = $this->getMockery(
			Options::class
		);

		$section = $this->getMockery(
			Section::class
		);

		$sectionData = [
			'name' => 'AB',
			'repeat' => 2,
			'class' => get_class($section)
		];

		$raw = $this->getMockery(
			Raw::class
		);

		$objects = [];

		$this->section->shouldAllowMockingProtectedMethods();

		$this->section->shouldReceive('options')
			->once()
			->andReturn($options);

		$this->section->shouldReceive('getName')
			->once()
			->with(true)
			->andReturn('/');

		$section->shouldReceive('getInstance')
			->once()
			->andReturn($section);

		$section->shouldReceive('parse')
			->once()
			->andReturn(false);

		$this->assertFalse(
			$this->callProtectedMethod(
				$this->section,
				'parseSection',
				[
					$sectionData,
					$raw,
					&$objects
				]
			),
			'Process did not succeed.'
		);
	}

	/**
	 * @covers SunCoastConnection\ClaimsToEMR\Document\Section::parseSection()
	 */
	public function testParseSectionWithNonSegmentSuccessfulParse() {
		$options = $this->getMockery(
			Options::class
		);

		$section = $this->getMockery(
			Section::class
		);

		$sectionData = [
			'name' => 'AB',
			'repeat' => 2,
			'class' => get_class($section)
		];

		$raw = $this->getMockery(
			Raw::class
		);

		$objects = [];

		$this->section->shouldAllowMockingProtectedMethods();

		$this->section->shouldReceive('options')
			->once()
			->andReturn($options);

		$this->section->shouldReceive('getName')
			->once()
			->with(true)
			->andReturn('/');

		$section->shouldReceive('getInstance')
			->twice()
			->andReturn($section);

		$section->shouldReceive('parse')
			->andReturn(true, false);

		$this->assertTrue(
			$this->callProtectedMethod(
				$this->section,
				'parseSection',
				[
					$sectionData,
					$raw,
					&$objects,
				]
			),
			'Process did not succeed'
		);

		$this->assertEquals(
			[
				$section,
			],
			$objects,
			'Section not returned in objects'
		);

	}

}