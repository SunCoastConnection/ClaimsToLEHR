<?php

namespace SunCoastConnection\ClaimsToOEMR;

use \Illuminate\Container\Container,
	\Illuminate\Database\Capsule\Manager,
	\Illuminate\Events\Dispatcher,
	\SunCoastConnection\ClaimsToOEMR\Document\Options;

class Database {

	protected $manager;

	static protected $addressFields = [
		'id' => 0,
		'line1' => null,
		'line2' => null,
		'city' => null,
		'state' => null,
		'zip' => null,
		'plus_four' => null,
		'country' => null,
		'foreign_id' => null,
	];

	static protected $billingFields = [
		'id' => null,
		'date' => null,
		'code_type' => null,
		'code' => null,
		'pid' => null,
		'provider_id' => null,
		'user' => null,
		'groupname' => null,
		'authorized' => null,
		'encounter' => null,
		'code_text' => null,
		'billed' => null,
		'activity' => null,
		'payer_id' => null,
		'bill_process' => 0,
		'bill_date' => null,
		'process_date' => null,
		'process_file' => null,
		'modifier' => null,
		'units' => null,
		'fee' => null,
		'justify' => null,
	];

	static protected $facilityFields = [
		'id' => null,
		'name' => null,
		'street' => null,
		'city' => null,
		'state' => null,
		'postal_code' => null,
		'country_code' => null,
		'federal_ein' => null,
		'service_location' => 1,
		'billing_location' => 0,
		'accepts_assignment' => 0,
		'pos_code' => null,
		'attn' => null,
		'domain_identifier' => null,
		'tax_id_type' => '',
		'color' => '',
		'primary_business_entity' => 0,
	];

	static protected $formEncounterFields = [
		'id' => null,
		'date' => null,
		'reason' => '',
		'facility' => '',
		'facility_id' => 0,
		'pid' => null,
		'encounter' => null,
		'onset_date' => null,
		'sensitivity' => null,
		'billing_note' => '',
		'pc_catid' => 5,
		'last_level_billed' => 0,
		'last_level_closed' => 0,
		'last_stmt_date' => null,
		'stmt_count' => 0,
		'provider_id' => 0,
		'supervisor_id' => 0,
		'invoice_refno' => '',
		'referral_source' => '',
		'billing_facility' => 0,
	];

	static protected $formFields = [
		'id' => null,
		'date' => null,
		'encounter' => null,
		'form_name' => '',
		'form_id' => null,
		'pid' => null,
		'user' => null,
		'groupname' => null,
		'authorized' => null,
		'deleted' => '0',
		'formdir' => '',
	];

	static protected $groupFields = [
		'id' => null,
		'name' => '',
		'user' => '',
	];

	static protected $insuranceCompanyFields = [
		'id' => 0,
		'name' => null,
		'attn' => null,
		'cms_id' => null,
		'x12_receiver_id' => null,
		'x12_default_partner_id' => null,
	];

	static protected $insuranceDataFields = [
		'id' => null,
		'type' => null,
		'provider' => null,
		'plan_name' => null,
		'policy_number' => null,
		'group_number' => null,
		'subscriber_lname' => null,
		'subscriber_mname' => null,
		'subscriber_fname' => null,
		'subscriber_relationship' => null,
		'subscriber_DOB' => null,
		'subscriber_street' => null,
		'subscriber_postal_code' => null,
		'subscriber_city' => null,
		'subscriber_state' => null,
		'subscriber_country' => null,
		'date' => '0000-00-00',
		'pid' => '0',
		'subscriber_sex' => null,
		'accept_assignment' => 'TRUE',
	];

	static protected $patientDataFields = [
		'id' => null,
		'language' => '',
		'fname' => '',
		'lname' => '',
		'mname' => '',
		'DOB' => null,
		'street' => '',
		'postal_code' => '',
		'city' => '',
		'state' => '',
		'date' => null,
		'sex' => '',
		'providerID' => null,
		'pubpid' => '',
		'pid' => '0',
	];

	static protected $phoneNumberFields = [
		'id' => 0,
		'country_code' => null,
		'area_code' => null,
		'prefix' => null,
		'number' => null,
		'type' => null,
		'foreign_id' => null,
	];

	static protected $userFields = [
		'id' => null,
		'username' => null,
		'password' => '',
		'authorized' => null,
		'fname' => null,
		'mname' => null,
		'lname' => null,
		'federaltaxid' => null,
		'federaldrugid' => null,
		'facility' => null,
		'facility_id' => 0,
		'see_auth' => 1,
		'active' => 1,
		'npi' => null,
		'cal_ui' => 1,
		'taxonomy' => '207Q00000X',
		'calendar' => 0,
		'abook_type' => '',
		'state_license_number' => null,
	];

	static protected $x12PartnersFields = [
		'id' => 0,
		'name' => null,
		'id_number' => null,
		'x12_sender_id' => null,
		'x12_receiver_id' => null,
		'x12_version' => null,
		'x12_isa01' => '00',
		'x12_isa02' => '          ',
		'x12_isa03' => '00',
		'x12_isa04' => '          ',
		'x12_isa05' => 'ZZ',
		'x12_isa07' => 'ZZ',
		'x12_isa14' => '0',
		'x12_isa15' => 'P',
		'x12_gs02' => '',
		'x12_gs03' => '',
	];

	static public function getNew(Options $options) {
		return new static($options);
	}

	public function __construct(Options $options) {
		$this->manager = new Manager;

		$this->manager->addConnection($options->get('Database'));
		// $this->manager->setEventDispatcher(new Dispatcher(new Container));
		$this->manager->setAsGlobal();

		$this->manager->bootEloquent();
	}

	public function getManager() {
		return $this->manager;
	}

