<?php

namespace SunCoastConnection\ClaimsToOEMR\Tests\Document;

use \SunCoastConnection\ClaimsToOEMR\Tests\BaseTestCase,
	\SunCoastConnection\ClaimsToOEMR\Document\Loop,
	\SunCoastConnection\ClaimsToOEMR\Document\Raw,
	\SunCoastConnection\ClaimsToOEMR\Document\Section;

class LoopTest extends BaseTestCase {

	protected $loop;

	public function setUp() {
		parent::setUp();

		$this->loop = $this->getMockery(
			Loop::class
		)->makePartial();
	}

	/**
	 * @covers SunCoastConnection\ClaimsToOEMR\Document\Loop::parse()
	 */
	public function testParseWithNoHeaderSegment() {
		$raw = $this->getMockery(
			Raw::class
		)->makePartial();

		$this->loop->shouldAllowMockingProtectedMethods();

		$this->loop->shouldReceive('getSequence')
			->once()
			->with('headerSequence')
			->andReturn([]);

		$this->loop->shouldReceive('parseSequence')
			->andReturn(false);

		$this->assertFalse(
			$this->loop->parse($raw),
			'Parse should have returned false.'
		);

		$this->assertEquals(
			0,
			$this->getProtectedProperty(
				$this->loop,
				'subSectionCount'
			),
			'Sub-section count not set correctly.'
		);
	}

	/**
	 * @covers SunCoastConnection\ClaimsToOEMR\Document\Loop::parse()
	 */
	public function testParseWithHeaderSegment() {
		$raw = $this->getMockery(
			Raw::class
		)->makePartial();

		$this->loop->shouldAllowMockingProtectedMethods();

		$this->loop->shouldReceive('getSequence')
			->with('headerSequence')
			->andReturn([]);

		$this->loop->shouldReceive('parseSequence')
			->andReturn(true);

		$this->loop->shouldReceive('getSequence')
			->with('descendantSequence')
			->andReturn([]);

		$this->loop->shouldReceive('parseSequence')
			->andReturn(true);

		$this->assertTrue(
			$this->loop->parse($raw),
			'Parse should have returned false.'
		);

		$this->assertEquals(
			0,
			$this->getProtectedProperty(
				$this->loop,
				'subSectionCount'
			),
			'Sub-section count not set correctly.'
		);
	}

	/**
	 * @covers SunCoastConnection\ClaimsToOEMR\Document\Loop::__toString()
	 */
	public function testToString() {
		$this->setProtectedProperty(
			$this->loop,
			'header',
			[
				'A',
				'B',
			]
		);

		$this->setProtectedProperty(
			$this->loop,
			'descendant',
			[
				'C',
				'D',
				'E',
			]
		);

		$this->assertSame(
			'ABCDE',
			(string) $this->loop,
			'Loop object did not return the correct string.'
		);
	}

}