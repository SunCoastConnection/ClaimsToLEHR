<?php

namespace SunCoastConnection\ClaimsToOEMR\Tests;

use \SunCoastConnection\ClaimsToOEMR\Database\Cache,
	\SunCoastConnection\ClaimsToOEMR\Tests\BaseTestCase,
	\SunCoastConnection\ClaimsToOEMR\X12N837\Segment\AMT,
	\SunCoastConnection\ClaimsToOEMR\X12N837\Segment\BHT;

class CacheTest extends BaseTestCase {

	protected $cache;

	public function setUp() {
		parent::setUp();

		$this->cache = $this->getMockery(
			OpenEMR::class
		)->makePartial();
	}

	/**
	 * @covers SunCoastConnection\ClaimsToOEMR\Cache::findNextSegment()
	 */
	public function testFindNextSegment() {
		$segmentGroup = [
			'AMT' => $this->getMockery(
				AMT::class
			)->makePartial(),
			'BHT' => $this->getMockery(
				BHT::class
			)->makePartial(),
		];

		$segmentMatches = [
			get_class($segmentGroup['AMT']),
			get_class($segmentGroup['BHT']),
		];

		$this->assertInstanceOf(
			$segmentMatches[0],
			$this->callProtectedMethod(
				$this->cache,
				'findNextSegment',
				[ &$segmentGroup, $segmentMatches, true ]
			)
		);

		$this->assertInstanceOf(
			$segmentMatches[1],
			$this->callProtectedMethod(
				$this->cache,
				'findNextSegment',
				[ &$segmentGroup, $segmentMatches ]
			)
		);

		$this->assertNull(
			$this->callProtectedMethod(
				$this->cache,
				'findNextSegment',
				[ &$segmentGroup, $segmentMatches ]
			)
		);

		$this->assertInstanceOf(
			$segmentMatches[0],
			$this->callProtectedMethod(
				$this->cache,
				'findNextSegment',
				[ &$segmentGroup, $segmentMatches, true ]
			)
		);
	}

}