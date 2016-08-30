<?php

namespace SunCoastConnection\ClaimsToOEMR\Tests\Document\Raw;

use \SunCoastConnection\ClaimsToOEMR\Tests\BaseTestCase,
	\SunCoastConnection\ClaimsToOEMR\Document\Options,
	\SunCoastConnection\ClaimsToOEMR\Document\Raw,
	\SunCoastConnection\ClaimsToOEMR\Document\Raw\Segment;

class SegmentTest extends BaseTestCase {

	protected $segment;

	public function setUp() {
		parent::setUp();

		$this->segment = $this->getMockery(
			Segment::class
		)->makePartial();
	}

	/**
	 * @covers SunCoastConnection\ClaimsToOEMR\Document\Raw\Segment::getElementNames()
	 */
	public function testGetElementNames() {
		$elementNames = [
			'ABC01' => 'First element name',
			'ABC01' => 'Second element name',
			'ABC01' => 'Third element name',
			'ABC01' => 'Fourth element name',
			'ABC01' => 'Fifth element name',
		];

		$this->setProtectedProperty(
			$this->segment,
			'elementNames',
			$elementNames
		);

		$this->assertEquals(
			$elementNames,
			$this->segment->getElementNames(),
			'Array of element names was expected.'
		);
	}

	/**
	 * @covers SunCoastConnection\ClaimsToOEMR\Document\Raw\Segment::__construct()
	 */
	public function testConstruct() {
		$options = $this->getMockery(
			Options::class
		)->makePartial();

		$options->set('Document.delimiters.data', '*');

		$this->segment->shouldAllowMockingProtectedMethods()
			->shouldReceive('options')
			->once()
			->with($options);

		$this->segment->__construct($options);

		$this->assertEquals(
			'*',
			$this->getProtectedProperty(
				$this->segment,
				'subSectionDelimiter'
			),
			'Sub-Section delimiter not set correctly.'
		);
	}

	/**
	 * @covers SunCoastConnection\ClaimsToOEMR\Document\Raw\Segment::Parse()
	 */
	public function testParseWithDesignatorNotMachingName() {
		// $this->markTestIncomplete('Not yet implemented');

		$raw = $this->getMockery(
			Raw::class
		);

		$raw->shouldReceive('getSegmentDesignator')
			->once()
			->andReturn('CD');

		$this->segment->shouldReceive('getName')
			->once()
			->andReturn('AB');

		$this->assertFalse(
			$this->segment->parse($raw),
			'Segment mismatch should return false.'
		);
	}

	/**
	 * @covers SunCoastConnection\ClaimsToOEMR\Document\Raw\Segment::Parse()
	 */
	public function testParseWithDesignatorMachingName() {
		// $this->markTestIncomplete('Not yet implemented');
		$elements = [
			'C',
			'D',
			'E',
			':',
			'123'
		];

		$sequence = [
			['name' => 'AB01', 'required' => true],
			['name' => 'AB02', 'required' => true],
			['name' => 'AB03', 'required' => false],
			['name' => 'AB04', 'required' => false],
			['name' => 'AB05', 'required' => false],
		];

		$options = $this->getMockery(
			Options::class
		)->makePartial();

		$options->set('Document.delimiters.component', ':');

		$this->segment->shouldAllowMockingProtectedMethods()
			->shouldReceive('options')
			->andReturn($options);

		$raw = $this->getMockery(
			Raw::class
		);

		$raw->shouldReceive('getSegmentDesignator')
			->once()
			->andReturn('AB');

		$this->segment->shouldReceive('getName')
			->once()
			->andReturn('AB');

		$raw->shouldReceive('getSegmentElements')
			->once()
			->andReturn($elements);

		$this->segment->shouldReceive('getSequence')
			->once()
			->with('elementSequence')
			->andReturn($sequence);

		$raw->shouldReceive('next')
			->once();

		$this->assertTrue(
			$this->segment->parse($raw),
			'Segment match should return true.'
		);

		$this->assertCount(
			1,
			$this->getProtectedProperty(
				$this->segment,
				'subSections'
			),
			'Sub-section count should return 1.'
		);

		$this->assertEquals(
			[
				'elements' => [
							'AB01' => 'C',
							'AB02' => 'D',
							'AB03' => 'E',
							'AB04' => ['', ''],
							'AB05' => '123'
				]
			],
			$this->getProtectedProperty(
				$this->segment,
				'subSections'
			),
			'Elements were not set correctly.'
		);
	}

