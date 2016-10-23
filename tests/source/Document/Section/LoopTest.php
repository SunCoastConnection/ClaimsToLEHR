<?php

namespace SunCoastConnection\ClaimsToOEMR\Tests\Document\Section;

use \SunCoastConnection\ClaimsToOEMR\Tests\BaseTestCase;
use \SunCoastConnection\ClaimsToOEMR\Document\Raw;
use \SunCoastConnection\ClaimsToOEMR\Document\Section;
use \SunCoastConnection\ClaimsToOEMR\Document\Section\Loop;

class LoopTest extends BaseTestCase {

	protected $loop;

	public function setUp() {
		parent::setUp();

		$this->loop = $this->getMockery(
			Loop::class
		)->makePartial();
	}

	/**
	 * @covers SunCoastConnection\ClaimsToOEMR\Document\Section\Loop::parse()
	 */
	public function testParseWithNoHeaderSegment() {
		$raw = $this->getMockery(
			Raw::class
		);

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
			$this->loop->getSubSectionCount(),
			'Sub-section count not set correctly.'
		);
	}

	/**
	 * @covers SunCoastConnection\ClaimsToOEMR\Document\Section\Loop::parse()
	 */
	public function testParseWithHeaderSegment() {
		$raw = $this->getMockery(
			Raw::class
		);

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
			$this->loop->getSubSectionCount(),
			'Sub-section count not set correctly.'
		);
	}

	/**
	 * @covers SunCoastConnection\ClaimsToOEMR\Document\Section\Loop::getHeader()
	 */
	public function testGetHeader() {
		$header = [
			'A',
			'B',
			'C',
		];

		$this->setProtectedProperty(
			$this->loop,
			'subSections',
			['header' => $header]
		);

		$this->assertEquals(
			$header,
			$this->loop->getHeader(),
			'Header value not returned.'
		);
	}

	/**
	 * @covers SunCoastConnection\ClaimsToOEMR\Document\Section\Loop::getDescendant()
	 */
	public function testGetDescendant() {
		$descendant = [
			'A',
			'B',
			'C',
		];

		$this->setProtectedProperty(
			$this->loop,
			'subSections',
			['descendant' => $descendant]
		);

		$this->assertEquals(
			$descendant,
			$this->loop->getDescendant(),
			'Descendant value not returned.'
		);
	}

	/**
	 * @covers SunCoastConnection\ClaimsToOEMR\Document\Section\Loop::__toString()
	 */
	public function testToString() {
		$this->setProtectedProperty(
			$this->loop,
			'subSections',
			[
				'header' => [
					'A',
					'B',
				],
				'descendant' => [
					'C',
					'D',
					'E',
				],
			]
		);

		$this->assertSame(
			'ABCDE',
			(string) $this->loop,
			'Loop object did not return the correct string.'
		);
	}

}