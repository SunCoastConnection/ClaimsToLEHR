<?php

namespace SunCoastConnection\ClaimsToEMR\Tests\Document\Raw;

use \SunCoastConnection\ClaimsToEMR\Tests\BaseTestCase;
use \SunCoastConnection\ClaimsToEMR\Document\Options;
use \SunCoastConnection\ClaimsToEMR\Document\Raw\Element;

class ElementTest extends BaseTestCase {

	protected $element;

	public function setUp() {
		parent::setUp();

		$this->element = $this->getMockery(
			Element::class
		)->makePartial();
	}

	/**
	 * @covers SunCoastConnection\ClaimsToEMR\Document\Raw\Element::getInstance()
	 */
	public function testGetInstance() {
		$options = $this->getMockery(
			Options::class
		);

		$options->shouldReceive('get')
			->with('Document.delimiters.component')
			->andReturn('*');

		$element = Element::getInstance($options, 'A*B');

		$this->assertInstanceOf(
			Element::class,
			$element,
			'Expected new instance of '.Element::class.'.'
		);
	}

	/**
	 * @covers SunCoastConnection\ClaimsToEMR\Document\Raw\Element::__construct()
	 */
	public function testConstruct() {
		$options = $this->getMockery(
			Options::class
		);

		$this->element->shouldAllowMockingProtectedMethods()
			->shouldReceive('options')
			->once()
			->with($options);

		$this->element->__construct($options);
	}

	/**
	 * @covers SunCoastConnection\ClaimsToEMR\Document\Raw\Element::options()
	 */
	public function testOptions() {
		$this->assertNull(
			$this->element->options(),
			'Options should return null when empty.'
		);

		$options = $this->getMockery(
			Options::class
		);

		$this->assertSame(
			$options,
			$this->element->options($options),
			'Options should return set option object when setting value.'
		);

		$this->assertSame(
			$options,
			$this->element->options(),
			'Options should return set option object after setting value.'
		);
	}

	/**
	 * @covers SunCoastConnection\ClaimsToEMR\Document\Raw\Element::parse()
	 */
	public function testParseWithSubElements() {
		$options = $this->getMockery(
			Options::class
		);

		$options->shouldReceive('get')
			->with('Document.delimiters.component')
			->andReturn(':');

		$this->element->shouldAllowMockingProtectedMethods()
			->shouldReceive('options')
			->andReturn($options);

		$this->element->parse('B:C');

		$this->getProtectedProperty(
			$this->element,
			'subElements',
			[ 'B', 'C' ]
		);
	}

	/**
	 * @covers SunCoastConnection\ClaimsToEMR\Document\Raw\Element::parse()
	 */
	public function testParseWithNoSubElements() {
		$options = $this->getMockery(
			Options::class
		);

		$options->shouldReceive('get')
			->with('Document.delimiters.component')
			->andReturn(':');

		$this->element->shouldAllowMockingProtectedMethods()
			->shouldReceive('options')
			->andReturn($options);

		$this->element->parse('A');

		$this->getProtectedProperty(
			$this->element,
			'subElements',
			[ 'A' ]
		);
	}

	/**
	 * @covers SunCoastConnection\ClaimsToEMR\Document\Raw\Element::subElementExists()
	 */
	public function testSubElementExistsWithMissingSubElement() {
		$this->assertFalse(
			$this->element->subElementExists(0),
			'Sub-element should not have been found.'
		);
	}

	/**
	 * @covers SunCoastConnection\ClaimsToEMR\Document\Raw\Element::subElementExists()
	 */
	public function testSubElementExistsWithExistingSubElement() {
		$this->setProtectedProperty(
			$this->element,
			'subElements',
			[ 'B', 'C' ]
		);

		$this->assertTrue(
			$this->element->subElementExists(0),
			'Sub-element should have been found.'
		);
	}

