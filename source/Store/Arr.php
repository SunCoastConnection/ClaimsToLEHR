<?php

namespace SunCoastConnection\ClaimsToOEMR\Store;

use \SunCoastConnection\ClaimsToOEMR\Document\Options,
	\SunCoastConnection\ClaimsToOEMR\Store;

class Arr extends Store {

	protected $manager;

	static protected $fields = [
		'address' => [
			'id' => 0,
			'line1' => null,
			'line2' => null,
			'city' => null,
			'state' => null,
			'zip' => null,
			'plus_four' => null,
			'country' => null,
			'foreign_id' => null,
		],
		'billing' => [
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
		],
		'facility' => [
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
		],
		'formEncounter' => [
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
		],
		'form' => [
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
		],
		'group' => [
			'id' => null,
			'name' => '',
			'user' => '',
		],
		'insuranceCompany' => [
			'id' => 0,
			'name' => null,
			'attn' => null,
			'cms_id' => null,
			'x12_receiver_id' => null,
			'x12_default_partner_id' => null,
		],
		'insuranceData' => [
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
		],
		'patientData' => [
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
		],
		'phoneNumber' => [
			'id' => 0,
			'country_code' => null,
			'area_code' => null,
			'prefix' => null,
			'number' => null,
			'type' => null,
			'foreign_id' => null,
		],
		'user' => [
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
		],
		'x12Partners' => [
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
		],
	];

	protected $tables = [];

	protected function onConstruct() {
		foreach($this::$tableNames as $tableName) {
			$this->tables[$tableName] = [];
		}
	}

	public function recordCount($table) {
		if(array_key_exists($table, $this->tables)) {
			return count($this->tables[$table]);
		}
	}

	protected function findRecord($table, $data, $matchFields) {
		foreach($this->tables[$table] as $recordId => $record) {
			$match = true;

			foreach($matchFields as $field) {
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

	protected function insertRecord($table, $data) {
		$id = count($this->tables[$table]);

		$this->updateRecord($table, $id, $data);

		return $id;
	}

	protected function updateRecord($table, $id, $data) {
		$this->tables[$table][$id] = array_merge($this::$fields[$table], $data);
	}

	protected function insertUpdateRecord($table, $data, $matchFields) {
		$id = $this->findRecord($table, $data, $matchFields);

		if($id === null) {
			$id = $this->insertRecord($table, $data);
		} else {
			$this->updateRecord($table, $id, $data);
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
			'x12_gs03',
		]);
	}

}