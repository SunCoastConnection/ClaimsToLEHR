<?php

namespace SunCoastConnection\ClaimsToEMR\Tests;

use \SunCoastConnection\ClaimsToEMR\Tests\BaseTestCase;
use \SunCoastConnection\ClaimsToEMR\Document\Options;
use \SunCoastConnection\ClaimsToEMR\Document\Raw;
use \SunCoastConnection\ClaimsToEMR\X12N837;
use \SunCoastConnection\ClaimsToEMR\X12N837\Document;

class X12N837Test extends BaseTestCase {

	protected $x12n837;

	public function setUp() {
		parent::setUp();

		$this->x12n837 = $this->getMockery(
			X12N837::class
		)->makePartial();
	}

	/**
	 * @covers SunCoastConnection\ClaimsToEMR\X12N837::setOptions()
	 */
	public function testSetOptions() {
		$options = $this->getMockery(
			Options::class
		);

		$this->x12n837->setOptions($options);

		$this->assertSame(
			$options,
			$this->getProtectedProperty(
				$this->x12n837,
				'options'
			),
			'Options not set correctly'
		);
	}

	/**
	 * @covers SunCoastConnection\ClaimsToEMR\X12N837::getOptions()
	 */
	public function testGetOptions() {
		$options = $this->getMockery(
			Options::class
		);

		$this->setProtectedProperty(
			$this->x12n837,
			'options',
			$options
		);

		$this->assertSame(
			$options,
			$this->x12n837->getOptions(),
			'Options not returned correctly'
		);
	}

	/**
	 * @covers SunCoastConnection\ClaimsToEMR\X12N837::parseClaim()
	 */
	public function testParseClaim() {
		$options = $this->getMockery(
			Options::class
		);

		$raw = $this->getMockery(
			Raw::class
		);

		$claim = 'CLAIM DATA';

		$this->x12n837->shouldReceive('getOptions')
			->twice()
			->andReturn($options);

		$options->shouldReceive('instanciateAlias')
			->once()
			->with(
				'Raw',
				[
					$options
				]
			)
			->andReturn($raw);

		$raw->shouldReceive('parse')
			->once()
			->with($claim);

		$this->assertSame(
			$raw,
			$this->x12n837->parseClaim($claim),
			'Raw claim not returned'
		);
	}

	/**
	 * @covers SunCoastConnection\ClaimsToEMR\X12N837::parseClaim()
	 */
	public function testParseClaimWithFromFileTrue() {
		$options = $this->getMockery(
			Options::class
		);

		$raw = $this->getMockery(
			Raw::class
		);

		$claim = 'CLAIM DATA';

		$this->x12n837->shouldReceive('getOptions')
			->twice()
			->andReturn($options);

		$options->shouldReceive('instanciateAlias')
			->once()
			->with(
				'Raw',
				[
					$options
				]
			)
			->andReturn($raw);

		$raw->shouldReceive('parseFromFile')
			->once()
			->with($claim);

		$this->assertSame(
			$raw,
			$this->x12n837->parseClaim($claim, true),
			'Raw claim not returned'
		);
	}

	/**
	 * @covers SunCoastConnection\ClaimsToEMR\X12N837::disposeClaim()
	 */
	public function testDisposeClaim() {
		$options = $this->getMockery(
			Options::class
		);

		$document = $this->getMockery(
			Document::class
		);

		$raw = $this->getMockery(
			Raw::class
		);

		$this->x12n837->shouldReceive('getOptions')
			->twice()
			->andReturn($options);

		$options->shouldReceive('instanciateAlias')
			->once()
			->with(
				'Document',
				[
					$options
				]
			)
			->andReturn($document);

		$document->shouldReceive('parse')
			->once()
			->with($raw);

		$this->assertSame(
			$document,
			$this->x12n837->disposeClaim($raw),
			'Document not returned'
		);
	}

	/**
	 * @covers SunCoastConnection\ClaimsToEMR\X12N837::cacheClaim()
	 */
	public function testCacheClaim() {
		$options = $this->getMockery(
			Options::class
		);

		$cache = $this->getMockery(
			Cache::class
		);

		$document = $this->getMockery(
			Document::class
		);

		$store = [
			'store' => 'data'
		];

		$this->x12n837->shouldReceive('getOptions')
			->twice()
			->andReturn($options);

		$options->shouldReceive('get')
			->once()
			->with('App.store')
			->andReturn($store);

		$options->shouldReceive('instanciateAlias')
			->once()
			->with(
				'Cache',
				[
					$store
				]
			)
			->andReturn($cache);

		$cache->shouldReceive('processDocument')
			->once()
			->with($document);

		$this->assertSame(
			$cache,
			$this->x12n837->cacheClaim($document),
			'Cache not returned'
		);
	}
}