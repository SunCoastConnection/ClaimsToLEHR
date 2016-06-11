<?php

namespace SunCoastConnection\ClaimsToOEMR\Tests\Document;

use \SunCoastConnection\ClaimsToOEMR\Tests\BaseTestCase,
	\SunCoastConnection\ClaimsToOEMR\Document\Envelop,
	\SunCoastConnection\ClaimsToOEMR\Document\Raw,
	\SunCoastConnection\ClaimsToOEMR\Document\Section;

class EnvelopTest extends BaseTestCase {

	protected $envelop;

	public function setUp() {
		parent::setUp();

		$this->envelop = $this->getMockery(
			Envelop::class
		)->makePartial();
	}

	/**
	 * @covers SunCoastConnection\ClaimsToOEMR\Document\Envelop::parse()
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
			$this->getProtectedProperty(
				$this->envelop,
				'subSectionCount'
			),
			'Sub-section count not set correctly.'
		);
	}

	/**
	 * @covers SunCoastConnection\ClaimsToOEMR\Document\Envelop::parse()
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
			$this->getProtectedProperty(
				$this->envelop,
				'subSectionCount'
			),
			'Sub-section count not set correctly.'
		);
	}

	/**
	 * @covers SunCoastConnection\ClaimsToOEMR\Document\Envelop::getHeader()
	 */
	public function testGetHeader() {
		$header = [
			'A',
			'B',
			'C',
		];

		$this->setProtectedProperty(
			$this->envelop,
			'header',
			$header
		);

		$this->assertEquals(
			$header,
			$this->envelop->getHeader(),
			'Header value not returned.'
		);
	}

	/**
	 * @covers SunCoastConnection\ClaimsToOEMR\Document\Envelop::getDescendant()
	 */
	public function testGetDescendant() {
		$descendant = [
			'A',
			'B',
			'C',
		];

		$this->setProtectedProperty(
			$this->envelop,
			'descendant',
			$descendant
		);

		$this->assertEquals(
			$descendant,
			$this->envelop->getDescendant(),
			'Descendant value not returned.'
		);
	}

	/**
	 * @covers SunCoastConnection\ClaimsToOEMR\Document\Envelop::getTrailer()
	 */
	public function testGetTrailer() {
		$trailer = [
			'A',
			'B',
			'C',
		];

		$this->setProtectedProperty(
			$this->envelop,
			'trailer',
			$trailer
		);

		$this->assertEquals(
			$trailer,
			$this->envelop->getTrailer(),
			'Trailer value not returned.'
		);
	}

	/**
	 * @covers SunCoastConnection\ClaimsToOEMR\Document\Envelop::__toString()
	 */
	public function testToString() {
		$this->setProtectedProperty(
			$this->envelop,
			'header',
			[
				'A',
				'B',
			]
		);

		$this->setProtectedProperty(
			$this->envelop,
			'descendant',
			[
				'C',
				'D',
				'E',
			]
		);

		$this->setProtectedProperty(
			$this->envelop,
			'trailer',
			[
				'F',
				'G',
				'H',
				'I',
			]
		);

		$this->assertSame(
			'ABCDEFGHI',
			(string) $this->envelop,
			'Envelop object did not return the correct string.'
		);
	}

}