<?php

namespace SunCoastConnection\ClaimsToOEMR\Tests\Document\Raw;

use \Exception;
use \SunCoastConnection\ClaimsToOEMR\Tests\BaseTestCase;
use \SunCoastConnection\ClaimsToOEMR\Document\Options;
use \SunCoastConnection\ClaimsToOEMR\Document\Raw;
use \SunCoastConnection\ClaimsToOEMR\Document\Raw\Element;
use \SunCoastConnection\ClaimsToOEMR\Document\Raw\Segment;

class SegmentTest extends BaseTestCase {

	protected $segment;

	public function setUp() {
		parent::setUp();

		$this->segment = $this->getMockery(
			Segment::class
		)->makePartial();
	}

	/**
	 * @covers SunCoastConnection\ClaimsToOEMR\Document\Raw\Segment::getInstance()
	 */
	public function testGetInstance() {
		$elements = [
			'A',
			'B:1',
			'C:2',
			'D',
		];

		$options = $this->getMockery(
			Options::class
		);

		$options->shouldReceive('get')
			->with('Document.delimiters.data')
			->andReturn('*');

		$segmentA = $this->getMockery(
			Segment::class
		);

		$options->shouldReceive('instanciateAlias')
			->with($elements[0], [ $options ])
			->andReturn($segmentA);

		$justElements = $elements;
		array_shift($justElements);

		$segmentA->shouldReceive('parse')
			->once()
			->with(implode('*', $justElements));

		$segment = $this->segment::getInstance($options, implode('*', $elements));

		$this->assertSame(
			$segmentA,
			$segment,
			'Segment not returned'
		);
	}

	/**
	 * @covers SunCoastConnection\ClaimsToOEMR\Document\Raw\Segment::getInstance()
	 */
	public function testGetInstanceWithMissingElements() {
		$elements = [
			'E',
		];

		$options = $this->getMockery(
			Options::class
		);

		$options->shouldReceive('get')
			->with('Document.delimiters.data')
			->andReturn('*');

		$segmentE = $this->getMockery(
			Segment::class
		);

		$options->shouldReceive('instanciateAlias')
			->with($elements[0], [ $options ])
			->andReturn($segmentE);

		$segmentE->shouldReceive('parse')
			->once()
			->with('');

		$segment = $this->segment::getInstance($options, implode('*', $elements));

		$this->assertSame(
			$segmentE,
			$segment,
			'Segment not returned'
		);
	}

	/**
	 * @covers SunCoastConnection\ClaimsToOEMR\Document\Raw\Segment::getInstance()
	 */
	public function testGetInstanceWithInvalidDesignator() {
		$elements = [
			'A',
			'B:1',
			'C:2',
			'D',
		];

		$options = $this->getMockery(
			Options::class
		);

		$options->shouldReceive('get')
			->with('Document.delimiters.data')
			->andReturn('*');

		$options->shouldReceive('instanciateAlias')
			->with($elements[0], [ $options ])
			->andThrow(new Exception);

		$this->setExpectedException(
			'Exception',
			'Segment designator can not be found: '.$elements[0]
		);

		$segment = $this->segment::getInstance($options, implode('*', $elements));
	}

	/**
	 * @covers SunCoastConnection\ClaimsToOEMR\Document\Raw\Segment::getElementSequence()
	 */
	public function testGetElementSequence() {
		$elementSequence = [
			['name' => 'ABC01', 'required' => true],
			['name' => 'ABC02', 'required' => true],
			['name' => 'ABC03', 'required' => false],
		];

		$this->setProtectedProperty(
			$this->segment,
			'elementSequence',
			$elementSequence
		);

		$this->assertEquals(
			$elementSequence,
			$this->segment->getElementSequence(),
			'Array of element sequence was expected'
		);
	}

	/**
	 * @covers SunCoastConnection\ClaimsToOEMR\Document\Raw\Segment::getElementNames()
	 */
	public function testGetElementNames() {
		$elementNames = [
			'ABC01' => 'First element name',
			'ABC02' => 'Second element name',
			'ABC03' => 'Third element name',
			'ABC04' => 'Fourth element name',
			'ABC05' => 'Fifth element name',
		];

		$this->setProtectedProperty(
			$this->segment,
			'elementNames',
			$elementNames
		);

		$this->assertEquals(
			$elementNames,
			$this->segment->getElementNames(),
			'Array of element names was expected'
		);
	}

