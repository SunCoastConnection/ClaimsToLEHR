<?php

namespace SunCoastConnection\ClaimsToOEMR\Tests\Store;

use \SunCoastConnection\ClaimsToOEMR\Store\Arr,
	\SunCoastConnection\ClaimsToOEMR\Tests\BaseTestCase;

class ArrTest extends BaseTestCase {

	protected $arr;

	public function setUp() {
		parent::setUp();

		$this->arr = $this->getMockery(
			Arr::class
		)->makePartial();
	}

	/**
	 * @covers SunCoastConnection\ClaimsToOEMR\Store\Arr::onConstruct()
	 */
	public function testOnConstruct() {
		$this->setProtectedProperty(
			$this->arr,
			'tableNames',
			[ 'A', 'B', 'C' ]
		);

		$this->callProtectedMethod(
			$this->arr,
			'onConstruct'
		);

		$this->assertEquals(
			[ 'A' => [], 'B' => [], 'C' => [] ],
			$this->getProtectedProperty(
				$this->arr,
				'tables'
			),
			'Tables array not setup correctly'
		);
	}

	/**
	 * @covers SunCoastConnection\ClaimsToOEMR\Store\Arr::recordCount()
	 */
	public function testRecordCountWithMissingTable() {
		$this->assertNull(
			$this->arr->recordCount('nonExistant'),
			'Record count did not return correctly'
		);
	}

	/**
	 * @covers SunCoastConnection\ClaimsToOEMR\Store\Arr::recordCount()
	 */
	public function testRecordCount() {
		$this->setProtectedProperty(
			$this->arr,
			'tables',
			[
				'test' => [ 1, 2, 3 ]
			]
		);

		$this->assertEquals(
			3,
			$this->arr->recordCount('test'),
			'Record count did not return correctly'
		);
	}

	/**
	 * @covers SunCoastConnection\ClaimsToOEMR\Store\Arr::findRecord()
	 */
	public function testFindRecord() {
		$tables = [
			'test' => [
				[ 'one' => 3, 'two' => 1, 'three' => 2 ],
				[ 'one' => 1, 'two' => 3, 'three' => 2 ],
				[ 'one' => 3, 'two' => 2, 'three' => 1 ],
				[ 'one' => 2, 'two' => 1, 'three' => 3 ],
				[ 'one' => 1, 'two' => 2, 'three' => 3 ],
				[ 'one' => 2, 'two' => 3, 'three' => 1 ]
			]
		];

		$data = [
			'one' => '1',
			'two' => '4',
			'three' => '3'
		];

		$matchFields = [
			'one',
			'three'
		];

		$this->setProtectedProperty(
			$this->arr,
			'tables',
			$tables
		);

		$this->assertEquals(
			4,
			$this->callProtectedMethod(
				$this->arr,
				'findRecord',
				[
					'test',
					$data,
					$matchFields
				]
			),
			'Correct record not returned'
		);
	}

	/**
	 * @covers SunCoastConnection\ClaimsToOEMR\Store\Arr::findRecord()
	 */
	public function testFindRecordWithMissingRecord() {
		$tables = [
			'test' => [
				[ 'one' => 3, 'two' => 2, 'three' => 1 ],
				[ 'one' => 1, 'two' => 2, 'three' => 3 ],
				[ 'one' => 2, 'two' => 1, 'three' => 3 ]
			]
		];

		$data = [
			'one' => '1',
			'two' => '4',
			'three' => '2'
		];

		$matchFields = [
			'one',
			'three'
		];

		$this->setProtectedProperty(
			$this->arr,
			'tables',
			$tables
		);

		$this->assertNull(
			$this->callProtectedMethod(
				$this->arr,
				'findRecord',
				[
					'test',
					$data,
					$matchFields
				]
			),
			'Correct record not returned'
		);
	}

	/**
	 * @covers SunCoastConnection\ClaimsToOEMR\Store\Arr::insertRecord()
	 */
	public function testInsertRecord() {
		$tables = [
			'test' => [
				[ 'one' => 1, 'two' => 2, 'three' => 3 ]
			]
		];

		$data = [
			'one' => '1',
			'three' => '2'
		];

		$this->setProtectedProperty(
			$this->arr,
			'tables',
			$tables
		);

		$this->arr->shouldAllowMockingProtectedMethods()
			->shouldReceive('updateRecord')
			->once()
			->with('test', 1, $data);

		$this->assertEquals(
			1,
			$this->callProtectedMethod(
				$this->arr,
				'insertRecord',
				[
					'test',
					$data
				]
			),
			'Record id not returned correctly'
		);
	}

