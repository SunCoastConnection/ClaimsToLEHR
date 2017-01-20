<?php

namespace SunCoastConnection\ClaimsToEMR\Store;

use \Illuminate\Container\Container;
use \Illuminate\Database\Capsule\Manager;
use \Illuminate\Database\Eloquent\Model;
use \Illuminate\Database\Events\QueryExecuted;
use \Illuminate\Events\Dispatcher;
use \SunCoastConnection\ClaimsToEMR\Store;
use \SunCoastConnection\ParseX12\Options;

class Database extends Store {

	/**
	 * Database manager
	 * @var \Illuminate\Database\Capsule\Manager
	 */
	protected $manager;

	/**
	 * Initialize Eloquent Database
	 */
	protected function onConstruct() {
		$this->manager = new Manager;

		$this->manager->addConnection(
			$this->options()->get(
				'Store.connections.'.
					$this->options()->get('Store.default', 'memory')
			)
		);

		$this->manager->setAsGlobal();
		$this->manager->bootEloquent();

		if($this->options()->get('Store.queryLog', false)) {
			$this->manager->setEventDispatcher(new Dispatcher(new Container));

			$this->manager->connection()->enableQueryLog();

			$this->manager->connection()->listen(
				function(QueryExecuted $queryExecuted) { $this->logQuery($queryExecuted); }
			);
		}
	}

	/**
	 * Log Queries after query is executed
	 *
	 * @param  \Illuminate\Database\Events\QueryExecuted  $queryExecuted  Eloquent Event
	 */
	protected function logQuery(QueryExecuted $queryExecuted) {
		$bindings = $queryExecuted->bindings;

		foreach($bindings as &$binding) {
			if($binding instanceof \DateTime) {
				$binding = $binding->format('"Y-m-d H:i:s"');
			} elseif(is_string($binding)) {
				$binding = '"'.$binding.'"';
			}
		}

		$query = '['.$queryExecuted->time.']: '.
			vsprintf(
				str_replace(
					[ '%', '?' ],
					[ '%%', '%s' ],
					$queryExecuted->sql
				),
				$bindings
			);

		$queryLog = $this->options()->get('Store.queryLog', false);

		if($queryLog === true) {
			echo $query.PHP_EOL;
		} elseif($queryLog != false) {
			file_put_contents($queryLog, $query.PHP_EOL, FILE_APPEND);
		}
	}

	/**
	 * Get the database manager
	 *
	 * @return \Illuminate\Database\Capsule\Manager  Database manager
	 */
	public function getManager() {
		return $this->manager;
	}

	/**
	 * Add value to array if index is missing
	 *
	 * @param string  $index   Index in array to check and add if missing
	 * @param array   &$array  Array to check and add to if index is missing
	 * @param string  $value   Value to add to array if index is missing
	 */
	protected function addIfMissing($index, array &$array, $value = '') {
		if(!array_key_exists($index, $array)) {
			$array[$index] = $value;
		}
	}

	/**
	 * Find a record with matching fields
	 *
	 * @param  string  $modelClass   Class name for model
	 * @param  array   $data         Field data to match against
	 * @param  array   $matchFields  Record fields to compare
	 *
	 * @return \Illuminate\Database\Eloquent\Model|null  Found record or null on fail
	 */
	protected function findRecord($modelClass, array $data, array $matchFields) {
		$match = [];

		foreach($matchFields as $field) {
			if(array_key_exists($field, $data)) {
				$match[$field] = $data[$field];
			}
		}

		return $modelClass::where($match)->first();
	}

	/**
	 * Insert record into table with provided data
	 *
	 * @param  string  $table  Name of table to insert record
	 * @param  array   $data   Fields data to set
	 *
	 * @return \Illuminate\Database\Eloquent\Model  Record inserted
	 */
	protected function insertRecord($table, array $data) {
		$modelClass = $this->options()->resolveAlias($table);

		$record = new $modelClass();

		$this->updateRecord($record, $data);

		return $record;
	}

	/**
	 * Update record with provided data
	 *
	 * @param  \Illuminate\Database\Eloquent\Model  $record  Record to update
	 * @param  array                                $data    Field data to set
	 */
	protected function updateRecord($record, array $data) {
		foreach($data as $field => $value) {
			$record->$field = $value;
		}

		$record->save();
	}

	/**
	 * Insert or update record with provided data matching on provided fields
	 *
	 * @param  string  $table        Name of table to insert record
	 * @param  array   $data         Field data to compare and set
	 * @param  array   $matchFields  Record fields to compare
	 *
	 * @return \Illuminate\Database\Eloquent\Model  Record inserted or updated
	 */
	protected function insertUpdateRecord($table, array $data, array $matchFields) {
		$modelClass = $this->options()->resolveAlias($table);

		$record = $this->findRecord($modelClass, $data, $matchFields);

		if($record === null) {
			$record = $this->insertRecord($table, $data);
		} else {
			$this->updateRecord($record, $data);
		}

		return $record;
	}

