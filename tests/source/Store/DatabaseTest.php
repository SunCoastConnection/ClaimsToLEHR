<?php

namespace SunCoastConnection\ClaimsToOEMR\Tests\Store;

use \DateTime;
use \Illuminate\Container\Container;
use \Illuminate\Database\Capsule\Manager;
use \Illuminate\Database\Events\QueryExecuted;
use \Illuminate\Events\Dispatcher;
use \SunCoastConnection\ClaimsToOEMR\Document\Options;
use \SunCoastConnection\ClaimsToOEMR\Models;
use \SunCoastConnection\ClaimsToOEMR\Store\Database;
use \SunCoastConnection\ClaimsToOEMR\Tests\BaseTestCase;
use \org\bovigo\vfs\vfsStream;

class DatabaseTest extends BaseTestCase {

	protected $database;

	public function setUp() {
		parent::setUp();

		$this->database = $this->getMockery(
			Database::class
		)->makePartial();
	}

	/**
	 * @covers SunCoastConnection\ClaimsToOEMR\Store\Database::onConstruct()
	 */
	public function testOnConstructWithNoQueryLog() {
		$this->database->shouldAllowMockingProtectedMethods();

		$this->database->shouldReceive('options->get')
			->once()
			->with('Store.default', 'memory')
			->andReturn('memory');

		$connection = [
			'driver'	=> 'sqlite',
			'database'	=> ':memory:',
		];

		$this->database->shouldReceive('options->get')
			->once()
			->with('Store.connections.memory')
			->andReturn($connection);

		$manager = $this->getMockery(
			'overload:'.Manager::class
		);

		$manager->shouldReceive('addConnection')
			->once()
			->with($connection);

		$manager->shouldReceive('setAsGlobal')
			->once();

		$manager->shouldReceive('bootEloquent')
			->once();

		$this->database->shouldReceive('options->get')
			->once()
			->with('Store.queryLog', false)
			->andReturn(false);

		$this->database->onConstruct();
	}

	/**
	 * @covers SunCoastConnection\ClaimsToOEMR\Store\Database::onConstruct()
	 */
	public function testOnConstructWithQueryLog() {
		$this->database->shouldAllowMockingProtectedMethods();

		$this->database->shouldReceive('options->get')
			->once()
			->with('Store.default', 'memory')
			->andReturn('memory');

		$connection = [
			'driver'	=> 'sqlite',
			'database'	=> ':memory:',
		];

		$this->database->shouldReceive('options->get')
			->once()
			->with('Store.connections.memory')
			->andReturn($connection);

		$manager = $this->getMockery(
			'overload:'.Manager::class
		);

		$manager->shouldReceive('addConnection')
			->once()
			->with($connection);

		$manager->shouldReceive('setAsGlobal')
			->once();

		$manager->shouldReceive('bootEloquent')
			->once();

		$this->database->shouldReceive('options->get')
			->once()
			->with('Store.queryLog', false)
			->andReturn(true);

		$container = $this->getMockery(
			'overload:'.Container::class
		);

		$dispatcher = $this->getMockery(
			'overload:'.Dispatcher::class)
		;

		$manager->shouldReceive('setEventDispatcher')
			->once();

		$manager->shouldReceive('connection->enableQueryLog')
			->once();

		$manager->shouldReceive('connection->listen')
			->once()
			->with(\Mockery::type('callable'));

		$this->database->onConstruct();
	}

	/**
	 * @covers SunCoastConnection\ClaimsToOEMR\Store\Database::logQuery()
	 */
	public function testLogQueryWithEcho() {
		$queryExecuted = $this->getMockery(
			QueryExecuted::class
		);

		$dateTimeString = '2000-12-31 23:59:59';
		$dateTime = new DateTime($dateTimeString);

		$queryExecuted->bindings = [
			$dateTime,
			'ABC'
		];

		$queryExecuted->sql = 'select * from "table" where "date" = ? and "field1" = ?';

		$queryExecuted->time = 1.04;

		$this->database->shouldAllowMockingProtectedMethods()
			->shouldReceive('options->get')
			->with('Store.queryLog', false)
			->andReturn(true);

		$this->expectOutputString(
			'[1.04]: select * from "table" where "date" = "'.
				$dateTimeString.
				'" and "field1" = "ABC"'.PHP_EOL
		);

		$this->database->logQuery($queryExecuted);
	}