	/**
	 * @covers SunCoastConnection\ClaimsToOEMR\Store\Arr::updateRecord()
	 */
	public function testUpdateRecord() {
		$fields = [
			'test' => [
				'one' => null,
				'two' => null,
				'three' => null
			]
		];

		$tables = [
			'test' => [
				[ 'one' => 1, 'two' => 2, 'three' => 3 ],
				[ 'one' => 1, 'two' => 2, 'three' => 2 ]
			]
		];

		$data = [
			'one' => '1',
			'three' => '2'
		];

		$this->setProtectedProperty(
			$this->arr,
			'fields',
			$fields
		);

		$this->setProtectedProperty(
			$this->arr,
			'tables',
			$tables
		);

		$this->callProtectedMethod(
			$this->arr,
			'updateRecord',
			[
				'test',
				1,
				$data
			]
		);

		$tables['test'][1] = [
			'one' => '1',
			'two' => null,
			'three' => '2',
		];

		$this->assertEquals(
			$tables,
			$this->getProtectedProperty(
				$this->arr,
				'tables'
			),
			'Record not update in table'
		);
	}

	/**
	 * @covers SunCoastConnection\ClaimsToOEMR\Store\Arr::insertUpdateRecord()
	 */
	public function testInsertUpdateRecordWithUpdate() {
		$table = 'test';

		$data = [
			'one' => '1',
			'three' => '2'
		];

		$fields = [
			'test' => [
				'one' => null,
				'two' => null,
				'three' => null
			]
		];

		$this->arr->shouldAllowMockingProtectedMethods();

		$this->arr->shouldReceive('findRecord')
			->once()
			->with($table, $data, $fields)
			->andReturn(21);

		$this->arr->shouldReceive('updateRecord')
			->once()
			->with($table, 21, $data);

		$this->assertEquals(
			21,
			$this->callProtectedMethod(
				$this->arr,
				'insertUpdateRecord',
				[
					$table,
					$data,
					$fields
				]
			),
			'Id not returned correctly'
		);
	}

	/**
	 * @covers SunCoastConnection\ClaimsToOEMR\Store\Arr::insertUpdateRecord()
	 */
	public function testInsertUpdateRecordWithInsert() {
		$table = 'test';

		$data = [
			'one' => '1',
			'three' => '2'
		];

		$fields = [
			'test' => [
				'one' => null,
				'two' => null,
				'three' => null
			]
		];

		$this->arr->shouldAllowMockingProtectedMethods();

		$this->arr->shouldReceive('findRecord')
			->once()
			->with($table, $data, $fields)
			->andReturnNull();

		$this->arr->shouldReceive('insertRecord')
			->once()
			->with($table, $data)
			->andReturn(21);

		$this->assertEquals(
			21,
			$this->callProtectedMethod(
				$this->arr,
				'insertUpdateRecord',
				[
					$table,
					$data,
					$fields
				]
			),
			'Id not returned correctly'
		);
	}

	/**
	 * @covers SunCoastConnection\ClaimsToOEMR\Store\Arr::storeAddress()
	 */
	public function testStoreAddress() {
		$data = [
			'one' => 1,
			'two' => 2,
			'three' => 3
		];

		$this->arr->shouldAllowMockingProtectedMethods()
			->shouldReceive('insertUpdateRecord')
			->once()
			->with(
				'address',
				$data,
				[
					'line1',
					'line2',
					'city',
					'state',
					'zip',
					'plus_four',
					'country',
				]
			)
			->andReturn(123);

		$this->assertEquals(
			123,
			$this->arr->storeAddress($data),
			'Record id not returned correctly'
		);
	}

	/**
	 * @covers SunCoastConnection\ClaimsToOEMR\Store\Arr::storeBilling()
	 */
	public function testStoreBilling() {
		$data = [
			'one' => 1,
			'two' => 2,
			'three' => 3
		];

		$this->arr->shouldAllowMockingProtectedMethods()
			->shouldReceive('insertRecord')
			->once()
			->with(
				'billing',
				$data
			)
			->andReturn(123);

		$this->assertEquals(
			123,
			$this->arr->storeBilling($data),
			'Record id not returned correctly'
		);
	}

	/**
	 * @covers SunCoastConnection\ClaimsToOEMR\Store\Arr::storeFacility()
	 */
	public function testStoreFacility() {
		$data = [
			'one' => 1,
			'two' => 2,
			'three' => 3
		];

		$this->arr->shouldAllowMockingProtectedMethods()
			->shouldReceive('insertUpdateRecord')
			->once()
			->with(
				'facility',
				$data,
				[
					'name',
					'street',
					'city',
					'state',
					'postal_code',
					'country_code',
					'federal_ein',
					'domain_identifier',
				]
			)
			->andReturn(123);

		$this->assertEquals(
			123,
			$this->arr->storeFacility($data),
			'Record id not returned correctly'
		);
	}

	/**
	 * @covers SunCoastConnection\ClaimsToOEMR\Store\Arr::storeFormEncounter()
	 */
	public function testStoreFormEncounter() {
		$data = [
			'one' => 1,
			'two' => 2,
			'three' => 3
		];

		$this->arr->shouldAllowMockingProtectedMethods()
			->shouldReceive('insertRecord')
			->once()
			->with(
				'formEncounter',
				$data
			)
			->andReturn(123);

		$this->assertEquals(
			123,
			$this->arr->storeFormEncounter($data),
			'Record id not returned correctly'
		);
	}