	/**
	 * @covers SunCoastConnection\ClaimsToOEMR\Document\Raw\Segment::__construct()
	 */
	public function testConstruct() {
		$options = $this->getMockery(
			Options::class
		);

		$options->shouldReceive('get')
			->with('Document.delimiters.data')
			->andReturn('*');

		$this->segment->shouldAllowMockingProtectedMethods()
			->shouldReceive('options')
			->once()
			->with($options);

		$this->segment->__construct($options);
	}

	/**
	 * @covers SunCoastConnection\ClaimsToOEMR\Document\Raw\Segment::options()
	 */
	public function testOptions() {
		$this->assertNull(
			$this->callProtectedMethod(
				$this->segment,
				'options'
			),
			'Options should return null when empty.'
		);

		$options = $this->getMockery(
			Options::class
		);

		$this->assertSame(
			$options,
			$this->callProtectedMethod(
				$this->segment,
				'options',
				[ $options ]
			),
			'Options should return set option object when setting value.'
		);

		$this->assertSame(
			$options,
			$this->callProtectedMethod(
				$this->segment,
				'options'
			),
			'Options should return set option object after setting value.'
		);
	}

	/**
	 * @covers SunCoastConnection\ClaimsToOEMR\Document\Raw\Segment::setParentName()
	 */
	public function testSetParentName() {
		$parentName = '/ROOT';

		$this->segment->setParentName($parentName);

		$this->assertEquals(
			$parentName,
			$this->getProtectedProperty(
				$this->segment,
				'parentName'
			),
			'Parent name not set correctly.'
		);
	}

	/**
	 * @covers SunCoastConnection\ClaimsToOEMR\Document\Raw\Segment::getName()
	 */
	public function testGetName() {
		$this->assertEquals(
			get_class($this->segment),
			$this->segment->getName(),
			'Name not returned correctly'
		);
	}

	/**
	 * @covers SunCoastConnection\ClaimsToOEMR\Document\Raw\Segment::getName()
	 */
	public function testGetNameWithFull() {
		$this->setProtectedProperty(
			$this->segment,
			'parentName',
			'/ROOT'
		);

		$this->assertEquals(
			'/ROOT/'.get_class($this->segment),
			$this->segment->getName(true),
			'Full name not returned correctly'
		);
	}

	/**
	 * @covers SunCoastConnection\ClaimsToOEMR\Document\Raw\Segment::getName()
	 */
	public function testGetNameWithFullAndNoNameParrent() {
		$this->setProtectedProperty(
			$this->segment,
			'parentName',
			'/'
		);

		$this->assertEquals(
			'/'.get_class($this->segment),
			$this->segment->getName(true),
			'Full name not returned correctly'
		);
	}

	/**
	 * @covers SunCoastConnection\ClaimsToOEMR\Document\Raw\Segment::Parse()
	 */
	public function testParse() {
		$elements = [
			'A',
			'B:C',
			'D',
		];

		$options = $this->getMockery(
			Options::class
		);

		$options->shouldReceive('get')
			->with('Document.delimiters.data')
			->andReturn('*');
		$options->shouldReceive('get')
			->with('Document.delimiters.component')
			->andReturn(':');

		$this->segment->shouldAllowMockingProtectedMethods()
			->shouldReceive('options')
			->once()
			->andReturn($options);

		$this->setProtectedProperty(
			$this->segment,
			'elementSequence',
			[
				[ 'name' => 'Element 1' ],
				[ 'name' => 'Element 2' ],
			]
		);

		$this->segment->parse(implode('*', $elements));

		$this->assertEquals(
			[
				'Element 1' => Element::getInstance($options, $elements[0]),
				'Element 2' => Element::getInstance($options, $elements[1]),
				2 => Element::getInstance($options, $elements[2]),
			],
			$this->getProtectedProperty(
				$this->segment,
				'elements'
			),
			'Elements not stored correctly'
		);
	}