	/**
	 * @covers SunCoastConnection\ClaimsToOEMR\Store\Database::logQuery()
	 */
	public function testLogQueryWithFile() {
		$queryExecuted = $this->getMockery(
			QueryExecuted::class
		);

		$dateTimeString = '2000-12-31 23:59:59';
		$dateTime = new DateTime($dateTimeString);

		$queryExecuted->bindings = [
			$dateTime,
			'ABC'
		];

		$queryExecuted->sql = 'select * from "table" where "date" = ? and "field1" = ?';

		$queryExecuted->time = 1.04;

		$fileContents = 'This file should be appended to'.PHP_EOL;

		$root = vfsStream::setup();

		$file = vfsStream::newFile('query.log')
			->at($root)
			->setContent($fileContents);

		$this->database->shouldAllowMockingProtectedMethods()
			->shouldReceive('options->get')
			->with('Store.queryLog', false)
			->andReturn($file->url());

		$this->database->logQuery($queryExecuted);

		$this->assertEquals(
			$fileContents.'[1.04]: select * from "table" where "date" = "'.
				$dateTimeString.'" and "field1" = "ABC"'.PHP_EOL,
			$file->getContent(),
			'Log file was not written to correctly'
		);
	}

	/**
	 * @covers SunCoastConnection\ClaimsToOEMR\Store\Database::getManager()
	 */
	public function testGetManager() {
		$manager = $this->getMockery(
			Manager::class
		);

		$this->setProtectedProperty(
			$this->database,
			'manager',
			$manager
		);

		$this->assertSame(
			$manager,
			$this->database->getManager(),
			'Manager not returned'
		);
	}

	/**
	 * @covers SunCoastConnection\ClaimsToOEMR\Store\Database::getModelClass()
	 */
	public function testGetModelClass() {
		$className = '\\Namespace\\AbcdEfg';

		$options = $this->getMockery(
			Options::class
		);

		$this->database->shouldAllowMockingProtectedMethods()
			->shouldReceive('options')
			->once()
			->andReturn($options);

		$options->shouldReceive('get')
			->once()
			->with('Aliases.abcdEfg')
			->andReturn($className);

		$this->assertSame(
			$className,
			$this->callProtectedMethod(
				$this->database,
				'getModelClass',
				[ 'abcdEfg' ]
			),
			'Class name not returned correctly'
		);
	}

	/**
	 * @covers SunCoastConnection\ClaimsToOEMR\Store\Database::addIfMissing()
	 */
	public function testAddIfMissing() {
		$array = [];

		$this->callProtectedMethod(
			$this->database,
			'addIfMissing',
			[
				'test',
				&$array,
				'123'
			]
		);

		$this->assertArrayHasKey(
			'test',
			$array,
			'Index should have been added to array'
		);

		$this->assertEquals(
			'123',
			$array['test'],
			'Index value in array not set correctly'
		);
	}

	/**
	 * @covers SunCoastConnection\ClaimsToOEMR\Store\Database::findRecord()
	 */
	public function testFindRecord() {
		$model = $this->getMockery(
			'overload:'.Models\X12Partners::class
		);

		$data = [
			'field1' => 'f1',
			'field2' => 'f2',
			'field3' => 'f3',
			'field4' => 'f4'
		];

		$matchFields = [
			'field1',
			'field3'
		];

		$model->shouldReceive('where')
			->once()
			->with([
				'field1' => 'f1',
				'field3' => 'f3'
			])->andReturn($model);

		$model->shouldReceive('first')
			->once()
			->andReturn($model);

		$this->assertSame(
			$model,
			$this->callProtectedMethod(
				$this->database,
				'findRecord',
				[
					get_class($model),
					$data,
					$matchFields
				]
			),
			'First record not returned'
		);
	}

