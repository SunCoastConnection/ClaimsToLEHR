<?php

namespace SunCoastConnection\ClaimsToOEMR\Tests\Document;

use \SunCoastConnection\ClaimsToOEMR\Tests\BaseTestCase,
	\SunCoastConnection\ClaimsToOEMR\Tests\Document\SectionMock,
	\SunCoastConnection\ClaimsToOEMR\Document\Options,
	\SunCoastConnection\ClaimsToOEMR\Document\Raw,
	\SunCoastConnection\ClaimsToOEMR\Document\Section,
	\SunCoastConnection\ClaimsToOEMR\Document\Segment;

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
	 * @covers SunCoastConnection\ClaimsToOEMR\Document\Section::getNew()
	 */
	public function testGetNew() {
		$options = $this->getMockery(
			Options::class
		)->makePartial();

		$section = SectionMock::getNew($options);

		$this->assertInstanceOf(
			Section::class,
			$section,
			'Expected new instance of '.Section::class.'.'
		);
	}

	/**
	 * @covers SunCoastConnection\ClaimsToOEMR\Document\Section::getSequence()
	 */
	public function testGetSequence() {
		$this->assertEquals(
			SectionMock::$testSequence,
			SectionMock::getSequence('testSequence'),
			'Returned sequence incorrect.'
		);
	}

	/**
	 * @covers SunCoastConnection\ClaimsToOEMR\Document\Section::getSequence()
	 */
	public function testGetSequenceWithMissing() {
		$this->assertEquals(
			null,
			SectionMock::getSequence('testMissingSequence'),
			'Returned sequence incorrect.'
		);
	}

	/**
	 * @covers SunCoastConnection\ClaimsToOEMR\Document\Section::__construct()
	 */
	public function testConstruct() {
		$options = $this->getMockery(
			Options::class
		)->makePartial();

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
	 * @covers SunCoastConnection\ClaimsToOEMR\Document\Section::getName()
	 */
	public function testGetName() {
		$this->assertEquals(
			get_class($this->section),
			$this->section->getName(),
			'Name not returned correctly.'
		);
	}

	/**
	 * @covers SunCoastConnection\ClaimsToOEMR\Document\Section::getName()
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
	 * @covers SunCoastConnection\ClaimsToOEMR\Document\Section::getName()
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
	 * @covers SunCoastConnection\ClaimsToOEMR\Document\Section::getSubSectionCount()
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
	 * @covers SunCoastConnection\ClaimsToOEMR\Document\Section::options()
	 */
	public function testOptions() {
		$this->assertNull(
			$this->section->options(),
			'Options should return null when empty.'
		);

		$options = $this->getMockery(
			Options::class
		)->makePartial();

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
	 * @covers SunCoastConnection\ClaimsToOEMR\Document\Section::resolveAlias()
	 */
	public function testResolveAlias() {
		$alias = 'TestAlias';
		$class = '/Namespace/To/TestClass';

		$this->section->shouldAllowMockingProtectedMethods()
			->shouldReceive('options->get')
			->once()
			->with('Aliases.'.$alias)
			->andReturn($class);

		$this->assertSame(
			$class,
			$this->section->resolveAlias($alias),
			'Alias did not return correct class string.'
		);
	}

	/**
	 * @covers SunCoastConnection\ClaimsToOEMR\Document\Section::parseSequence()
	 */
	public function testParseSequenceWithEmptySequence() {
		$sequence = [];

		$raw = $this->getMockery(
			Raw::class
		)->makePartial();

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
	 * @covers SunCoastConnection\ClaimsToOEMR\Document\Section::parseSequence()
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

		$this->assertEquals(
			Segment::class,
			get_parent_class($class),
			'Mock Segment class parent should be Segment.'
		);

		$this->section->shouldAllowMockingProtectedMethods();

		$this->section->shouldReceive('resolveAlias')
			->once()
			->with($sequence[0]['name'])
			->andReturn($class);

		$raw->shouldReceive('getSegmentDesignator')
			->times(1)
			->andReturn('CD');

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
	 * @covers SunCoastConnection\ClaimsToOEMR\Document\Section::parseSequence()
	 */
	public function testParseSequenceWithSegmentSectionSameAsDesignator() {
		// $this->markTestIncomplete('Not yet implemented');

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

		$this->assertEquals(
			Segment::class,
			get_parent_class($class),
			'Mock Segment class parent should be Segment.'
		);

		$this->section->shouldAllowMockingProtectedMethods();

		$this->section->shouldReceive('resolveAlias')
			->once()
			->with($sequence[0]['name'])
			->andReturn($class);

		$raw->shouldReceive('getSegmentDesignator')
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
	 * @covers SunCoastConnection\ClaimsToOEMR\Document\Section::parseSection()
	 */
	public function testParseSectionWithFailedParse() {
		// $this->markTestIncomplete('Not yet implemented');

		$options = $this->getMockery(
			Options::class
		);

		$class = $this->getMockery(
			Segment::class
		);

		$sectionData = [
			'repeat' => 1,
			'class' => get_class($class)
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

		$class->shouldReceive('getNew')
			->once()
			->andReturn($class);

		$class->shouldReceive('parse')
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
	 * @covers SunCoastConnection\ClaimsToOEMR\Document\Section::parseSection()
	 */
	public function testParseSectionWithSuccessfulParse() {
		// $this->markTestIncomplete('Not yet implemented');

		$options = $this->getMockery(
			Options::class
		);

		$class = $this->getMockery(
			Segment::class
		);

		$sectionData = [
			'repeat' => 1,
			'class' => get_class($class)
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

		$class->shouldReceive('getNew')
			->once()
			->andReturn($class);

		$class->shouldReceive('parse')
			->once()
			->andReturn(true);

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
			'Process did not succeed.'
		);
	}

}