	protected $address = [];
	protected $billing = [];
	protected $facility = [];
	protected $formEncounter = [];
	protected $form = [];
	protected $group = [];
	protected $insuranceCompany = [];
	protected $insuranceData = [];
	protected $patientData = [];
	protected $phoneNumber = [];
	protected $user = [];
	protected $x12Partners = [];

	public function recordCount($tables = null) {
		if(is_string($tables)) {
			$tables = [ $tables ];
		} elseif(!is_array($tables)) {
			$tables = [
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
				'x12Partners',
			];
		}

		$output = [];

		foreach($tables as $table) {
			if(property_exists($this, $table) &&
				is_array($this->$table)
			) {
				$output[$table] = count($this->$table);
			}
		}

		return $output;
	}

	public function printRecordCount($tables = null) {
		$tables = $this->recordCount($tables);

		$maxTabs = ceil((max(array_map('strlen', array_keys($tables))) + 2) / 8);

		foreach($tables as $table => $recordCount) {
			$neededTabs = $maxTabs - floor((strlen($table) + 2) / 8);
			echo $table.': '.str_repeat("\t", $neededTabs).$recordCount.PHP_EOL;
		}
	}

	public function findRecord(&$table, $data, $fields) {
		foreach($table as $recordId => $record) {
			$match = true;

			foreach($fields as $field) {
				if($record[$field] != $data[$field]) {
					$match = false;
					break;
				}
			}

			if($match) {
				return $recordId;
			}
		}
	}

	public function storeAddress(array $data) {
		echo " - Function:\t\t".__FUNCTION__.PHP_EOL;

		$id = $this->findRecord($this->address, $data, [
			'line1',
			'line2',
			'city',
			'state',
			'zip',
			'plus_four',
			'country',
		]);

		if(is_null($id)) {
			$this->address[] = array_merge($this::$addressFields, $data);

			$id = count($this->address) - 1;
		}

		return $id;
	}

	public function storeBilling(array $data) {
		$this->billing[] = array_merge($this::$billingFields, $data);

		return count($this->billing) - 1;
	}

	public function storeFacility(array $data) {
		echo " - Function:\t\t".__FUNCTION__.PHP_EOL;

		$id = $this->findRecord($this->facility, $data, [
			'name',
			'street',
			'city',
			'state',
			'postal_code',
			'country_code',
			'federal_ein',
			'domain_identifier',
		]);

		if(is_null($id)) {
			$this->facility[] = array_merge($this::$facilityFields, $data);

			$id = count($this->facility) - 1;
		}

		return $id;
	}

	public function storeFormEncounter(array $data) {
		echo " - Function:\t\t".__FUNCTION__.PHP_EOL;

		$this->formEncounter[] = array_merge($this::$formEncounterFields, $data);

		return count($this->formEncounter) - 1;
	}

	public function storeForm(array $data) {
		echo " - Function:\t\t".__FUNCTION__.PHP_EOL;

		$this->form[] = array_merge($this::$formFields, $data);

		return count($this->form) - 1;
	}

	public function storeGroup(array $data) {
		echo " - Function:\t\t".__FUNCTION__.PHP_EOL;

		$id = $this->findRecord($this->group, $data, [
			'user',
		]);

		if(is_null($id)) {
			$this->group[] = array_merge($this::$groupFields, $data);

			$id = count($this->group) - 1;
		}

		return $id;
	}

	public function storeInsuranceCompany(array $data) {
		echo " - Function:\t\t".__FUNCTION__.PHP_EOL;

		$id = $this->findRecord($this->insuranceCompany, $data, [
			'name',
			'cms_id',
		]);

		if(is_null($id)) {
			$this->insuranceCompany[] = array_merge($this::$insuranceCompanyFields, $data);

			$id = count($this->insuranceCompany) - 1;
		}

		return $id;
	}

	public function storeInsuranceData(array $data) {
		echo " - Function:\t\t".__FUNCTION__.PHP_EOL;

		$id = $this->findRecord($this->insuranceData, $data, [
			'pid',
			'type',
			'plan_name',
			'policy_number',
			'group_number',
		]);

		if(is_null($id)) {
			$this->insuranceData[] = array_merge($this::$insuranceDataFields, $data);

			$id = count($this->insuranceData) - 1;
		}

		return $id;
	}

	public function storePatientData(array $data) {
		echo " - Function:\t\t".__FUNCTION__.PHP_EOL;

		$id = $this->findRecord($this->patientData, $data, [
			'fname',
			'lname',
			'mname',
			'DOB',
			'sex',
		]);

		if(is_null($id)) {
			$this->patientData[] = array_merge($this::$patientDataFields, $data);

			$id = count($this->patientData) - 1;
		}

		return $id;
	}

	public function storePhoneNumber(array $data) {
		echo " - Function:\t\t".__FUNCTION__.PHP_EOL;

		$this->phoneNumber[] = array_merge($this::$phoneNumberFields, $data);

		return count($this->phoneNumber) - 1;
	}

	public function storeUser(array $data) {
		echo " - Function:\t\t".__FUNCTION__.PHP_EOL;

		$id = $this->findRecord($this->user, $data, [
			'username',
			'federaltaxid'
		]);

		if(is_null($id)) {
			$this->user[] = array_merge($this::$userFields, $data);

			$id = count($this->user) - 1;
		}

		return $id;
	}

	public function storeX12Partner(array $data) {
		// echo " - Function:\t\t".__FUNCTION__.PHP_EOL;

		$id = $this->findRecord($this->x12Partners, $data, [
			'x12_gs03',
		]);

		if(is_null($id)) {
			$this->x12Partners[] = array_merge($this::$x12PartnersFields, $data);

			$id = count($this->x12Partners) - 1;
		}

		return $id;
	}

}