<?php

namespace SunCoastConnection\ClaimsToOEMR\Store;

use \Illuminate\Container\Container,
	\Illuminate\Database\Capsule\Manager,
	\Illuminate\Events\Dispatcher,
	\SunCoastConnection\ClaimsToOEMR\Document\Options,
	\SunCoastConnection\ClaimsToOEMR\Store;

class Database extends Store {

	protected $manager;

	public function __construct(Options $options) {
		$this->manager = new Manager;

		$this->manager->addConnection($options->get('Store'));

		// $this->manager->setEventDispatcher(new Dispatcher(new Container));

		$this->manager->setAsGlobal();

		$this->manager->bootEloquent();

		parent::__construct($options);
	}

	protected function getManager() {
		return $this->manager;
	}

	public function recordCount($tables = null) {


	}

	// public function findRecord(&$table, $data, $fields) {
	//
	//
	// }

	public function storeAddress(array $data) {


	}

	public function storeBilling(array $data) {


	}

	public function storeFacility(array $data) {


	}

	public function storeFormEncounter(array $data) {


	}

	public function storeForm(array $data) {


	}

	public function storeGroup(array $data) {


	}

	public function storeInsuranceCompany(array $data) {


	}

	public function storeInsuranceData(array $data) {


	}

	public function storePatientData(array $data) {


	}

	public function storePhoneNumber(array $data) {


	}

	public function storeUser(array $data) {


	}

	public function storeX12Partner(array $data) {


	}

}