<?php

namespace SunCoastConnection\ClaimsToOEMR\Tests\Document;

use \Countable,
	\Iterator,
	\SunCoastConnection\ClaimsToOEMR\Tests\BaseTestCase,
	\SunCoastConnection\ClaimsToOEMR\Document\Raw;

class RawTest extends BaseTestCase {

	protected $raw;

	public function setUp() {
		parent::setUp();

		$this->raw = $this->getMockery(
			Raw::class
		)->makePartial();
	}

	/**
	 * @covers SunCoastConnection\ClaimsToOEMR\Document\Raw::getNew()
	 */
	public function testGetNew() {
		$raw = Raw::getNew('');

		$this->assertInstanceOf(
			Raw::class,
			$raw,
			'Expected new instance of '.Raw::class.'.'
		);

		$this->assertInstanceOf(
			Iterator::class,
			$raw,
			'Expected instance to implement '.Iterator::class.'.'
		);

		$this->assertInstanceOf(
			Countable::class,
			$raw,
			'Expected instance to implement '.Countable::class.'.'
		);
	}

	/**
	 * @covers SunCoastConnection\ClaimsToOEMR\Document\Raw::__construct()
	 */
	public function testConstruct() {
		$this->raw->__construct('A~HL*1**20*1~B~HL*2**20*1~C~');

		$this->assertAttributeEquals(
			[
				'A',
				'HL*1**20*1',
				'B',
				'HL*2**20*1',
				'C'
			],
			'chunks',
			$this->raw,
			'Explosion of raw data failed.'
		);
	}

	/**
	 * @covers SunCoastConnection\ClaimsToOEMR\Document\Raw::__construct()
	 */
	public function testConstructWithNonString() {
		$this->setExpectedException(
			'Exception',
			'First paramiter should be a string: NULL passed.'
		);

		$this->raw->__construct(null);
	}

	/**
	 * @covers SunCoastConnection\ClaimsToOEMR\Document\Raw::__construct()
	 */
	public function testConstructWithSingleMode() {
		$this->raw->__construct('A~HL*1**20*1~B~HL*2**20*1~C~', true);

		$this->assertAttributeEquals(
			[
				'A',
				'HL*1**20*1',
				'B',
				'HL*2',
				'SE*28*0003',
				'ST*837*0004*005010X222A1',
				'C'
			],
			'chunks',
			$this->raw,
			'Explosion of raw data failed.'
		);
	}

	/**
	 * @covers SunCoastConnection\ClaimsToOEMR\Document\Raw::__tostring()
	 */
	public function testToString() {
		$this->setProtectedProperty(
			$this->raw,
			'chunks',
			[
				'A',
				'B',
				'C',
			]
		);

		$this->assertEquals(
			'A~B~C~',
			(string) $this->raw,
			'Implosion of segmented data failed.'
		);
	}

	/**
	 * @covers SunCoastConnection\ClaimsToOEMR\Document\Raw::key()
	 */
	public function testKey() {
		$array = [
			'A',
			'B',
			'C',
		];

		$this->setProtectedProperty(
			$this->raw,
			'chunks',
			$array
		);

		$this->assertEquals(
			0,
			$this->raw->key(),
			'Array position not at start of array.'
		);

		next($array);
		next($array);

		$this->setProtectedProperty(
			$this->raw,
			'chunks',
			$array
		);

		$this->assertEquals(
			2,
			$this->raw->key(),
			'Array position not advanced from start of array.'
		);
	}

	/**
	 * @covers SunCoastConnection\ClaimsToOEMR\Document\Raw::valid()
	 */
	public function testValidWithEmptyArray() {
		$this->assertEquals(
			false,
			$this->raw->valid(),
			'Failed to detect empty array.'
		);
	}

