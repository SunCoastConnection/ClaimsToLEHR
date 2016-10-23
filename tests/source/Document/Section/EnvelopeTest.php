<?php

namespace SunCoastConnection\ClaimsToOEMR\Tests\Document\Section;

use \SunCoastConnection\ClaimsToOEMR\Tests\BaseTestCase;
use \SunCoastConnection\ClaimsToOEMR\Document\Raw;
use \SunCoastConnection\ClaimsToOEMR\Document\Section;
use \SunCoastConnection\ClaimsToOEMR\Document\Section\Envelope;

class EnvelopeTest extends BaseTestCase {

	protected $envelope;

	public function setUp() {
		parent::setUp();

		$this->envelope = $this->getMockery(
			Envelope::class
		)->makePartial();
	}

	/**
	 * @covers SunCoastConnection\ClaimsToOEMR\Document\Section\Envelope::parse()
	 */
	public function testParseWithNoHeaderSegment() {
		$raw = $this->getMockery(
			Raw::class
		);

		$this->envelope->shouldAllowMockingProtectedMethods();

		$this->envelope->shouldReceive('getSequence')
			->once()
			->with('headerSequence')
			->andReturn([]);

		$this->envelope->shouldReceive('parseSequence')
			->andReturn(false);

		$this->assertFalse(
			$this->envelope->parse($raw),
			'Parse should have returned false.'
		);

		$this->assertEquals(
			0,
			$this->envelope->getSubSectionCount(),
			'Sub-section count not set correctly.'
		);
	}

	/**
	 * @covers SunCoastConnection\ClaimsToOEMR\Document\Section\Envelope::parse()
	 */
	public function testParseWithHeaderSegment() {
		$raw = $this->getMockery(
			Raw::class
		);

		$this->envelope->shouldAllowMockingProtectedMethods();

		$this->envelope->shouldReceive('getSequence')
			->with('headerSequence')
			->andReturn([]);

		$this->envelope->shouldReceive('parseSequence')
			->andReturn(true);

		$this->envelope->shouldReceive('getSequence')
			->with('descendantSequence')
			->andReturn([]);

		$this->envelope->shouldReceive('parseSequence')
			->andReturn(true);

		$this->envelope->shouldReceive('getSequence')
			->with('trailerSequence')
			->andReturn([]);

		$this->envelope->shouldReceive('parseSequence')
			->andReturn(true);

		$this->assertTrue(
			$this->envelope->parse($raw),
			'Parse should have returned false.'
		);

		$this->assertEquals(
			0,
			$this->envelope->getSubSectionCount(),
			'Sub-section count not set correctly.'
		);
	}

	/**
	 * @covers SunCoastConnection\ClaimsToOEMR\Document\Section\Envelope::getHeader()
	 */
	public function testGetHeader() {
		$header = [
			'A',
			'B',
			'C',
		];

		$this->setProtectedProperty(
			$this->envelope,
			'subSections',
			['header' => $header]
		);

		$this->assertEquals(
			$header,
			$this->envelope->getHeader(),
			'Header value not returned.'
		);
	}

	/**
	 * @covers SunCoastConnection\ClaimsToOEMR\Document\Section\Envelope::getDescendant()
	 */
	public function testGetDescendant() {
		$descendant = [
			'A',
			'B',
			'C',
		];

		$this->setProtectedProperty(
			$this->envelope,
			'subSections',
			['descendant' => $descendant]
		);

		$this->assertEquals(
			$descendant,
			$this->envelope->getDescendant(),
			'Descendant value not returned.'
		);
	}

	/**
	 * @covers SunCoastConnection\ClaimsToOEMR\Document\Section\Envelope::getTrailer()
	 */
	public function testGetTrailer() {
		$trailer = [
			'A',
			'B',
			'C',
		];

		$this->setProtectedProperty(
			$this->envelope,
			'subSections',
			['trailer' => $trailer]
		);

		$this->assertEquals(
			$trailer,
			$this->envelope->getTrailer(),
			'Trailer value not returned.'
		);
	}

	/**
	 * @covers SunCoastConnection\ClaimsToOEMR\Document\Section\Envelope::__toString()
	 */
	public function testToString() {
		$this->setProtectedProperty(
			$this->envelope,
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
			(string) $this->envelope,
			'Envelope object did not return the correct string.'
		);
	}

}