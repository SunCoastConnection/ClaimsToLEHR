<?php

namespace SunCoastConnection\ClaimsToOEMR\Tests\Document;

use \SunCoastConnection\ClaimsToOEMR\Tests\BaseTestCase,
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

		$this->assertEquals(
			1,
			$this->getProtectedProperty(
				$this->segment,
				'subSectionCount'
			),
			'Sub-section count should return 1.'
		);

		$this->assertEquals(
			[
				'AB01' => 'C',
				'AB02' => 'D',
				'AB03' => 'E',
				'AB04' => ':',
				'AB05' => '123'
			],
			$this->getProtectedProperty(
				$this->segment,
				'data'
			),
			'Elements were not set correctly.'
		);
	}

	/**
	 * @covers SunCoastConnection\ClaimsToOEMR\Document\Segment::__toString()
	 */
	public function testToString() {
		// $this->markTestIncomplete('Not yet implemented');

		$this->setProtectedProperty(
			$this->segment,
			'data',
			[
				'AB01' => 'C',
				'AB02' => 'D',
				'AB03' => 'E',
				'AB04' => ':',
				'AB05' => '123'
			]
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