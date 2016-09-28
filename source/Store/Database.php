<?php

namespace SunCoastConnection\ClaimsToOEMR\Store;

use \Illuminate\Container\Container,
	\Illuminate\Database\Capsule\Manager,
	\Illuminate\Events\Dispatcher,
	\SunCoastConnection\ClaimsToOEMR\Document\Options,
	\SunCoastConnection\ClaimsToOEMR\Store;

class Database extends Store {

	protected $manager;

	protected function onConstruct() {
		$this->manager = new Manager;

		$options = $this->options();

		$this->manager->addConnection(
			$options->get(
				'Store.connections.'.
				$options->get('Store.default')
			)
		);

		// $this->manager->setEventDispatcher(new Dispatcher(new Container));

		$this->manager->setAsGlobal();

		$this->manager->bootEloquent();
	}

	protected function getManager() {
		return $this->manager;
	}

	protected function getModelClass($model) {
		return $this->options()->get('Aliases.'.$model);
	}

	public function recordCount($table) {
		$modelClass = $this->getModelClass($table);

		return $modelClass::count();
	}

	protected function findRecord($modelClass, $data, $matchFields) {
		$match = [];

		foreach($matchFields as $field) {
			$match[$field] = $data[$field];
		}

		return $modelClass::where($match)->first();
	}

	protected function insertRecord($table, $data) {
		$modelClass = $this->getModelClass($table);

		$record = new $modelClass();

		return $this->updateRecord($record, $data);
	}

	protected function updateRecord($record, $data) {
		foreach($data as $field => $value) {
			$record->$field = $value;
		}

		$record->save();

		return $record->id;
	}

	protected function insertUpdateRecord($table, $data, $matchFields) {
		$modelClass = $this->getModelClass($table);

		$record = $this->findRecord($modelClass, $data, $matchFields);

		if(is_null($record)) {
			$id = $this->insertRecord($table, $data);
		} else {
			$id = $this->updateRecord($record, $data);
		}

		return $id;
	}

	public function storeAddress(array $data) {
		return $this->insertUpdateRecord('address', $data, [
			'line1',
			'line2',
			'city',
			'state',
			'zip',
			'plus_four',
			'country',
		]);
	}

	public function storeBilling(array $data) {
		return $this->insertRecord('billing', $data);
	}

	public function storeFacility(array $data) {
		return $this->insertUpdateRecord('facility', $data, [
			'name',
			'street',
			'city',
			'state',
			'postal_code',
			'country_code',
			'federal_ein',
			'domain_identifier',
		]);
	}

	public function storeFormEncounter(array $data) {
		return $this->insertRecord('formEncounter', $data);
	}

	public function storeForm(array $data) {
		return $this->insertRecord('form', $data);
	}

	public function storeGroup(array $data) {
		return $this->insertUpdateRecord('group', $data, [
			'user',
		]);
	}

	public function storeInsuranceCompany(array $data) {
		return $this->insertUpdateRecord('insuranceCompany', $data, [
			'name',
			'cms_id',
		]);
	}

	public function storeInsuranceData(array $data) {
		return $this->insertUpdateRecord('insuranceData', $data, [
			'pid',
			'type',
			'plan_name',
			'policy_number',
			'group_number',
		]);
	}

	public function storePatientData(array $data) {
		return $this->insertUpdateRecord('patientData', $data, [
			'fname',
			'lname',
			'mname',
			'DOB',
			'sex',
		]);
	}

	public function storePhoneNumber(array $data) {
		return $this->insertRecord('phoneNumber', $data);
	}

	public function storeUser(array $data) {
		return $this->insertUpdateRecord('user', $data, [
			'username',
			'federaltaxid'
		]);
	}

	public function storeX12Partner(array $data) {
		return $this->insertUpdateRecord('x12Partners', $data, [
			'x12_gs03'
		]);
	}

}