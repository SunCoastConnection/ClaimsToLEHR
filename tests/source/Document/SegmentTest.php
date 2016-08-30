<?php

namespace SunCoastConnection\ClaimsToOEMR\Tests\Document;

use \SunCoastConnection\ClaimsToOEMR\Tests\BaseTestCase,
	\SunCoastConnection\ClaimsToOEMR\Document\Options,
	\SunCoastConnection\ClaimsToOEMR\Document\Raw,
	\SunCoastConnection\ClaimsToOEMR\Document\Segment;

class SegmentTest extends BaseTestCase {

	protected $segment;

	public function setUp() {
		parent::setUp();

		$this->segment = $this->getMockery(
			Segment::class
		)->makePartial();

	}

	/**
	 * @covers SunCoastConnection\ClaimsToOEMR\Document\Segment::getElementNames()
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
	 * @covers SunCoastConnection\ClaimsToOEMR\Document\Segment::__construct()
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
	 * @covers SunCoastConnection\ClaimsToOEMR\Document\Segment::Parse()
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
	 * @covers SunCoastConnection\ClaimsToOEMR\Document\Segment::Parse()
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
				'data' => [
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
	 * @covers SunCoastConnection\ClaimsToOEMR\Document\Segment::elementExists()
	 */
	public function testElementExistsWithMissingElement() {
		$this->assertFalse(
			$this->segment->elementExists('AAA'),
			'Element should not have been found.'
		);
	}

	/**
	 * @covers SunCoastConnection\ClaimsToOEMR\Document\Segment::elementExists()
	 */
	public function testElementExistsWithExistingElement() {
		$this->setProtectedProperty(
			$this->segment,
			'subSections',
			[ 'data' => [ 'AAA' => 'true' ] ]
		);

		$this->assertTrue(
			$this->segment->elementExists('AAA'),
			'Element should have been found.'
		);
	}

	/**
	 * @covers SunCoastConnection\ClaimsToOEMR\Document\Segment::subElementExists()
	 */
	public function testSubElementExistsWithMissingElement() {
		$this->assertFalse(
			$this->segment->subElementExists('AAA', 0),
			'Element should not have been found.'
		);
	}

	/**
	 * @covers SunCoastConnection\ClaimsToOEMR\Document\Segment::subElementExists()
	 */
	public function testSubElementExistsWithMissingSubElement() {
		$this->setProtectedProperty(
			$this->segment,
			'subSections',
			[ 'data' => [ 'AAA' => 'true' ] ]
		);

		$this->assertFalse(
			$this->segment->subElementExists('AAA', 0),
			'Sub-element should not have been found.'
		);
	}

	/**
	 * @covers SunCoastConnection\ClaimsToOEMR\Document\Segment::subElementExists()
	 */
	public function testSubElementExistsWithExistingSubElement() {
		$this->setProtectedProperty(
			$this->segment,
			'subSections',
			[ 'data' => [ 'AAA' => [ 'B', 'C' ] ] ]
		);

		$this->assertTrue(
			$this->segment->subElementExists('AAA', 0),
			'Sub-element should have been found.'
		);
	}

	/**
	 * @covers SunCoastConnection\ClaimsToOEMR\Document\Segment::element()
	 */
	public function testElementWithMissingElement() {
		$this->assertNull(
			$this->segment->element('AAA'),
			'Element should not have been found.'
		);
	}

	/**
	 * @covers SunCoastConnection\ClaimsToOEMR\Document\Segment::element()
	 */
	public function testElementWithExistingElement() {
		$this->setProtectedProperty(
			$this->segment,
			'subSections',
			[ 'data' => [ 'AAA' => 'true' ] ]
		);

		$this->assertSame(
			'true',
			$this->segment->element('AAA'),
			'Element should have been found.'
		);
	}

	/**
	 * @covers SunCoastConnection\ClaimsToOEMR\Document\Segment::subElement()
	 */
	public function testSubElementWithMissingElement() {
		$this->assertNull(
			$this->segment->subElement('AAA', 0),
			'Element should not have been found.'
		);
	}

	/**
	 * @covers SunCoastConnection\ClaimsToOEMR\Document\Segment::subElement()
	 */
	public function testSubElementWithMissingSubElement() {
		$this->setProtectedProperty(
			$this->segment,
			'subSections',
			[ 'data' => [ 'AAA' => 'true' ] ]
		);

		$this->assertNull(
			$this->segment->subElement('AAA', 0),
			'Sub-element should not have been found.'
		);
	}

