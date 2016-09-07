<?php

namespace SunCoastConnection\ClaimsToOEMR;

use \SunCoastConnection\ClaimsToOEMR\Document\Options;

abstract class Store {

	static public function getInstance(Options $options) {
		return new static($options);
	}

	public function __construct(Options $options) {
		$this->options($options);
	}

	protected function options(Options $setOptions = null) {
		static $options = null;

		if(is_null($options) && !is_null($setOptions)) {
			$options = $setOptions;
		}

		return $options;
	}

	public function printRecordCount($tables = null) {
		$tables = $this->recordCount($tables);

		$maxTabs = ceil((max(array_map('strlen', array_keys($tables))) + 2) / 8);

		foreach($tables as $table => $recordCount) {
			$neededTabs = $maxTabs - floor((strlen($table) + 2) / 8);
			echo $table.': '.str_repeat("\t", $neededTabs).$recordCount.PHP_EOL;
		}
	}

	abstract public function recordCount($tables = null);

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