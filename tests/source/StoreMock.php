<?php

namespace SunCoastConnection\ClaimsToEMR\Tests;

use \SunCoastConnection\ClaimsToEMR\Store;

class StoreMock extends Store {

	protected function onConstruct() {}

	public function recordCount($tables) {}

	public function storeAddress(array $data) {}

	public function storeBilling(array $data) {}

	public function storeFacility(array $data) {}

	public function storeFormEncounter(array $data) {}

	public function storeForm(array $data) {}

	public function storeGroup(array $data) {}

	public function storeInsuranceCompany(array $data) {}

	public function storeInsuranceData(array $data) {}

	public function storePatientData(array $data) {}

	public function storePhoneNumber(array $data) {}

	public function storeUser(array $data) {}

	public function storeX12Partner(array $data) {}

}