	/**
	 * Find the current count of records in specified table
	 *
	 * @param  string  $table  Name of table to find record count
	 *
	 * @return integer  Current count of records
	 */
	public function recordCount($table) {
		$modelClass = $this->options()->resolveAlias($table);

		return $modelClass::count();
	}

	/**
	 * Store data in Address table
	 *
	 * @param  array  $data  to store in table
	 *
	 * @return integer  Id of record from table
	 */
	public function storeAddress(array $data) {
		$record = $this->insertUpdateRecord('address', $data, [
			'line1',
			'line2',
			'city',
			'state',
			'zip',
			'plus_four',
			'country',
		]);

		return $record->id;
	}

	/**
	 * Store data in Billing table
	 *
	 * @param  array  $data  to store in table
	 *
	 * @return integer  Id of record from table
	 */
	public function storeBilling(array $data) {
		$record = $this->insertUpdateRecord('billing', $data, [
			'provider_id',
			'payer_id',
			'pid',
			'encounter',
			'code'
		]);

		return $record->id;
	}

	/**
	 * Store data in Facility table
	 *
	 * @param  array  $data  to store in table
	 *
	 * @return integer  Id of record from table
	 */
	public function storeFacility(array $data) {
		$record = $this->insertUpdateRecord('facility', $data, [
			'name',
			'street',
			'city',
			'state',
			'postal_code',
			'country_code',
			'federal_ein',
			'domain_identifier',
		]);

		return $record->id;
	}

	/**
	 * Store data in FormEncounter table
	 *
	 * @param  array  $data  to store in table
	 *
	 * @return integer  Id of record from table
	 */
	public function storeFormEncounter(array $data) {
		$this->addIfMissing('reason', $data, '');
		$this->addIfMissing('facility', $data, '');
		$this->addIfMissing('billing_note', $data, '');

		$record = $this->insertUpdateRecord('formEncounter', $data, [
			'facility_id',
			'provider_id',
			'pid',
			'encounter',
			'facility'
		]);

		return $record->id;
	}

	/**
	 * Store data in Form table
	 *
	 * @param  array  $data  to store in table
	 *
	 * @return integer  Id of record from table
	 */
	public function storeForm(array $data) {
		$this->addIfMissing('form_name', $data, '');
		$this->addIfMissing('formdir', $data, '');

		$record = $this->insertUpdateRecord('form', $data, [
			'form_id',
			'user',
			'pid',
			'encounter'
		]);

		return $record->id;
	}

	/**
	 * Store data in Group table
	 *
	 * @param  array  $data  to store in table
	 *
	 * @return integer  Id of record from table
	 */
	public function storeGroup(array $data) {
		$this->addIfMissing('name', $data, '');

		$record = $this->insertUpdateRecord('group', $data, [
			'user',
		]);

		return $record->id;
	}

	/**
	 * Store data in InsuranceCompany table
	 *
	 * @param  array  $data  to store in table
	 *
	 * @return integer  Id of record from table
	 */
	public function storeInsuranceCompany(array $data) {
		$record = $this->insertUpdateRecord('insuranceCompany', $data, [
			'name',
			'cms_id',
		]);

		return $record->id;
	}

	/**
	 * Store data in InsuranceData table
	 *
	 * @param  array  $data  to store in table
	 *
	 * @return integer  Id of record from table
	 */
	public function storeInsuranceData(array $data) {
		$record = $this->insertUpdateRecord('insuranceData', $data, [
			'pid',
			'type',
			'plan_name',
			'policy_number',
			'group_number',
		]);

		return $record->id;
	}

	/**
	 * Store data in PatientData table
	 *
	 * @param  array  $data  to store in table
	 *
	 * @return integer  Id of record from table
	 */
	public function storePatientData(array $data) {
		$this->addIfMissing('mname', $data, '');

		$record = $this->insertUpdateRecord('patientData', $data, [
			'fname',
			'lname',
			'mname',
			'DOB',
			'sex',
		]);

		if($record->pubpid == '' || $record->pid == '') {
			$record->pubpid = $record->id;
			$record->pid = $record->id;

			$record->save();
		}

		return $record->id;
	}

	/**
	 * Store data in PhoneNumber table
	 *
	 * @param  array  $data  to store in table
	 *
	 * @return integer  Id of record from table
	 */
	public function storePhoneNumber(array $data) {
		$record = $this->insertUpdateRecord('phoneNumber', $data, [
			'country_code',
			'area_code',
			'prefix',
			'number',
			'type',
			'foreign_id',
		]);

		return $record->id;
	}

	/**
	 * Store data in User table
	 *
	 * @param  array  $data  to store in table
	 *
	 * @return integer  Id of record from table
	 */
	public function storeUser(array $data) {
		$this->addIfMissing('password', $data, '');

		$record = $this->insertUpdateRecord('user', $data, [
			'username',
			'federaltaxid'
		]);

		return $record->id;
	}

	/**
	 * Store data in X12Partner table
	 *
	 * @param  array  $data  to store in table
	 *
	 * @return integer  Id of record from table
	 */
	public function storeX12Partner(array $data) {
		$record = $this->insertUpdateRecord('x12Partners', $data, [
			'x12_gs03'
		]);

		return $record->id;
	}

}