	/**
	 * @covers SunCoastConnection\ClaimsToOEMR\Document\Segment::subElement()
	 */
	public function testSubElementWithExistingSubElement() {
		$this->setProtectedProperty(
			$this->segment,
			'subSections',
			[ 'data' => [ 'AAA' => [ 'B', 'C' ] ] ]
		);

		$this->assertSame(
			'B',
			$this->segment->subElement('AAA', 0),
			'Sub-element should have been found.'
		);
	}

	/**
	 * @covers SunCoastConnection\ClaimsToOEMR\Document\Segment::elementEquals()
	 */
	public function testElementEqualsWithMissingElement() {
		$this->assertFalse(
			$this->segment->elementEquals('AAA', 'true'),
			'Element should not have been found.'
		);
	}

	/**
	 * @covers SunCoastConnection\ClaimsToOEMR\Document\Segment::elementEquals()
	 */
	public function testElementEqualsWithWrongValue() {
		$this->setProtectedProperty(
			$this->segment,
			'subSections',
			[ 'data' => [ 'AAA' => 'true' ] ]
		);

		$this->assertFalse(
			$this->segment->elementEquals('AAA', 'false'),
			'Element value should not have matched.'
		);
	}

	/**
	 * @covers SunCoastConnection\ClaimsToOEMR\Document\Segment::elementEquals()
	 */
	public function testElementEqualsWithCorrectValue() {
		$this->setProtectedProperty(
			$this->segment,
			'subSections',
			[ 'data' => [ 'AAA' => 'true' ] ]
		);

		$this->assertTrue(
			$this->segment->elementEquals('AAA', 'true'),
			'Element value should have matched.'
		);
	}

	/**
	 * @covers SunCoastConnection\ClaimsToOEMR\Document\Segment::subElementEquals()
	 */
	public function testSubElementEqualsWithMissingElement() {
		$this->assertFalse(
			$this->segment->subElementEquals('AAA', 0, 'true'),
			'Element should not have been found.'
		);
	}

	/**
	 * @covers SunCoastConnection\ClaimsToOEMR\Document\Segment::subElementEquals()
	 */
	public function testSubElementEqualsWithMissingSubElement() {
		$this->setProtectedProperty(
			$this->segment,
			'subSections',
			[ 'data' => [ 'AAA' => 'true' ] ]
		);

		$this->assertFalse(
			$this->segment->subElementEquals('AAA', 0, 'true'),
			'Sub-element should not have been found.'
		);
	}

	/**
	 * @covers SunCoastConnection\ClaimsToOEMR\Document\Segment::subElementEquals()
	 */
	public function testSubElementEqualsWithWrongValue() {
		$this->setProtectedProperty(
			$this->segment,
			'subSections',
			[ 'data' => [ 'AAA' => [ 'B', 'C' ] ] ]
		);

		$this->assertFalse(
			$this->segment->subElementEquals('AAA', 0, 'false'),
			'Sub-element value should not have matched.'
		);
	}

	/**
	 * @covers SunCoastConnection\ClaimsToOEMR\Document\Segment::subElementEquals()
	 */
	public function testSubElementEqualsWithCorrectValue() {
		$this->setProtectedProperty(
			$this->segment,
			'subSections',
			[ 'data' => [ 'AAA' => [ 'B', 'C' ] ] ]
		);

		$this->assertTrue(
			$this->segment->subElementEquals('AAA', 0, 'B'),
			'Sub-element value should have matched.'
		);
	}

	/**
	 * @covers SunCoastConnection\ClaimsToOEMR\Document\Segment::subElementCount()
	 */
	public function testSubElementCountWithMissingElement() {
		$this->assertEquals(
			0,
			$this->segment->subElementCount('AAA'),
			'Sub-element should not have been found.'
		);
	}

	/**
	 * @covers SunCoastConnection\ClaimsToOEMR\Document\Segment::subElementCount()
	 */
	public function testSubElementCountWithNoSubElements() {
		$this->setProtectedProperty(
			$this->segment,
			'subSections',
			[ 'data' => [ 'AAA' => 'true' ] ]
		);

		$this->assertEquals(
			0,
			$this->segment->subElementCount('AAA'),
			'Sub-element count should have been 0.'
		);
	}

	/**
	 * @covers SunCoastConnection\ClaimsToOEMR\Document\Segment::subElementCount()
	 */
	public function testSubElementCountWithSubElements() {
		$this->setProtectedProperty(
			$this->segment,
			'subSections',
			[ 'data' => [ 'AAA' => [ 'B', 'C' ] ] ]
		);

		$this->assertEquals(
			2,
			$this->segment->subElementCount('AAA'),
			'Sub-element count should have been 2.'
		);
	}

	/**
	 * @covers SunCoastConnection\ClaimsToOEMR\Document\Segment::__toString()
	 */
	public function testToString() {
		// $this->markTestIncomplete('Not yet implemented');

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
				'data' => [
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