	/**
	 * @covers SunCoastConnection\ClaimsToOEMR\Store\Database::insertRecord()
	 */
	public function testInsertRecord() {
		$model = $this->getMockery(
			'overload:'.Models\X12Partners::class
		);

		$data = [
			'field1' => 'f1',
			'field2' => 'f2',
			'field3' => 'f3',
			'field4' => 'f4'
		];

		$this->database->shouldAllowMockingProtectedMethods();

		$this->database->shouldReceive('getModelClass')
			->once()
			->with('x12Partners')
			->andReturn(get_class($model));

		$this->database->shouldReceive('updateRecord')
			->once()
			->with(\Mockery::type(get_class($model)), $data)
			->andReturn(123);

		$this->assertInstanceOf(
			get_class($model),
			$this->callProtectedMethod(
				$this->database,
				'insertRecord',
				[
					'x12Partners',
					$data,
				]
			),
			'Insert record not returned correctly'
		);
	}

	/**
	 * @covers SunCoastConnection\ClaimsToOEMR\Store\Database::updateRecord()
	 */
	public function testUpdateRecord() {
		$model = $this->getMockery(
			'overload:'.Models\Addresses::class
		);

		$data = [
			'id' => '123',
			'field1' => 'f1',
			'field2' => 'f2',
		];

		$model->shouldReceive('save')
			->once();

		$this->database->updateRecord($model, $data);

		$this->assertEquals(
			$data['id'],
			$model->id,
			'Field value not set'
		);

		$this->assertEquals(
			$data['field1'],
			$model->field1,
			'Field value not set'
		);

		$this->assertEquals(
			$data['field2'],
			$model->field2,
			'Field value not set'
		);
	}

	/**
	 * @covers SunCoastConnection\ClaimsToOEMR\Store\Database::insertUpdateRecord()
	 */
	public function testInsertUpdateRecordWithMissingRecord() {
		$data = [
			'field1' => 'f1',
			'field2' => 'f2',
			'field3' => 'f3',
			'field4' => 'f4'
		];

		$matchFields = [
			'field1',
			'field3'
		];

		$model = $this->getMockery(
			Models\X12Partners::class
		);

		$this->database->shouldAllowMockingProtectedMethods();

		$this->database->shouldReceive('getModelClass')
			->once()
			->with('x12Partners')
			->andReturn(get_class($model));

		$this->database->shouldReceive('findRecord')
			->once()
			->with(get_class($model), $data, $matchFields)
			->andReturnNull();

		$this->database->shouldReceive('insertRecord')
			->once()
			->with('x12Partners', $data)
			->andReturn($model);

		$this->database->shouldNotReceive('updateRecord');

		$this->assertSame(
			$model,
			$this->callProtectedMethod(
				$this->database,
				'insertUpdateRecord',
				[
					'x12Partners',
					$data,
					$matchFields

				]
			),
			'Record not returned correctly'
		);
	}

	/**
	 * @covers SunCoastConnection\ClaimsToOEMR\Store\Database::insertUpdateRecord()
	 */
	public function testInsertUpdateRecordWithFoundRecord() {
		$data = [
			'field1' => 'f1',
			'field2' => 'f2',
			'field3' => 'f3',
			'field4' => 'f4'
		];

		$matchFields = [
			'field1',
			'field3'
		];

		$model = $this->getMockery(
			Models\X12Partners::class
		);

		$this->database->shouldAllowMockingProtectedMethods();

		$this->database->shouldReceive('getModelClass')
			->once()
			->with('x12Partners')
			->andReturn(get_class($model));

		$this->database->shouldReceive('findRecord')
			->once()
			->with(get_class($model), $data, $matchFields)
			->andReturn($model);

		$this->database->shouldNotReceive('insertRecord');

		$this->database->shouldReceive('updateRecord')
			->once()
			->with($model, $data)
			->andReturn(123);

		$this->assertEquals(
			$model,
			$this->callProtectedMethod(
				$this->database,
				'insertUpdateRecord',
				[
					'x12Partners',
					$data,
					$matchFields

				]
			),
			'Record not returned correctly'
		);
	}