	/**
	 * @covers SunCoastConnection\ClaimsToOEMR\Document\Raw::valid()
	 */
	public function testValid() {
		$array = [
			'A',
			'B',
			'C',
		];

		$this->setProtectedProperty(
			$this->raw,
			'chunks',
			$array
		);

		$this->assertEquals(
			true,
			$this->raw->valid(),
			'Failed to detect valid key/value set.'
		);

		next($array);
		next($array);
		next($array);

		$this->setProtectedProperty(
			$this->raw,
			'chunks',
			$array
		);

		$this->assertEquals(
			false,
			$this->raw->valid(),
			'Failed to detect missing key/value set.'
		);
	}

	/**
	 * @covers SunCoastConnection\ClaimsToOEMR\Document\Raw::current()
	 */
	public function testCurrent() {
		$array = [
			'A',
			'B',
			'C',
		];

		$this->setProtectedProperty(
			$this->raw,
			'chunks',
			$array
		);

		$this->assertEquals(
			'A',
			$this->raw->current(),
			'Failed to return initial array value.'
		);

		next($array);
		next($array);

		$this->setProtectedProperty(
			$this->raw,
			'chunks',
			$array
		);

		$this->assertEquals(
			'C',
			$this->raw->current(),
			'Failed to return incremented array value.'
		);
	}

	/**
	 * @covers SunCoastConnection\ClaimsToOEMR\Document\Raw::next()
	 */
	public function testNext() {
		$array = [
			'A',
			'B',
			'C',
		];

		$this->setProtectedProperty(
			$this->raw,
			'chunks',
			$array
		);

		$this->raw->next();
		$this->raw->next();

		$array = $this->getProtectedProperty(
			$this->raw,
			'chunks'
		);

		$this->assertEquals(
			'C',
			current($array),
			'Failed to increment array pointer.'
		);
	}

	/**
	 * @covers SunCoastConnection\ClaimsToOEMR\Document\Raw::rewind()
	 */
	public function testRewind() {
		$array = [
			'A',
			'B',
			'C',
		];

		next($array);
		next($array);
		next($array);

		$this->setProtectedProperty(
			$this->raw,
			'chunks',
			$array
		);

		$this->raw->rewind();

		$array = $this->getProtectedProperty(
			$this->raw,
			'chunks'
		);

		$this->assertEquals(
			'A',
			current($array),
			'Failed to reset array pointer.'
		);
	}

	/**
	 * @covers SunCoastConnection\ClaimsToOEMR\Document\Raw::count()
	 */
	public function testCount() {
		$array = [
			'A',
			'B',
			'C',
		];

		$this->setProtectedProperty(
			$this->raw,
			'chunks',
			$array
		);

		$this->assertEquals(
			3,
			$this->raw->count(),
			'Failed to correct array count.'
		);
	}

	/**
	 * @covers SunCoastConnection\ClaimsToOEMR\Document\Raw::getSegments()
	 */
	public function testGetSegments() {
		$array = [
			'ST*837*0001'
		];

		$this->setProtectedProperty(
			$this->raw,
			'chunks',
			$array
		);

		$this->assertEquals(
			[
				'ST',
				'837',
				'0001'
			],
			$this->raw->getSegments(),
			'Segments not returned correctly.'
		);
	}

	/**
	 * @covers SunCoastConnection\ClaimsToOEMR\Document\Raw::getSegmentDesignator()
	 */
	public function testGetSegmentDesignator() {
		$array = [
			'ST*837*0001'
		];

		$this->setProtectedProperty(
			$this->raw,
			'chunks',
			$array
		);

		$this->assertEquals(
			'ST',
			$this->raw->getSegmentDesignator(),
			'Segment designator not returned correctly.'
		);
	}

	/**
	 * @covers SunCoastConnection\ClaimsToOEMR\Document\Raw::getSegmentElements()
	 */
	public function testGetSegmentElements() {
		$array = [
			'ST*837*0001'
		];

		$this->setProtectedProperty(
			$this->raw,
			'chunks',
			$array
		);

		$this->assertEquals(
			[
				'837',
				'0001'
			],
			$this->raw->getSegmentElements(),
			'Segment elements not returned correctly.'
		);
	}

}