	/**
	 * @covers SunCoastConnection\ClaimsToOEMR\Document\Raw\Segment::Parse()
	 */
	public function testParseWithNoElements() {
		$this->segment->parse('');

		$this->assertEquals(
			[
			],
			$this->getProtectedProperty(
				$this->segment,
				'elements'
			),
			'Elements should not have been stored'
		);
	}

	/**
	 * @covers SunCoastConnection\ClaimsToOEMR\Document\Raw\Segment::elementExists()
	 */
	public function testElementExistsWithMissingElement() {
		$this->assertFalse(
			$this->segment->elementExists('AAA'),
			'Element should not have been found'
		);
	}

	/**
	 * @covers SunCoastConnection\ClaimsToOEMR\Document\Raw\Segment::elementExists()
	 */
	public function testElementExistsWithExistingElement() {
		$this->setProtectedProperty(
			$this->segment,
			'elements',
			[ 'AAA' => 'true' ]
		);

		$this->assertTrue(
			$this->segment->elementExists('AAA'),
			'Element should have been found'
		);
	}

	/**
	 * @covers SunCoastConnection\ClaimsToOEMR\Document\Raw\Segment::element()
	 */
	public function testElementWithMissingElement() {
		$this->assertNull(
			$this->segment->element('AAA'),
			'Element should not have been found'
		);
	}

	/**
	 * @covers SunCoastConnection\ClaimsToOEMR\Document\Raw\Segment::element()
	 */
	public function testElementWithExistingElement() {
		$this->setProtectedProperty(
			$this->segment,
			'elements',
			[ 'AAA' => 'true' ]
		);

		$this->assertSame(
			'true',
			$this->segment->element('AAA'),
			'Element should have been found'
		);
	}

	/**
	 * @covers SunCoastConnection\ClaimsToOEMR\Document\Raw\Segment::elementEquals()
	 */
	public function testElementEqualsWithMissingElement() {
		$this->assertFalse(
			$this->segment->elementEquals('AAA', 'true'),
			'Element should not have been found'
		);
	}

	/**
	 * @covers SunCoastConnection\ClaimsToOEMR\Document\Raw\Segment::elementEquals()
	 */
	public function testElementEqualsWithWrongValue() {
		$this->setProtectedProperty(
			$this->segment,
			'elements',
			[ 'AAA' => 'true' ]
		);

		$this->assertFalse(
			$this->segment->elementEquals('AAA', 'false'),
			'Element value should not have matched'
		);
	}

	/**
	 * @covers SunCoastConnection\ClaimsToOEMR\Document\Raw\Segment::elementEquals()
	 */
	public function testElementEqualsWithCorrectValue() {
		$this->setProtectedProperty(
			$this->segment,
			'elements',
			[ 'AAA' => 'true' ]
		);

		$this->assertTrue(
			$this->segment->elementEquals('AAA', 'true'),
			'Element value should have matched'
		);
	}

	/**
	 * @covers SunCoastConnection\ClaimsToOEMR\Document\Raw\Segment::__toString()
	 */
	public function testToString() {
		$options = $this->getMockery(
			Options::class
		);

		$options->shouldReceive('get')
			->with('Document.delimiters.data')
			->andReturn('*');
		$options->shouldReceive('get')
			->with('Document.delimiters.component')
			->andReturn(':');

		$this->segment->shouldAllowMockingProtectedMethods()
			->shouldReceive('options')
			->andReturn($options);

		$this->setProtectedProperty(
			$this->segment,
			'elements',
			[
				'AB01' => Element::getInstance($options, 'C'),
				'AB02' => Element::getInstance($options, 'D'),
				'AB03' => Element::getInstance($options, 'E'),
				'AB04' => Element::getInstance($options, ':'),
				'AB05' => Element::getInstance($options, '123'),
			]
		);

		$this->segment->shouldReceive('getName')
			->once()
			->andReturn('AB');

		$this->assertEquals(
			'AB*C*D*E*:*123',
			(string) $this->segment,
			'Segment object did not return the correct string'
		);
	}

}