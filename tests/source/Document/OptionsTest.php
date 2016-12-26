<?php

namespace SunCoastConnection\ClaimsToOEMR\Tests\Document;

use \Exception;
use \Illuminate\Config\Repository;
use \SunCoastConnection\ClaimsToOEMR\Tests\BaseTestCase;
use \SunCoastConnection\ClaimsToOEMR\Document\Options;

class OptionsTest extends BaseTestCase {

	protected $options;

	public function setUp() {
		parent::setUp();

		$this->options = $this->getMockery(
			Options::class
		)->makePartial();
	}

	/**
	 * @covers SunCoastConnection\ClaimsToOEMR\Document\Options::getInstance()
	 */
	public function testGetInstance() {
		$options = Options::getInstance([]);

		$this->assertInstanceOf(
			Options::class,
			$options,
			'Expected new instance of '.Options::class.'.'
		);

		$this->assertInstanceOf(
			Repository::class,
			$options,
			'Expected instance to extend '.Repository::class.'.'
		);
	}

	/**
	 * @covers SunCoastConnection\ClaimsToOEMR\Document\Options::resolveAlias()
	 */
	public function testResolveAlias() {
		$alias = 'TestAlias';
		$class = '/Namespace/To/TestClass';

		$this->options->shouldReceive('get')
			->once()
			->with('Aliases.'.$alias)
			->andReturn($class);

		$this->assertSame(
			$class,
			$this->options->resolveAlias($alias),
			'Alias did not return correct class string.'
		);
	}

	/**
	 * @covers SunCoastConnection\ClaimsToOEMR\Document\Options::instanciateAlias()
	 */
	public function testInstanciateAliasWithMissingAlias() {
		$alias = 'TestAlias';
		$class = null;

		$this->options->shouldReceive('resolveAlias')
			->once()
			->with($alias)
			->andReturn($class);

		$this->setExpectedException(
			Exception::class,
			'Alias not found: '.$alias
		);

		$this->options->instanciateAlias($alias);
	}

	/**
	 * @covers SunCoastConnection\ClaimsToOEMR\Document\Options::instanciateAlias()
	 */
	public function testInstanciateAlias() {
		$alias = 'TestAlias';
		$class = Options::class;
		$paramiters = [
			'c' => 3,
			'4' => 'd'
		];

		$this->options->shouldReceive('resolveAlias')
			->once()
			->with($alias)
			->andReturn($class);

		$optionClass = $this->options->instanciateAlias(
			$alias,
			[
				$paramiters
			]
		);

		$this->assertInstanceOf(
			$class,
			$optionClass,
			'Wrong class was instanciates'
		);

		$this->assertEquals(
			$paramiters,
			$optionClass->all(),
			'Class was not instanciated with correct paramiters'
		);
	}

	/**
	 * @covers SunCoastConnection\ClaimsToOEMR\Document\Options::getSubset()
	 */
	public function testGetSubsetWithMissingKeyAndNullDefault() {
		$key = 'set';
		$subset = null;

		$this->options->shouldReceive('get')
			->once()
			->with($key, $subset)
			->andReturn($subset);

		$this->assertNull(
			$this->options->getSubset($key, $subset),
			'Subset does not exist null not returned'
		);
	}

	/**
	 * @covers SunCoastConnection\ClaimsToOEMR\Document\Options::getSubset()
	 */
	public function testGetSubsetWithMissingKey() {
		$key = 'set';
		$subset = [
		];

		$this->options->shouldReceive('get')
			->once()
			->with($key, $subset)
			->andReturn($subset);

		$this->options->shouldReceive('getInstance')
			->once()
			->with($subset)
			->andReturnSelf();

		$this->assertSame(
			$this->options,
			$this->options->getSubset($key),
			'Subset not returned correctly'
		);
	}

	/**
	 * @covers SunCoastConnection\ClaimsToOEMR\Document\Options::getSubset()
	 */
	public function testGetSubset() {
		$key = 'set';
		$subset = [
			'c' => 3,
			'4' => 'd'
		];

		$this->options->shouldReceive('get')
			->once()
			->with($key, [])
			->andReturn($subset);

		$this->options->shouldReceive('getInstance')
			->once()
			->with($subset)
			->andReturnSelf();

		$this->assertSame(
			$this->options,
			$this->options->getSubset($key),
			'Subset not returned correctly'
		);
	}
}