	/**
	 * @covers SunCoastConnection\ClaimsToOEMR\Store\Database::recordCount()
	 */
	public function testRecordCount() {
		$model = $this->getMockery(
			Models\X12Partners::class
		);

		$this->database->shouldAllowMockingProtectedMethods();

		$this->database->shouldReceive('getModelClass')
			->once()
			->with('x12Partners')
			->andReturn(get_class($model));

		$model->shouldReceive('count')
			->once()
			->andReturn(3);

		$this->assertEquals(
			3,
			$this->database->recordCount('x12Partners'),
			'Model count not returned correctly'
		);
	}

	/**
	 * @covers SunCoastConnection\ClaimsToOEMR\Store\Database::storeAddress()
	 */
	public function testStoreAddress() {
		$data = [
			'line1' => 'line1',
			'line2' => 'line2',
			'city' => 'city',
			'state' => 'state',
			'zip' => 'zip',
			'plus_four' => 'plus_four',
			'country' => 'country',
			'foreign_id' => 'foreign_id'
		];

		$model = $this->getMockery(
			Models\Addresses::class
		);

		$this->database->shouldAllowMockingProtectedMethods()
			->shouldReceive('insertUpdateRecord')
			->once()
			->with('address', $data, [
				'line1',
				'line2',
				'city',
				'state',
				'zip',
				'plus_four',
				'country'
			])
			->andReturn($model);

		$model->shouldReceive('getAttribute')
			->once()
			->with('id')
			->andReturn(123);

		$this->assertEquals(
			123,
			$this->database->storeAddress($data),
			'Record not returned'
		);
	}

	/**
	 * @covers SunCoastConnection\ClaimsToOEMR\Store\Database::storeBilling()
	 */
	public function testStoreBilling() {
		$data = [
			'date' => 'date',
			'code_type' => 'code_type',
			'code' => 'code',
			'pid' => 'pid',
			'provider_id' => 'provider_id',
			'user' => 'user',
			'groupname' => 'groupname',
			'authorized' => 'authorized',
			'encounter' => 'encounter',
			'code_text' => 'code_text',
			'billed' => 'billed',
			'activity' => 'activity',
			'payer_id' => 'payer_id',
			'bill_process' => 'bill_process',
			'bill_date' => 'bill_date',
			'process_date' => 'process_date',
			'process_file' => 'process_file',
			'modifier' => 'modifier',
			'units' => 'units',
			'fee' => 'fee',
			'justify' => 'justify'
		];

		$model = $this->getMockery(
			Models\Billing::class
		);

		$this->database->shouldAllowMockingProtectedMethods()
			->shouldReceive('insertUpdateRecord')
			->once()
			->with(
				'billing',
				$data,
				[
					'provider_id',
					'payer_id',
					'pid',
					'encounter',
					'code'
				]
			)
			->andReturn($model);

		$model->shouldReceive('getAttribute')
			->once()
			->with('id')
			->andReturn(123);

		$this->assertEquals(
			123,
			$this->database->storeBilling($data),
			'Record not returned'
		);
	}