	/**
	 * @covers SunCoastConnection\ClaimsToOEMR\Document\Raw\Segment::elementExists()
	 */
	public function testElementExistsWithMissingElement() {
		$this->assertFalse(
			$this->segment->elementExists('AAA'),
			'Element should not have been found.'
		);
	}

	/**
	 * @covers SunCoastConnection\ClaimsToOEMR\Document\Raw\Segment::elementExists()
	 */
	public function testElementExistsWithExistingElement() {
		$this->setProtectedProperty(
			$this->segment,
			'subSections',
			[ 'elements' => [ 'AAA' => 'true' ] ]
		);

		$this->assertTrue(
			$this->segment->elementExists('AAA'),
			'Element should have been found.'
		);
	}

	/**
	 * @covers SunCoastConnection\ClaimsToOEMR\Document\Raw\Segment::element()
	 */
	public function testElementWithMissingElement() {
		$this->assertNull(
			$this->segment->element('AAA'),
			'Element should not have been found.'
		);
	}

	/**
	 * @covers SunCoastConnection\ClaimsToOEMR\Document\Raw\Segment::element()
	 */
	public function testElementWithExistingElement() {
		$this->setProtectedProperty(
			$this->segment,
			'subSections',
			[ 'elements' => [ 'AAA' => 'true' ] ]
		);

		$this->assertSame(
			'true',
			$this->segment->element('AAA'),
			'Element should have been found.'
		);
	}

	/**
	 * @covers SunCoastConnection\ClaimsToOEMR\Document\Raw\Segment::elementEquals()
	 */
	public function testElementEqualsWithMissingElement() {
		$this->assertFalse(
			$this->segment->elementEquals('AAA', 'true'),
			'Element should not have been found.'
		);
	}

	/**
	 * @covers SunCoastConnection\ClaimsToOEMR\Document\Raw\Segment::elementEquals()
	 */
	public function testElementEqualsWithWrongValue() {
		$this->setProtectedProperty(
			$this->segment,
			'subSections',
			[ 'elements' => [ 'AAA' => 'true' ] ]
		);

		$this->assertFalse(
			$this->segment->elementEquals('AAA', 'false'),
			'Element value should not have matched.'
		);
	}

	/**
	 * @covers SunCoastConnection\ClaimsToOEMR\Document\Raw\Segment::elementEquals()
	 */
	public function testElementEqualsWithCorrectValue() {
		$this->setProtectedProperty(
			$this->segment,
			'subSections',
			[ 'elements' => [ 'AAA' => 'true' ] ]
		);

		$this->assertTrue(
			$this->segment->elementEquals('AAA', 'true'),
			'Element value should have matched.'
		);
	}

	/**
	 * @covers SunCoastConnection\ClaimsToOEMR\Document\Raw\Segment::__toString()
	 */
	public function testToString() {
		$options = $this->getMockery(
			Options::class
		)->makePartial();

		$options->set('Document.delimiters.component', ':');
		$options->set('Document.delimiters.segment', '~');

		$this->segment->shouldAllowMockingProtectedMethods()
			->shouldReceive('options')
			->andReturn($options);

		$this->setProtectedProperty(
			$this->segment,
			'subSections',
			[
				'elements' => [
					'AB01' => 'C',
					'AB02' => 'D',
					'AB03' => 'E',
					'AB04' => [ '', '' ],
					'AB05' => '123'
				]
			]
		);

		$this->setProtectedProperty(
			$this->segment,
			'subSectionDelimiter',
			'*'
		);

		$this->segment->shouldReceive('getName')
			->once()
			->andReturn('AB');

		$this->assertEquals(
			'AB*C*D*E*:*123~',
			(string) $this->segment,
			'Segment object did not return the correct string.'
		);
	}

}