	/**
	 * @covers SunCoastConnection\ClaimsToOEMR\Store\Arr::storeForm()
	 */
	public function testStoreForm() {
		$data = [
			'one' => 1,
			'two' => 2,
			'three' => 3
		];

		$this->arr->shouldAllowMockingProtectedMethods()
			->shouldReceive('insertRecord')
			->once()
			->with(
				'form',
				$data
			)
			->andReturn(123);

		$this->assertEquals(
			123,
			$this->arr->storeForm($data),
			'Record id not returned correctly'
		);
	}

	/**
	 * @covers SunCoastConnection\ClaimsToOEMR\Store\Arr::storeGroup()
	 */
	public function testStoreGroup() {
		$data = [
			'one' => 1,
			'two' => 2,
			'three' => 3
		];

		$this->arr->shouldAllowMockingProtectedMethods()
			->shouldReceive('insertUpdateRecord')
			->once()
			->with(
				'group',
				$data,
				[
					'user'
				]
			)
			->andReturn(123);

		$this->assertEquals(
			123,
			$this->arr->storeGroup($data),
			'Record id not returned correctly'
		);
	}

	/**
	 * @covers SunCoastConnection\ClaimsToOEMR\Store\Arr::storeInsuranceCompany()
	 */
	public function testStoreInsuranceCompany() {
		$data = [
			'one' => 1,
			'two' => 2,
			'three' => 3
		];

		$this->arr->shouldAllowMockingProtectedMethods()
			->shouldReceive('insertUpdateRecord')
			->once()
			->with(
				'insuranceCompany',
				$data,
				[
					'name',
					'cms_id',
				]
			)
			->andReturn(123);

		$this->assertEquals(
			123,
			$this->arr->storeInsuranceCompany($data),
			'Record id not returned correctly'
		);
	}

	/**
	 * @covers SunCoastConnection\ClaimsToOEMR\Store\Arr::storeInsuranceData()
	 */
	public function testStoreInsuranceData() {
		$data = [
			'one' => 1,
			'two' => 2,
			'three' => 3
		];

		$this->arr->shouldAllowMockingProtectedMethods()
			->shouldReceive('insertUpdateRecord')
			->once()
			->with(
				'insuranceData',
				$data,
				[
					'pid',
					'type',
					'plan_name',
					'policy_number',
					'group_number',
				]
			)
			->andReturn(123);

		$this->assertEquals(
			123,
			$this->arr->storeInsuranceData($data),
			'Record id not returned correctly'
		);
	}

	/**
	 * @covers SunCoastConnection\ClaimsToOEMR\Store\Arr::storePatientData()
	 */
	public function testStorePatientData() {
		$data = [
			'one' => 1,
			'two' => 2,
			'three' => 3
		];

		$this->arr->shouldAllowMockingProtectedMethods()
			->shouldReceive('insertUpdateRecord')
			->once()
			->with(
				'patientData',
				$data,
				[
					'fname',
					'lname',
					'mname',
					'DOB',
					'sex',
				]
			)
			->andReturn(123);

		$this->assertEquals(
			123,
			$this->arr->storePatientData($data),
			'Record id not returned correctly'
		);
	}

	/**
	 * @covers SunCoastConnection\ClaimsToOEMR\Store\Arr::storePhoneNumber()
	 */
	public function testStorePhoneNumber() {
		$data = [
			'one' => 1,
			'two' => 2,
			'three' => 3
		];

		$this->arr->shouldAllowMockingProtectedMethods()
			->shouldReceive('insertRecord')
			->once()
			->with(
				'phoneNumber',
				$data
			)
			->andReturn(123);

		$this->assertEquals(
			123,
			$this->arr->storePhoneNumber($data),
			'Record id not returned correctly'
		);
	}

	/**
	 * @covers SunCoastConnection\ClaimsToOEMR\Store\Arr::storeUser()
	 */
	public function testStoreUser() {
		$data = [
			'one' => 1,
			'two' => 2,
			'three' => 3
		];

		$this->arr->shouldAllowMockingProtectedMethods()
			->shouldReceive('insertUpdateRecord')
			->once()
			->with(
				'user',
				$data,
				[
					'username',
					'federaltaxid'
				]
			)
			->andReturn(123);

		$this->assertEquals(
			123,
			$this->arr->storeUser($data),
			'Record id not returned correctly'
		);
	}

	/**
	 * @covers SunCoastConnection\ClaimsToOEMR\Store\Arr::storeX12Partner()
	 */
	public function testStoreX12Partner() {
		$data = [
			'one' => 1,
			'two' => 2,
			'three' => 3
		];

		$this->arr->shouldAllowMockingProtectedMethods()
			->shouldReceive('insertUpdateRecord')
			->once()
			->with(
				'x12Partners',
				$data,
				[
					'x12_gs03',
				]
			)
			->andReturn(123);

		$this->assertEquals(
			123,
			$this->arr->storeX12Partner($data),
			'Record id not returned correctly'
		);
	}

}