	/**
	 * @covers SunCoastConnection\ClaimsToEMR\Document\Raw\Element::subElement()
	 */
	public function testSubElementWithMissingSubElement() {
		$this->assertNull(
			$this->element->subElement(0),
			'Sub-element should not have been found.'
		);
	}

	/**
	 * @covers SunCoastConnection\ClaimsToEMR\Document\Raw\Element::subElement()
	 */
	public function testSubElementWithExistingSubElement() {
		$this->setProtectedProperty(
			$this->element,
			'subElements',
			[ 'B', 'C' ]
		);

		$this->assertSame(
			'B',
			$this->element->subElement(0),
			'Sub-element should have been found.'
		);
	}

	/**
	 * @covers SunCoastConnection\ClaimsToEMR\Document\Raw\Element::subElementEquals()
	 */
	public function testSubElementEqualsWithMissingSubElement() {
		$this->assertFalse(
			$this->element->subElementEquals(0, 'true'),
			'Sub-element should not have been found.'
		);
	}

	/**
	 * @covers SunCoastConnection\ClaimsToEMR\Document\Raw\Element::subElementEquals()
	 */
	public function testSubElementEqualsWithWrongValue() {
		$this->setProtectedProperty(
			$this->element,
			'subElements',
			[ 'B', 'C' ]
		);

		$this->assertFalse(
			$this->element->subElementEquals(0, 'false'),
			'Sub-element value should not have matched.'
		);
	}

	/**
	 * @covers SunCoastConnection\ClaimsToEMR\Document\Raw\Element::subElementEquals()
	 */
	public function testSubElementEqualsWithCorrectValue() {
		$this->setProtectedProperty(
			$this->element,
			'subElements',
			[ 'B', 'C' ]
		);

		$this->assertTrue(
			$this->element->subElementEquals(0, 'B'),
			'Sub-element value should have matched.'
		);
	}

	/**
	 * @covers SunCoastConnection\ClaimsToEMR\Document\Raw\Element::subElementCount()
	 */
	public function testSubElementCountWithNoSubElements() {
		$this->setProtectedProperty(
			$this->element,
			'subElements',
			[ 'A' ]
		);

		$this->assertEquals(
			1,
			$this->element->subElementCount(),
			'Sub-element count should have been 1.'
		);
	}

	/**
	 * @covers SunCoastConnection\ClaimsToEMR\Document\Raw\Element::subElementCount()
	 */
	public function testSubElementCountWithSubElements() {
		$this->setProtectedProperty(
			$this->element,
			'subElements',
			[ 'B', 'C' ]
		);

		$this->assertEquals(
			2,
			$this->element->subElementCount(),
			'Sub-element count should have been 2.'
		);
	}

	/**
	 * @covers SunCoastConnection\ClaimsToEMR\Document\Raw\Element::__toString()
	 */
	public function testToStringWithNoSubElements() {
		$options = $this->getMockery(
			Options::class
		);

		$options->shouldReceive('get')
			->with('Document.delimiters.component')
			->andReturn(':');

		$this->element->shouldAllowMockingProtectedMethods()
			->shouldReceive('options')
			->andReturn($options);

		$this->setProtectedProperty(
			$this->element,
			'subElements',
			[ 'A' ]
		);

		$this->assertEquals(
			'A',
			(string) $this->element,
			'Element object did not return the correct string.'
		);
	}

	/**
	 * @covers SunCoastConnection\ClaimsToEMR\Document\Raw\Element::__toString()
	 */
	public function testToStringWithSubElements() {
		$options = $this->getMockery(
			Options::class
		);

		$options->shouldReceive('get')
			->with('Document.delimiters.component')
			->andReturn(':');

		$this->element->shouldAllowMockingProtectedMethods()
			->shouldReceive('options')
			->andReturn($options);

		$this->setProtectedProperty(
			$this->element,
			'subElements',
			[ 'B', 'C' ]
		);

		$this->assertEquals(
			'B:C',
			(string) $this->element,
			'Element object did not return the correct string.'
		);
	}

}