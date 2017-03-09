<?php

namespace SunCoastConnection\ClaimsToEMR;

use \SunCoastConnection\ParseX12\Options;

abstract class Store {

	/**
	 * Names of tables
	 * @var array
	 */
	static protected $tableNames = [
		'address',
		'billing',
		'facility',
		'formEncounter',
		'form',
		'group',
		'insuranceCompany',
		'insuranceData',
		'patientData',
		'phoneNumber',
		'user',
		'x12Partners'
	];

	/**
	 * Get instance of store class with provided options
	 *
	 * @param  \SunCoastConnection\ParseX12\Options  $options  Options to create store object with
	 *
	 * @return \SunCoastConnection\ClaimsToEMR\Store  Store object
	 */
	public static function getInstance(Options $options) {
		return new static($options);
	}

	/**
	 * Create a new Store
	 *
	 * @param \SunCoastConnection\ParseX12\Options  $options  Options to create store object with
	 */
	public function __construct(Options $options) {
		$this->options($options);

		if(method_exists($this, 'onConstruct')) {
			$this->onConstruct();
		}
	}

	/**
	 * Set store options or retrieve store options
	 *
	 * @param  \SunCoastConnection\ParseX12\Options|null  $setOptions  Options to set store object with
	 *
	 * @return \SunCoastConnection\ParseX12\Options|null  Store options or null when not set
	 */
	protected function options(Options $setOptions = null) {
		static $options = null;

		if(is_null($options) && !is_null($setOptions)) {
			$options = $setOptions;
		}

		return $options;
	}

	/**
	 * Output record counts for provided tables names
	 *
	 * @param  array|string|null  $tables  Name of table, array of tables, or null for all tables
	 */
	public function printTableCounts($tables = null) {
		$tables = $this->tableCounts($tables);

		$maxTabs = ceil((max(array_map('strlen', array_keys($tables))) + 2) / 8);

		foreach($tables as $table => $recordCount) {
			$neededTabs = $maxTabs - floor((strlen($table) + 2) / 8);
			echo $table.': '.str_repeat("\t", $neededTabs).$recordCount.PHP_EOL;
		}
	}

	/**
	 * Get record counts for provided table names
	 *
	 * @param  array|string|null  $tables  Name of table, array of tables, or null for all tables
	 *
	 * @return array  Array of table counts keyed by table name
	 */
	public function tableCounts($tables = null) {
		if(is_string($tables)) {
			$tables = [ $tables ];
		} elseif(!is_array($tables)) {
			$tables = self::$tableNames;
		}

		$output = [];

		foreach($tables as $table) {
			$count = $this->recordCount($table);

			if($count !== null) {
				$output[$table] = $count;
			}
		}

		return $output;
	}

	/**
	 * Find the current count of records in specified table
	 *
	 * @param  string  $table  Name of table to find record count
	 *
	 * @return integer  Current count of records
	 */
	abstract public function recordCount($tables);

	/**
	 * Store data in Address table
	 *
	 * @param  array  $data  to store in table
	 *
	 * @return integer  Id of record from table
	 */
	abstract public function storeAddress(array $data);

	/**
	 * Store data in Billing table
	 *
	 * @param  array  $data  to store in table
	 *
	 * @return integer  Id of record from table
	 */
	abstract public function storeBilling(array $data);

	/**
	 * Store data in Facility table
	 *
	 * @param  array  $data  to store in table
	 *
	 * @return integer  Id of record from table
	 */
	abstract public function storeFacility(array $data);

	/**
	 * Store data in FormEncounter table
	 *
	 * @param  array  $data  to store in table
	 *
	 * @return integer  Id of record from table
	 */
	abstract public function storeFormEncounter(array $data);

	/**
	 * Store data in Form table
	 *
	 * @param  array  $data  to store in table
	 *
	 * @return integer  Id of record from table
	 */
	abstract public function storeForm(array $data);

	/**
	 * Store data in Group table
	 *
	 * @param  array  $data  to store in table
	 *
	 * @return integer  Id of record from table
	 */
	abstract public function storeGroup(array $data);

	/**
	 * Store data in InsuranceCompany table
	 *
	 * @param  array  $data  to store in table
	 *
	 * @return integer  Id of record from table
	 */
	abstract public function storeInsuranceCompany(array $data);

	/**
	 * Store data in InsuranceData table
	 *
	 * @param  array  $data  to store in table
	 *
	 * @return integer  Id of record from table
	 */
	abstract public function storeInsuranceData(array $data);

	/**
	 * Store data in PatientData table
	 *
	 * @param  array  $data  to store in table
	 *
	 * @return integer  Id of record from table
	 */
	abstract public function storePatientData(array $data);

	/**
	 * Store data in PhoneNumber table
	 *
	 * @param  array  $data  to store in table
	 *
	 * @return integer  Id of record from table
	 */
	abstract public function storePhoneNumber(array $data);

	/**
	 * Store data in User table
	 *
	 * @param  array  $data  to store in table
	 *
	 * @return integer  Id of record from table
	 */
	abstract public function storeUser(array $data);

	/**
	 * Store data in X12Partner table
	 *
	 * @param  array  $data  to store in table
	 *
	 * @return integer  Id of record from table
	 */
	abstract public function storeX12Partner(array $data);

}