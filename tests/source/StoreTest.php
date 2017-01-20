<?php

namespace SunCoastConnection\ClaimsToEMR\Tests;

use \SunCoastConnection\ClaimsToEMR\Store;
use \SunCoastConnection\ClaimsToEMR\Tests\BaseTestCase;
use \SunCoastConnection\ClaimsToEMR\Tests\StoreMock;
use \SunCoastConnection\ParseX12\Options;

class StoreTest extends BaseTestCase {

	protected $store;

	protected $tableCounts = [
		'address' => 1,
		'billing' => 2,
		'facility' => 3,
		'formEncounter' => 4,
		'form' => 5,
		'group' => 6,
		'insuranceCompany' => 7,
		'insuranceData' => 8,
		'patientData' => 9,
		'phoneNumber' => 0,
		'user' => 1,
		'x12Partners' => 2
	];

	public function returnTableCounts($table) {
		if(array_key_exists($table, $this->tableCounts)) {
			return $this->tableCounts[$table];
		}
	}

	public function setUp() {
		parent::setUp();

		$this->store = $this->getMockery(
			Store::class
		)->makePartial();
	}

	/**
	 * @covers SunCoastConnection\ClaimsToEMR\Store::getInstance()
	 */
	public function testGetInstance() {
		$options = $this->getMockery(
			Options::class
		);

		$store = StoreMock::getInstance($options);

		$this->assertInstanceOf(
			Store::class,
			$store,
			'Expected new instance of '.Store::class.'.'
		);
	}

	/**
	 * @covers SunCoastConnection\ClaimsToEMR\Store::__construct()
	 */
	public function testConstructWithNoOnConstruct() {
		$options = $this->getMockery(
			Options::class
		);

		$this->store->shouldAllowMockingProtectedMethods();

		$this->store->shouldReceive('options')
			->once()
			->with($options);

		$this->store->shouldNotReceive('onConstruct');

		$this->store->__construct($options);
	}

	/**
	 * @covers SunCoastConnection\ClaimsToEMR\Store::__construct()
	 */
	public function testConstructWithOnConstruct() {
		$options = $this->getMockery(
			Options::class
		);

		$store = $this->getMockery(
			StoreMock::class
		)->makePartial();

		$store->shouldAllowMockingProtectedMethods();

		$store->shouldReceive('options')
			->once()
			->with($options);

		$store->shouldReceive('onConstruct')
			->once();

		$store->__construct($options);
	}

	/**
	 * @covers SunCoastConnection\ClaimsToEMR\Store::options()
	 */
	public function testOptions() {
		$this->assertNull(
			$this->store->options(),
			'Options should return null when empty.'
		);

		$options = $this->getMockery(
			Options::class
		);

		$this->assertSame(
			$options,
			$this->store->options($options),
			'Options should return set option object when setting value.'
		);

		$this->assertSame(
			$options,
			$this->store->options(),
			'Options should return set option object after setting value.'
		);
	}

	/**
	 * @covers SunCoastConnection\ClaimsToEMR\Store::printTableCounts()
	 */
	public function testPrintTableCounts() {
		$tableNames = [
			'insuranceCompany',
			'user',
		];

		$tableCounts = [
			'insuranceCompany' => 1,
			'user' => 3,
		];

		$this->store->shouldReceive('tableCounts')
			->once()
			->with($tableNames)
			->andReturn($tableCounts);

		$this->expectOutputRegex('/^\w+: \t+\d+$/m');

		$this->store->printTableCounts($tableNames);
	}

	/**
	 * @covers SunCoastConnection\ClaimsToEMR\Store::tableCounts()
	 */
	public function testTableCountsWithString() {
		$this->store->shouldReceive('recordCount')
			->once()
			->with('address')
			->andReturn(3);

		$this->assertEquals(
			[
				'address' => 3,
			],
			$this->store->tableCounts('address'),
			'Table counts did not return correctly'
		);
	}

	/**
	 * @covers SunCoastConnection\ClaimsToEMR\Store::tableCounts()
	 */
	public function testTableCountsWithNoArray() {
		$this->store->shouldReceive('recordCount')
			->andReturnUsing([$this, 'returnTableCounts']);

		$this->assertEquals(
			$this->tableCounts,
			$this->store->tableCounts(),
			'Table counts did not return correctly'
		);
	}

	/**
	 * @covers SunCoastConnection\ClaimsToEMR\Store::tableCounts()
	 */
	public function testTableCountsWithArray() {
		$this->store->shouldReceive('recordCount')
			->andReturnUsing([$this, 'returnTableCounts']);

		$this->assertEquals(
			$this->tableCounts,
			$this->store->tableCounts(array_keys($this->tableCounts)),
			'Table counts did not return correctly'
		);
	}

	/**
	 * @covers SunCoastConnection\ClaimsToEMR\Store::tableCounts()
	 */
	public function testTableCountsWithMissingTable() {
		$this->store->shouldReceive('recordCount')
			->andReturnUsing([$this, 'returnTableCounts']);

		$this->assertEquals(
			[
				'address' => $this->returnTableCounts('address'),
			],
			$this->store->tableCounts([
				'address',
				'nonExistant'
			]),
			'Table counts did not return correctly'
		);
	}

}