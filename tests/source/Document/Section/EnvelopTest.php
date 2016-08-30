<?php

namespace SunCoastConnection\ClaimsToOEMR\Tests\Document\Section;

use \SunCoastConnection\ClaimsToOEMR\Tests\BaseTestCase,
	\SunCoastConnection\ClaimsToOEMR\Document\Raw,
	\SunCoastConnection\ClaimsToOEMR\Document\Section,
	\SunCoastConnection\ClaimsToOEMR\Document\Section\Envelop;

class EnvelopTest extends BaseTestCase {

	protected $envelop;

	public function setUp() {
		parent::setUp();

		$this->envelop = $this->getMockery(
			Envelop::class
		)->makePartial();
	}

	/**
	 * @covers SunCoastConnection\ClaimsToOEMR\Document\Section\Envelop::parse()
	 */
	public function testParseWithNoHeaderSegment() {
		$raw = $this->getMockery(
			Raw::class
		)->makePartial();

		$this->envelop->shouldAllowMockingProtectedMethods();

		$this->envelop->shouldReceive('getSequence')
			->once()
			->with('headerSequence')
			->andReturn([]);

		$this->envelop->shouldReceive('parseSequence')
			->andReturn(false);

		$this->assertFalse(
			$this->envelop->parse($raw),
			'Parse should have returned false.'
		);

		$this->assertEquals(
			0,
			$this->envelop->getSubSectionCount(),
			'Sub-section count not set correctly.'
		);
	}

	/**
	 * @covers SunCoastConnection\ClaimsToOEMR\Document\Section\Envelop::parse()
	 */
	public function testParseWithHeaderSegment() {
		$raw = $this->getMockery(
			Raw::class
		)->makePartial();

		$this->envelop->shouldAllowMockingProtectedMethods();

		$this->envelop->shouldReceive('getSequence')
			->with('headerSequence')
			->andReturn([]);

		$this->envelop->shouldReceive('parseSequence')
			->andReturn(true);

		$this->envelop->shouldReceive('getSequence')
			->with('descendantSequence')
			->andReturn([]);

		$this->envelop->shouldReceive('parseSequence')
			->andReturn(true);

		$this->envelop->shouldReceive('getSequence')
			->with('trailerSequence')
			->andReturn([]);

		$this->envelop->shouldReceive('parseSequence')
			->andReturn(true);

		$this->assertTrue(
			$this->envelop->parse($raw),
			'Parse should have returned false.'
		);

		$this->assertEquals(
			0,
			$this->envelop->getSubSectionCount(),
			'Sub-section count not set correctly.'
		);
	}

	/**
	 * @covers SunCoastConnection\ClaimsToOEMR\Document\Section\Envelop::getHeader()
	 */
	public function testGetHeader() {
		$header = [
			'A',
			'B',
			'C',
		];

		$this->setProtectedProperty(
			$this->envelop,
			'subSections',
			['header' => $header]
		);

		$this->assertEquals(
			$header,
			$this->envelop->getHeader(),
			'Header value not returned.'
		);
	}

	/**
	 * @covers SunCoastConnection\ClaimsToOEMR\Document\Section\Envelop::getDescendant()
	 */
	public function testGetDescendant() {
		$descendant = [
			'A',
			'B',
			'C',
		];

		$this->setProtectedProperty(
			$this->envelop,
			'subSections',
			['descendant' => $descendant]
		);

		$this->assertEquals(
			$descendant,
			$this->envelop->getDescendant(),
			'Descendant value not returned.'
		);
	}

	/**
	 * @covers SunCoastConnection\ClaimsToOEMR\Document\Section\Envelop::getTrailer()
	 */
	public function testGetTrailer() {
		$trailer = [
			'A',
			'B',
			'C',
		];

		$this->setProtectedProperty(
			$this->envelop,
			'subSections',
			['trailer' => $trailer]
		);

		$this->assertEquals(
			$trailer,
			$this->envelop->getTrailer(),
			'Trailer value not returned.'
		);
	}

	/**
	 * @covers SunCoastConnection\ClaimsToOEMR\Document\Section\Envelop::__toString()
	 */
	public function testToString() {
		$this->setProtectedProperty(
			$this->envelop,
			'subSections',
			[
				'header' =>  [
					'A',
					'B',
				],
				'descendant' => [
					'C',
					'D',
					'E',
				],
				'trailer' => [
					'F',
					'G',
					'H',
					'I',
				],
			]
		);

		$this->assertSame(
			'ABCDEFGHI',
			(string) $this->envelop,
			'Envelop object did not return the correct string.'
		);
	}

}