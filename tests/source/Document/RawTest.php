<?php

namespace SunCoastConnection\ClaimsToOEMR\Tests\Document;

use \Countable,
	\Iterator,
	\SunCoastConnection\ClaimsToOEMR\Tests\BaseTestCase,
	\SunCoastConnection\ClaimsToOEMR\Document\Options,
	\SunCoastConnection\ClaimsToOEMR\Document\Raw,
	\SunCoastConnection\ClaimsToOEMR\Document\Raw\Segment,
	\org\bovigo\vfs\vfsStream;

class RawTest extends BaseTestCase {

	protected $raw;

	protected $document = [
		'A*LM*1',
		'HL*1**20*1',
		'B*LM*2',
		'HL*2**20*1',
		'C*LM*3'
	];

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
		$options = $this->getMockery(
			Options::class
		)->makePartial();

		$raw = Raw::getNew($options);

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
		$options = $this->getMockery(
			Options::class
		)->makePartial();

		$this->raw->shouldAllowMockingProtectedMethods()
			->shouldReceive('options')
			->once()
			->with($options);

		$this->raw->__construct($options);
	}

	/**
	 * @covers SunCoastConnection\ClaimsToOEMR\Document\Raw::options()
	 */
	public function testOptions() {
		$this->assertNull(
			$this->raw->options(),
			'Options should return null when empty.'
		);

		$options = $this->getMockery(
			Options::class
		)->makePartial();

		$this->assertSame(
			$options,
			$this->raw->options($options),
			'Options should return set option object when setting value.'
		);

		$this->assertSame(
			$options,
			$this->raw->options(),
			'Options should return set option object after setting value.'
		);
	}

	/**
	 * @covers SunCoastConnection\ClaimsToOEMR\Document\Raw::parseFromFile()
	 */
	public function testParseFromFile() {
		$contents = implode('~', $this->document).'~';

		$root = vfsStream::setup();

		$file = vfsStream::newFile('claim.file')
			->at($root)
			->setContent($contents);

		$this->raw->shouldReceive('parse')
			->once()
			->with($contents, false);

		$this->raw->parseFromFile($file->url());
	}

	/**
	 * @covers SunCoastConnection\ClaimsToOEMR\Document\Raw::parseFromFile()
	 */
	public function testParseFromFileWithNonString() {
		$this->setExpectedException(
			'Exception',
			'First paramiter should be a string: NULL passed'
		);

		$this->raw->parseFromFile(null);
	}

	/**
	 * @covers SunCoastConnection\ClaimsToOEMR\Document\Raw::parseFromFile()
	 */
	public function testParseFromFileWithMissingFile() {
		$fileName = __DIR__.'/missing.txt';

		$this->setExpectedException(
			'Exception',
			'Filename provided is not readable: '.$fileName
		);

		$this->raw->parseFromFile($fileName);
	}

	/**
	 * @covers SunCoastConnection\ClaimsToOEMR\Document\Raw::parseFromFile()
	 */
	public function testParseFromFileWithSingleMode() {
		$contents = implode('~', $this->document).'~';

		$root = vfsStream::setup();

		$file = vfsStream::newFile('claim.file')
			->at($root)
			->setContent($contents);

		$this->raw->shouldReceive('parse')
			->once()
			->with($contents, true);

		$this->raw->parseFromFile($file->url(), true);
	}

	/**
	 * @covers SunCoastConnection\ClaimsToOEMR\Document\Raw::parse()
	 */
	public function testParse() {
		$contents = implode('~', $this->document).'~';

		$options = $this->getMockery(
			Options::class
		)->makePartial();

		$this->raw->shouldAllowMockingProtectedMethods()
			->shouldReceive('options')
			->andReturn($options);

		$options->set('Document.autodetect', false);

		$options->set('Document.delimiters.segment', '~');

		$this->raw->shouldReceive('parseSegments');

		$this->raw->shouldReceive('rewind');

		$this->raw->parse($contents);

		$this->assertAttributeEquals(
			$this->document,
			'segments',
			$this->raw,
			'Explosion of raw data failed.'
		);
	}

	/**
	 * @covers SunCoastConnection\ClaimsToOEMR\Document\Raw::parse()
	 */
	public function testParseWithNonString() {
		$this->setExpectedException(
			'Exception',
			'First paramiter should be a string: NULL passed'
		);

		$this->raw->parse(null);
	}

	/**
	 * @covers SunCoastConnection\ClaimsToOEMR\Document\Raw::parse()
	 */
	public function testParseWithAutodetect() {
		$contents = implode('~', $this->document).'~';

		$options = $this->getMockery(
			Options::class
		)->makePartial();

		$this->raw->shouldAllowMockingProtectedMethods()
			->shouldReceive('options')
			->andReturn($options);

		$options->set('Document.autodetect', true);

		$this->raw->shouldReceive('setInterchangeData')
			->with($contents);

		$options->set('Document.delimiters.segment', '~');

		$this->raw->shouldReceive('parseSegments');
		$this->raw->shouldReceive('rewind');

		$this->raw->parse($contents);
	}

	/**
	 * @covers SunCoastConnection\ClaimsToOEMR\Document\Raw::parse()
	 */
	public function testParseWithSingleMode() {
		$contents = implode('~', $this->document).'~';

		$options = $this->getMockery(
			Options::class
		)->makePartial();

		$this->raw->shouldAllowMockingProtectedMethods()
			->shouldReceive('options')
			->andReturn($options);

		$options->set('Document.autodetect', false);

		$this->raw->shouldReceive('correctSingleMode')
			->with($contents);

		$options->set('Document.delimiters.segment', '~');

		$this->raw->shouldReceive('parseSegments');

		$this->raw->shouldReceive('rewind');

		$this->raw->parse($contents, true);
	}

	/**
	 * @covers SunCoastConnection\ClaimsToOEMR\Document\Raw::setInterchangeData()
	 */
	public function testSetInterchangeData() {
		$options = $this->getMockery(
			Options::class
		)->makePartial();

		$this->raw->shouldAllowMockingProtectedMethods()
			->shouldReceive('options')
			->andReturn($options);

		$this->callProtectedMethod(
			$this->raw,
			'setInterchangeData',
			[
				'ISA!00!          !00!          !ZZ!15G8           !ZZ!43142076400000 !150306!1617!@!00501!000638905!1!P!#$'
			]
		);

		$this->assertEquals(
			[
				'data'			=> '!',
				'repetition'	=> '@',
				'component'		=> '#',
				'segment'		=> '$',
			]
			,
			$options->get('Document.delimiters'),
			'Setting Interchange data failed'
		);
	}

	/**
	 * @covers SunCoastConnection\ClaimsToOEMR\Document\Raw::setInterchangeData()
	 */
	public function testSetInterchangeDataWithBadSegment() {
		$contents = implode('~', $this->document).'~';

		$options = $this->getMockery(
			Options::class
		)->makePartial();

		$options->set('Document.autodetect', true);

		$this->raw->shouldAllowMockingProtectedMethods()
			->shouldReceive('options')
			->andReturn($options);

		$this->setExpectedException(
			'Exception',
			'ISA segment not provided as first segment'
		);

		$this->callProtectedMethod(
			$this->raw,
			'setInterchangeData',
			[
				$contents
			]
		);
	}

	/**
	 * @covers SunCoastConnection\ClaimsToOEMR\Document\Raw::correctSingleMode()
	 */
	public function testCorrectSingleMode() {
		$contents = implode('~', $this->document).'~';

		$options = $this->getMockery(
			Options::class
		)->makePartial();

		$this->raw->shouldAllowMockingProtectedMethods()
			->shouldReceive('options')
			->andReturn($options);

		$options->set(
			'Document.delimiters',
			[
				'data'			=> '*',
				'repetition'	=> '^',
				'component'		=> ':',
				'segment'		=> '~',
			]
		);

		$this->assertEquals(
			implode(
				'~',
				[
					$this->document[0],
					$this->document[1],
					$this->document[2],
					'HL*2',
					'SE*28*0003',
					'ST*837*0004*005010X222A1',
					$this->document[4],
				]
			).'~',
			$this->callProtectedMethod(
				$this->raw,
				'correctSingleMode',
				[
					$contents
				]
			),
			'Explosion of raw data failed'
		);
	}

	/**
	 * @covers SunCoastConnection\ClaimsToOEMR\Document\Raw::parseSegments()
	 */
	public function testParseSegments() {
		$segmentString = 'AB*C*1*D*2';
		$output = 'Returned Segment Object';

		$options = $this->getMockery(
			Options::class
		)->makePartial();

		$this->raw->shouldAllowMockingProtectedMethods()
			->shouldReceive('options')
			->andReturn($options);

		$segment = $this->getMockery(
			'alias:'.Segment::class
		)->makePartial();

		$segment->shouldReceive('getNew')
			->with($options, $segmentString)
			->andReturn($output);

		$this->setProtectedProperty(
			$this->raw,
			'segments',
			[
				$segmentString,
			]
		);

		$this->callProtectedMethod(
			$this->raw,
			'parseSegments'
		);

		$this->assertEquals(
			[
				$output
			],
			$this->getProtectedProperty(
				$this->raw,
				'segments'
			),
			'Segment was not parsed correctly'
		);
	}

	/**
	 * @covers SunCoastConnection\ClaimsToOEMR\Document\Raw::__tostring()
	 */
	public function testToString() {
		$options = $this->getMockery(
			Options::class
		)->makePartial();

		$options->set('Document.delimiters.segment', '~');

		$this->raw->shouldAllowMockingProtectedMethods()
			->shouldReceive('options')
			->andReturn($options);

		$this->setProtectedProperty(
			$this->raw,
			'segments',
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
			'segments',
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
			'segments',
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
			'segments',
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
			'segments',
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
			'segments',
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
			'segments',
			$array
		);

		$this->assertEquals(
			'C',
			$this->raw->current(),
			'Failed to return incremented array value.'
		);

		next($array);

		$this->setProtectedProperty(
			$this->raw,
			'segments',
			$array
		);

		$this->assertFalse(
			$this->raw->current(),
			'Failed to return end of array value.'
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
			'segments',
			$array
		);

		$this->raw->next();
		$this->raw->next();

		$array = $this->getProtectedProperty(
			$this->raw,
			'segments'
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
			'segments',
			$array
		);

		$this->raw->rewind();

		$array = $this->getProtectedProperty(
			$this->raw,
			'segments'
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
			'segments',
			$array
		);

		$this->assertEquals(
			3,
			$this->raw->count(),
			'Failed to correct array count.'
		);
	}

}