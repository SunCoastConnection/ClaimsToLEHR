<?php

namespace SunCoastConnection\ClaimsToOEMR;

use \SunCoastConnection\ClaimsToOEMR\Document\Options;

abstract class Store {

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

	static public function getInstance(Options $options) {
		return new static($options);
	}

	public function __construct(Options $options) {
		$this->options($options);

		if(method_exists($this, 'onConstruct')) {
			$this->onConstruct();
		}
	}

	protected function options(Options $setOptions = null) {
		static $options = null;

		if(is_null($options) && !is_null($setOptions)) {
			$options = $setOptions;
		}

		return $options;
	}

	public function printTableCounts($tables = null) {
		$tables = $this->tableCounts($tables);

		$maxTabs = ceil((max(array_map('strlen', array_keys($tables))) + 2) / 8);

		foreach($tables as $table => $recordCount) {
			$neededTabs = $maxTabs - floor((strlen($table) + 2) / 8);
			echo $table.': '.str_repeat("\t", $neededTabs).$recordCount.PHP_EOL;
		}
	}

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

	abstract public function recordCount($tables);

	abstract public function storeAddress(array $data);

	abstract public function storeBilling(array $data);

	abstract public function storeFacility(array $data);

	abstract public function storeFormEncounter(array $data);

	abstract public function storeForm(array $data);

	abstract public function storeGroup(array $data);

	abstract public function storeInsuranceCompany(array $data);

	abstract public function storeInsuranceData(array $data);

	abstract public function storePatientData(array $data);

	abstract public function storePhoneNumber(array $data);

	abstract public function storeUser(array $data);

	abstract public function storeX12Partner(array $data);

}