	/**
	 * @covers SunCoastConnection\ClaimsToOEMR\Store\Database::storeFacility()
	 */
	public function testStoreFacility() {
		$data = [
			'name' => 'name',
			'street' => 'street',
			'city' => 'city',
			'state' => 'state',
			'postal_code' => 'postal_code',
			'country_code' => 'country_code',
			'federal_ein' => 'federal_ein',
			'service_location' => 'service_location',
			'billing_location' => 'billing_location',
			'accepts_assignment' => 'accepts_assignment',
			'pos_code' => 'pos_code',
			'attn' => 'attn',
			'domain_identifier' => 'domain_identifier',
			'tax_id_type' => 'tax_id_type',
			'color' => 'color',
			'primary_business_entity' => 'primary_business_entity'
		];

		$model = $this->getMockery(
			Models\Facilities::class
		);

		$this->database->shouldAllowMockingProtectedMethods()
			->shouldReceive('insertUpdateRecord')
			->once()
			->with('facility', $data, [
				'name',
				'street',
				'city',
				'state',
				'postal_code',
				'country_code',
				'federal_ein',
				'domain_identifier'
			])
			->andReturn($model);

		$model->shouldReceive('getAttribute')
			->once()
			->with('id')
			->andReturn(123);

		$this->assertEquals(
			123,
			$this->database->storeFacility($data),
			'Record not returned'
		);
	}

	/**
	 * @covers SunCoastConnection\ClaimsToOEMR\Store\Database::storeFormEncounter()
	 */
	public function testStoreFormEncounter() {
		$data = [
			'date' => 'date',
			'reason' => '',
			'facility' => '',
			'facility_id' => 'facility_id',
			'pid' => 'pid',
			'encounter' => 'encounter',
			'onset_date' => 'onset_date',
			'sensitivity' => 'sensitivity',
			'billing_note' => '',
			'pc_catid' => 'pc_catid',
			'last_level_billed' => 'last_level_billed',
			'last_level_closed' => 'last_level_closed',
			'last_stmt_date' => 'last_stmt_date',
			'stmt_count' => 'stmt_count',
			'provider_id' => 'provider_id',
			'supervisor_id' => 'supervisor_id',
			'invoice_refno' => 'invoice_refno',
			'referral_source' => 'referral_source',
			'billing_facility' => 'billing_facility'
		];

		$model = $this->getMockery(
			Models\FormEncounters::class
		);

		$this->database->shouldAllowMockingProtectedMethods()
			->shouldReceive('insertUpdateRecord')
			->once()
			->with(
				'formEncounter',
				$data,
				[
					'facility_id',
					'provider_id',
					'pid',
					'encounter',
					'facility'
				]
			)
			->andReturn($model);

		$model->shouldReceive('getAttribute')
			->once()
			->with('id')
			->andReturn(123);

		$this->assertEquals(
			123,
			$this->database->storeFormEncounter($data),
			'Record not returned'
		);
	}

	/**
	 * @covers SunCoastConnection\ClaimsToOEMR\Store\Database::storeForm()
	 */
	public function testStoreForm() {
		$data = [
			'date' => 'date',
			'encounter' => 'encounter',
			'form_name' => '',
			'form_id' => 'form_id',
			'pid' => 'pid',
			'user' => 'user',
			'groupname' => 'groupname',
			'authorized' => 'authorized',
			'deleted' => 'deleted',
			'formdir' => ''
		];

		$model = $this->getMockery(
			Models\Forms::class
		);

		$this->database->shouldAllowMockingProtectedMethods()
			->shouldReceive('insertUpdateRecord')
			->once()
			->with(
				'form',
				$data,
				[
					'form_id',
					'user',
					'pid',
					'encounter'
				]
			)
			->andReturn($model);

		$model->shouldReceive('getAttribute')
			->once()
			->with('id')
			->andReturn(123);

		$this->assertEquals(
			123,
			$this->database->storeForm($data),
			'Record not returned'
		);
	}

	/**
	 * @covers SunCoastConnection\ClaimsToOEMR\Store\Database::storeGroup()
	 */
	public function testStoreGroup() {
		$data = [
			'name' => '',
			'user' => 'user'
		];

		$model = $this->getMockery(
			Models\Groups::class
		);

		$this->database->shouldAllowMockingProtectedMethods()
			->shouldReceive('insertUpdateRecord')
			->once()
			->with('group', $data, [
				'user',
			])
			->andReturn($model);

		$model->shouldReceive('getAttribute')
			->once()
			->with('id')
			->andReturn(123);

		$this->assertEquals(
			123,
			$this->database->storeGroup($data),
			'Record not returned'
		);
	}

