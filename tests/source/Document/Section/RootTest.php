<?php

namespace SunCoastConnection\ClaimsToEMR\Tests\Document\Section;

use \SunCoastConnection\ClaimsToEMR\Tests\BaseTestCase;
use \SunCoastConnection\ClaimsToEMR\Document\Raw;
use \SunCoastConnection\ClaimsToEMR\Document\Section;
use \SunCoastConnection\ClaimsToEMR\Document\Section\Root;

class RootTest extends BaseTestCase {

	protected $root;

	public function setUp() {
		parent::setUp();

		$this->root = $this->getMockery(
			Root::class
		)->makePartial();

	}

	/**
	 * @covers SunCoastConnection\ClaimsToEMR\Document\Section\Root::parse()
	 */
	public function testParseWithNoHeaderSegment() {
		$descendantSequence = [];

		$raw = $this->getMockery(
			Raw::class
		);

		$raw->shouldReceive('rewind')
			->once();

		$this->root->shouldAllowMockingProtectedMethods();

		$this->root->shouldReceive('getSequence')
			->once()
			->with('descendantSequence')
			->andReturn($descendantSequence);

		$this->root->shouldReceive('parseSequence')
			->andReturn(true);

		$this->assertTrue(
			$this->root->parse($raw),
			'Parse should have returned true.'
		);

		$this->assertEquals(
			0,
			$this->root->getSubSectionCount(),
			'Sub-section count not set correctly.'
		);
	}

	/**
	 * @covers SunCoastConnection\ClaimsToEMR\Document\Section\Root::getDescendant()
	 */
	public function testGetDescendant() {
		$descendant = [
			'A',
			'B',
			'C',
		];

		$this->setProtectedProperty(
			$this->root,
			'subSections',
			['descendant' => $descendant]
		);

		$this->assertEquals(
			$descendant,
			$this->root->getDescendant(),
			'Descendant value not returned.'
		);
	}

	/**
	 * @covers SunCoastConnection\ClaimsToEMR\Document\Section\Root::__toString()
	 */
	public function testToString() {
		$descendant = [
			'A',
			'B',
			'C',
		];

		$this->setProtectedProperty(
			$this->root,
			'subSections',
			['descendant' => $descendant]
		);

		$this->assertSame(
			'ABC',
			(string) $this->root,
			'Root object did not return the correct string.'
		);
	}

}