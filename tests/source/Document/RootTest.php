<?php

namespace SunCoastConnection\ClaimsToOEMR\Tests\Document;

use \SunCoastConnection\ClaimsToOEMR\Tests\BaseTestCase,
	\SunCoastConnection\ClaimsToOEMR\Document\Raw,
	\SunCoastConnection\ClaimsToOEMR\Document\Root,
	\SunCoastConnection\ClaimsToOEMR\Document\Section;

class RootTest extends BaseTestCase {

	protected $root;

	public function setUp() {
		parent::setUp();

		$this->root = $this->getMockery(
			Root::class
		)->makePartial();

	}

	/**
	 * @covers SunCoastConnection\ClaimsToOEMR\Document\Root::parse()
	 */
	public function testParseWithNoHeaderSegment() {
		$descendantSequence = [];

		$raw = $this->getMockery(
			Raw::class
		)->makePartial();

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
			$this->getProtectedProperty(
				$this->root,
				'subSectionCount'
			),
			'Sub-section count not set correctly.'
		);
	}

	/**
	 * @covers SunCoastConnection\ClaimsToOEMR\Document\Root::__toString()
	 */
	public function testToString() {
		$this->setProtectedProperty(
			$this->root,
			'descendant',
			[
				'A',
				'B',
				'C',
			]
		);

		$this->assertSame(
			'ABC',
			(string) $this->root,
			'Root object did not return the correct string.'
		);
	}

}