	/**
	 * @covers SunCoastConnection\ClaimsToOEMR\Store\Database::storeInsuranceCompany()
	 */
	public function testStoreInsuranceCompany() {
		$data = [
			'name' => 'name',
			'attn' => 'attn',
			'cms_id' => 'cms_id',
			'x12_receiver_id' => 'x12_receiver_id',
			'x12_default_partner_id' => 'x12_default_partner_id'
		];

		$model = $this->getMockery(
			Models\InsuranceCompanies::class
		);

		$this->database->shouldAllowMockingProtectedMethods()
			->shouldReceive('insertUpdateRecord')
			->once()
			->with('insuranceCompany', $data, [
				'name',
				'cms_id'
			])
			->andReturn($model);

		$model->shouldReceive('getAttribute')
			->once()
			->with('id')
			->andReturn(123);

		$this->assertEquals(
			123,
			$this->database->storeInsuranceCompany($data),
			'Record not returned'
		);
	}

	/**
	 * @covers SunCoastConnection\ClaimsToOEMR\Store\Database::storeInsuranceData()
	 */
	public function testStoreInsuranceData() {
		$data = [
			'type' => 'type',
			'provider' => 'provider',
			'plan_name' => 'plan_name',
			'policy_number' => 'policy_number',
			'group_number' => 'group_number',
			'subscriber_lname' => 'subscriber_lname',
			'subscriber_mname' => 'subscriber_mname',
			'subscriber_fname' => 'subscriber_fname',
			'subscriber_relationship' => 'subscriber_relationship',
			'subscriber_DOB' => 'subscriber_DOB',
			'subscriber_street' => 'subscriber_street',
			'subscriber_postal_code' => 'subscriber_postal_code',
			'subscriber_city' => 'subscriber_city',
			'subscriber_state' => 'subscriber_state',
			'subscriber_country' => 'subscriber_country',
			'date' => 'date',
			'pid' => 'pid',
			'subscriber_sex' => 'subscriber_sex',
			'accept_assignment' => 'accept_assignment'
		];

		$model = $this->getMockery(
			Models\InsuranceData::class
		);

		$this->database->shouldAllowMockingProtectedMethods()
			->shouldReceive('insertUpdateRecord')
			->once()
			->with('insuranceData', $data, [
				'pid',
				'type',
				'plan_name',
				'policy_number',
				'group_number'
			])
			->andReturn($model);

		$model->shouldReceive('getAttribute')
			->once()
			->with('id')
			->andReturn(123);

		$this->assertEquals(
			123,
			$this->database->storeInsuranceData($data),
			'Record not returned'
		);
	}

	/**
	 * @covers SunCoastConnection\ClaimsToOEMR\Store\Database::storePatientData()
	 */
	public function testStorePatientData() {
		$data = [
			'language' => 'language',
			'fname' => 'fname',
			'lname' => 'lname',
			'mname' => '',
			'DOB' => 'DOB',
			'street' => 'street',
			'postal_code' => 'postal_code',
			'city' => 'city',
			'state' => 'state',
			'date' => 'date',
			'sex' => 'sex',
			'providerID' => 'providerID',
			'pubpid' => 'pubpid',
			'pid' => 'pid'
		];

		$model = $this->getMockery(
			Models\PatientData::class
		);

		$model->shouldReceive('getAttribute')
			->times(3)
			->with('id')
			->andReturn(123);

		$model->shouldReceive('getAttribute')
			->once()
			->with('pubpid')
			->andReturn(123);

		$model->shouldReceive('getAttribute')
			->once()
			->with('pid')
			->andReturn('');

		$model->shouldReceive('setAttribute')
			->once()
			->with('pubpid', 123);

		$model->shouldReceive('setAttribute')
			->once()
			->with('pid', 123);

		$model->shouldReceive('save')
			->once();

		$this->database->shouldAllowMockingProtectedMethods()
			->shouldReceive('insertUpdateRecord')
			->once()
			->with('patientData', $data, [
				'fname',
				'lname',
				'mname',
				'DOB',
				'sex'
			])
			->andReturn($model);

		$this->assertEquals(
			123,
			$this->database->storePatientData($data),
			'Record not returned'
		);
	}

	/**
	 * @covers SunCoastConnection\ClaimsToOEMR\Store\Database::storePhoneNumber()
	 */
	public function testStorePhoneNumber() {
		$data = [
			'country_code' => 'country_code',
			'area_code' => 'area_code',
			'prefix' => 'prefix',
			'number' => 'number',
			'type' => 'type',
			'foreign_id' => 'foreign_id'
		];

		$model = $this->getMockery(
			Models\PhoneNumbers::class
		);

		$this->database->shouldAllowMockingProtectedMethods()
			->shouldReceive('insertUpdateRecord')
			->once()
			->with('phoneNumber', $data, [
				'country_code',
				'area_code',
				'prefix',
				'number',
				'type',
				'foreign_id'
			])
			->andReturn($model);

		$model->shouldReceive('getAttribute')
			->once()
			->with('id')
			->andReturn(123);

		$this->assertEquals(
			123,
			$this->database->storePhoneNumber($data),
			'Record not returned'
		);
	}

	/**
	 * @covers SunCoastConnection\ClaimsToOEMR\Store\Database::storeUser()
	 */
	public function testStoreUser() {
		$data = [
			'username' => 'username',
			'password' => '',
			'authorized' => 'authorized',
			'fname' => 'fname',
			'mname' => 'mname',
			'lname' => 'lname',
			'federaltaxid' => 'federaltaxid',
			'federaldrugid' => 'federaldrugid',
			'facility' => 'facility',
			'facility_id' => 'facility_id',
			'see_auth' => 'see_auth',
			'active' => 'active',
			'npi' => 'npi',
			'cal_ui' => 'cal_ui',
			'taxonomy' => '207Q00000X',
			'calendar' => 'calendar',
			'abook_type' => 'abook_type',
			'state_license_number' => 'state_license_number'
		];

		$model = $this->getMockery(
			Models\Users::class
		);

		$this->database->shouldAllowMockingProtectedMethods()
			->shouldReceive('insertUpdateRecord')
			->once()
			->with('user', $data, [
				'username',
				'federaltaxid'
			])
			->andReturn($model);

		$model->shouldReceive('getAttribute')
			->once()
			->with('id')
			->andReturn(123);

		$this->assertEquals(
			123,
			$this->database->storeUser($data),
			'Record not returned'
		);
	}

	/**
	 * @covers SunCoastConnection\ClaimsToOEMR\Store\Database::storeX12Partner()
	 */
	public function testStoreX12Partner() {
		$data = [
			'name' => 'name',
			'id_number' => 'id_number',
			'x12_sender_id' => 'x12_sender_id',
			'x12_receiver_id' => 'x12_receiver_id',
			'x12_version' => 'x12_version',
			'x12_isa01' => 'x12_isa01',
			'x12_isa02' => 'x12_isa02',
			'x12_isa03' => 'x12_isa03',
			'x12_isa04' => 'x12_isa04',
			'x12_isa05' => 'x12_isa05',
			'x12_isa07' => 'x12_isa07',
			'x12_isa14' => 'x12_isa14',
			'x12_isa15' => 'x12_isa15',
			'x12_gs02' => 'x12_gs02',
			'x12_gs03' => 'x12_gs03'
		];

		$model = $this->getMockery(
			Models\X12Partners::class
		);

		$this->database->shouldAllowMockingProtectedMethods()
			->shouldReceive('insertUpdateRecord')
			->once()
			->with('x12Partners', $data, [
				'x12_gs03'
			])
			->andReturn($model);

		$model->shouldReceive('getAttribute')
			->once()
			->with('id')
			->andReturn(123);

		$this->assertEquals(
			123,
			$this->database->storeX12Partner($data),
			'Record not returned'
		);
	}

}