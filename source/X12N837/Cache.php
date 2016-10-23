<?php

namespace SunCoastConnection\ClaimsToOEMR\X12N837;

use \SunCoastConnection\ClaimsToOEMR\Store;
use \SunCoastConnection\ClaimsToOEMR\X12N837;
use \SunCoastConnection\ClaimsToOEMR\X12N837\Envelope;
use \SunCoastConnection\ClaimsToOEMR\X12N837\Loop;
use \SunCoastConnection\ClaimsToOEMR\X12N837\Segment;

class Cache {

	protected $store;

	/**
	 * Get instance of cache class with provided Store
	 *
	 * @param  \SunCoastConnection\ClaimsToOEMR\Store  $store  Store to create cache object with
	 *
	 * @return \SunCoastConnection\ClaimsToOEMR\X12N837\Cache  Cache object
	 */
	static public function getInstance(Store $store) {
		return new static($store);
	}

	/**
	 * Create a new Cache
	 *
	 * @param  \SunCoastConnection\ClaimsToOEMR\Store  $store  Store to create cache object with
	 */
	public function __construct(Store $store) {
		$this->store($store);
	}

	/**
	 * Set cache store or retrieve cache store
	 *
	 * @param  \SunCoastConnection\ClaimsToOEMR\Store|null  $setStore  Store to set cache object with
	 *
	 * @return \SunCoastConnection\ClaimsToOEMR\Store|null  Cache store or null when not set
	 */
	protected function store(Store $setStore = null) {
		static $store = null;

		if(is_null($store) && !is_null($setStore)) {
			$store = $setStore;
		}

		return $store;
	}

	/**
	 * Store data in Address table
	 *
	 * @param  array  $data  to store in table
	 *
	 * @return integer|null  Id of record from table or null if no data provided
	 */
	protected function storeAddress(array $data) {
		$address = [];

		if(array_key_exists('InsuranceCompany', $data)) {
			$address['foreign_id'] = $data['InsuranceCompany'];
		}

		if(array_key_exists('N3', $data)) {
			$address['line1'] = (string) $data['N3']->element('N301');
			$address['line2'] = (string) $data['N3']->element('N302');
		}

		if(array_key_exists('N4', $data)) {
			$address['city'] = (string) $data['N4']->element('N401');
			$address['state'] = (string) $data['N4']->element('N402');
			$address['zip'] = substr((string) $data['N4']->element('N403'), 0, 5);

			if(strlen((string) $data['N4']->element('N403')) > 5) {
				$address['plus_four'] = substr((string) $data['N4']->element('N403'), 5);
			}
		}

		if(count($address)) {
			return $this->store()->storeAddress($address);
		}
	}

	/**
	 * Store data in Billing table
	 *
	 * @param  array  $data  to store in table
	 */
	protected function storeBilling(array $data) {
		$billing = [];

		if(array_key_exists('User', $data)) {
			$billing['provider_id'] = $data['User'];
			$billing['user'] = $data['User'];
		}

		if(array_key_exists('InsuranceCompany', $data)) {
			$billing['payer_id'] = $data['InsuranceCompany'];
		}

		if(array_key_exists('Patient', $data)) {
			$billing['pid'] = $data['Patient'];
		}

		if(array_key_exists('CLM', $data)) {
			$billing['encounter'] = (string) $data['CLM']->element('CLM01');
		}

		if(array_key_exists('SV1', $data)) {
			if(array_key_exists('HI', $data)) {
				$billing['justify'] = (string) $data['HI'][0]->element('HI01')->subElement(1);
			}

			$billing['code'] = (string) $data['SV1']->element('SV101')->subElement(1);
			$billing['modifier'] = (string) $data['SV1']->element('SV101')->subElement(2);
			$billing['fee'] = (string) $data['SV1']->element('SV102');
			$billing['units'] = (string) $data['SV1']->element('SV104');

			$this->store()->storeBilling($billing);
		} elseif(array_key_exists('HI', $data)) {
			$elements = [
				'HI01', 'HI02', 'HI03',
				'HI04', 'HI05', 'HI06',
				'HI07', 'HI08', 'HI09',
				'HI10', 'HI11', 'HI12'
			];

			foreach($elements as $element) {
				if($data['HI']->elementExists($element) && $data['HI']->element($element)->subElementCount() > 1) {
					$billing['code_type'] = (string) $data['HI']->element($element)->subElement(0);
					$billing['code'] = (string) $data['HI']->element($element)->subElement(1);

					$this->store()->storeBilling($billing);
				}
			}
		}
	}

	/**
	 * Store data in Facility table
	 *
	 * @param  array  $data  to store in table
	 *
	 * @return integer|null  Id of record from table or null if no data provided
	 */
	protected function storeFacility(array $data) {
		$facility = [];

		if(array_key_exists('CLM', $data)) {
			$facility['pos_code'] = (string) $data['CLM']->element('CLM05')->subElement(0);
		}

		if(array_key_exists('NM1', $data)) {
			$facility['name'] = (string) $data['NM1']->element('NM103');
			$facility['domain_identifier'] = (string) $data['NM1']->element('NM109');
		}

		if(array_key_exists('N3', $data)) {
			$facility['street'] = (string) $data['N3']->element('N301').' '.$data['N3']->element('N302');
		}

		if(array_key_exists('N4', $data)) {
			$facility['city'] = (string) $data['N4']->element('N401');
			$facility['state'] = (string) $data['N4']->element('N402');
			$facility['postal_code'] = (string) $data['N4']->element('N403');
		}

		if(array_key_exists('REF', $data)) {
			$facility['federal_ein'] = (string) $data['REF']->element('REF02');
		}

		if(count($facility)) {
			return $this->store()->storeFacility($facility);
		}
	}

	/**
	 * Store data in FormEncounter table
	 *
	 * @param  array  $data  to store in table
	 *
	 * @return integer|null  Id of record from table or null if no data provided
	 */
	protected function storeFormEncounter(array $data) {
		$formEncounter = [];

		if(array_key_exists('Facility', $data)) {
			$formEncounter['facility_id'] = $data['Facility'];
			$formEncounter['billing_facility'] = $data['Facility'];
		}

		if(array_key_exists('User', $data)) {
			$formEncounter['provider_id'] = $data['User'];
		}

		if(array_key_exists('Patient', $data)) {
			$formEncounter['pid'] = $data['Patient'];
		}

		if(array_key_exists('CLM', $data)) {
			$formEncounter['encounter'] = (string) $data['CLM']->element('CLM01');
		}

		if(array_key_exists('NM1', $data) && $data['NM1']->elementEquals('NM101', '85')) {
			$formEncounter['facility'] = (string) $data['NM1']->element('NM103');
		}

		if(count($formEncounter)) {
			return $this->store()->storeFormEncounter($formEncounter);
		}
	}

	/**
	 * Store data in Form table
	 *
	 * @param  array  $data  to store in table
	 *
	 * @return integer|null  Id of record from table or null if no data provided
	 */
	protected function storeForm(array $data) {
		$form = [];

		if(array_key_exists('FormEncounter', $data)) {
			$form['form_id'] = $data['FormEncounter'];
		}

		if(array_key_exists('User', $data)) {
			$form['user'] = $data['User'];
		}

		if(array_key_exists('Patient', $data)) {
			$form['pid'] = $data['Patient'];
		}

		if(array_key_exists('CLM', $data)) {
			$form['encounter'] = (string) $data['CLM']->element('CLM01');
		}

		if(count($form)) {
			return $this->store()->storeForm($form);
		}
	}

	/**
	 * Store data in Group table
	 *
	 * @param  array  $data  to store in table
	 *
	 * @return integer|null  Id of record from table or null if no data provided
	 */
	protected function storeGroup(array $data) {
		$group = [];

		if(array_key_exists('NM1', $data)) {
			$group['user'] = (string) $data['NM1']->element('NM103').$data['NM1']->element('NM104');
		}

		if(count($group)) {
			return $this->store()->storeGroup($group);
		}
	}

	/**
	 * Store data in InsuranceCompany table
	 *
	 * @param  array  $data  to store in table
	 *
	 * @return integer|null  Id of record from table or null if no data provided
	 */
	protected function storeInsuranceCompany(array $data) {
		$insuranceCompany = [];

		if(array_key_exists('X12Partner', $data)) {
			$insuranceCompany['x12_receiver_id'] = $data['X12Partner'];
			$insuranceCompany['x12_default_partner_id'] = $data['X12Partner'];
		}

		if(array_key_exists('NM1', $data)) {
			$insuranceCompany['name'] = (string) $data['NM1']->element('NM103');
			$insuranceCompany['cms_id'] = (string) $data['NM1']->element('NM109');
		}

		if(count($insuranceCompany)) {
			return $this->store()->storeInsuranceCompany($insuranceCompany);
		}
	}

	/**
	 * Store data in InsuranceData table
	 *
	 * @param  array  $data  to store in table
	 *
	 * @return integer|null  Id of record from table or null if no data provided
	 */
	protected function storeInsuranceData(array $data) {
		$insuranceData = [];

		if(array_key_exists('PatientData', $data)) {
			$insuranceData['pid'] = $data['PatientData'];
		}

		if(array_key_exists('SBR', $data)) {
			$insuranceData['subscriber_relationship'] = (string) $data['SBR']->element('SBR02');
			$insuranceData['group_number'] = (string) $data['SBR']->element('SBR03');
			$insuranceData['plan_name'] = (string) $data['SBR']->element('SBR04');
		}

		if(array_key_exists('NM1', $data)) {
			$insuranceData['subscriber_lname'] = (string) $data['NM1']->element('NM103');
			$insuranceData['subscriber_fname'] = (string) $data['NM1']->element('NM104');
			$insuranceData['subscriber_mname'] = (string) $data['NM1']->element('NM105');
			$insuranceData['policy_number'] = (string) $data['NM1']->element('NM109');
		}

		if(array_key_exists('N3', $data)) {
			$insuranceData['subscriber_street'] = (string) $data['N3']->element('N301').' '.$data['N3']->element('N302');
		}

		if(array_key_exists('N4', $data)) {
			$insuranceData['subscriber_city'] = (string) $data['N4']->element('N401');
			$insuranceData['subscriber_state'] = (string) $data['N4']->element('N402');
			$insuranceData['subscriber_postal_code'] = (string) $data['N4']->element('N403');
		}

		if(array_key_exists('CLM', $data)) {
			$insuranceData['accept_assignment'] = (string) $data['CLM']->element('CLM08');
		}

		if(array_key_exists('DMG', $data)) {
			$insuranceData['subscriber_DOB'] = (string) $data['DMG']->element('DMG02');
			$insuranceData['subscriber_sex'] = (string) $data['DMG']->element('DMG03');
		}

		if(count($insuranceData)) {
			return $this->store()->storeInsuranceData($insuranceData);
		}
	}

	/**
	 * Store data in PatientData table
	 *
	 * @param  array  $data  to store in table
	 *
	 * @return integer|null  Id of record from table or null if no data provided
	 */
	protected function storePatientData(array $data) {
		$patientData = [];

		if(array_key_exists('User', $data)) {
			$patientData['providerID'] = $data['User'];
		}

		if(array_key_exists('NM1', $data)) {
			$patientData['lname'] = (string) $data['NM1']->element('NM103');
			$patientData['fname'] = (string) $data['NM1']->element('NM104');
			$patientData['mname'] = (string) $data['NM1']->element('NM105');
		}

		if(array_key_exists('N3', $data)) {
			$patientData['street'] = (string) $data['N3']->element('N301').' '.$data['N3']->element('N302');
		}

		if(array_key_exists('N4', $data)) {
			$patientData['city'] = (string) $data['N4']->element('N401');
			$patientData['state'] = (string) $data['N4']->element('N402');
			$patientData['postal_code'] = (string) $data['N4']->element('N403');
		}

		if(array_key_exists('DMG', $data)) {
			$patientData['DOB'] = (string) $data['DMG']->element('DMG02');
			$patientData['sex'] = (string) $data['DMG']->element('DMG03');
		}

		if(count($patientData)) {
			return $this->store()->storePatientData($patientData);
		}
	}

	/**
	 * Store data in PhoneNumber table
	 *
	 * @param  array  $data  to store in table
	 *
	 * @return integer|null  Id of record from table or null if no data provided
	 */
	protected function storePhoneNumber(array $data) {
		$phoneNumber = [];

		if(array_key_exists('InsuranceCompany', $data)) {
			$phoneNumber['foreign_id'] = $data['InsuranceCompany'];
		}

		if(count($phoneNumber)) {
			return $this->store()->storePhoneNumber($phoneNumber);
		}
	}

	/**
	 * Store data in User table
	 *
	 * @param  array  $data  to store in table
	 *
	 * @return integer|null  Id of record from table or null if no data provided
	 */
	protected function storeUser(array $data) {
		$user = [];

		if(array_key_exists('Facility', $data)) {
			$user['facility_id'] = $data['Facility'];
		}

		if(array_key_exists('NM1', $data)) {
			$user['username'] = (string) $data['NM1']->element('NM103').$data['NM1']->element('NM104');
			$user['lname'] = (string) $data['NM1']->element('NM103');
			$user['fname'] = (string) $data['NM1']->element('NM104');
			$user['mname'] = (string) $data['NM1']->element('NM105');
			$user['npi'] = (string) $data['NM1']->element('NM109');
		};

		if(array_key_exists('PRV', $data) && $data['PRV']->elementEquals('PRV01', 'BI')) {
			$user['taxonomy'] = (string) $data['PRV']->element('PRV03');
		}

		if(count($user)) {
			return $this->store()->storeUser($user);
		}
	}

	/**
	 * Store data in X12Partner table
	 *
	 * @param  array  $data  to store in table
	 *
	 * @return integer|null  Id of record from table or null if no data provided
	 */
	protected function storeX12Partner(array $data) {
		$x12Partner = [];

		if(array_key_exists('ISA', $data)) {
			$x12Partner['x12_sender_id'] = (string) $data['ISA']->element('ISA06');
			$x12Partner['x12_receiver_id'] = (string) $data['ISA']->element('ISA08');
			$x12Partner['x12_isa01'] = (string) $data['ISA']->element('ISA01');
			$x12Partner['x12_isa02'] = (string) $data['ISA']->element('ISA02');
			$x12Partner['x12_isa03'] = (string) $data['ISA']->element('ISA03');
			$x12Partner['x12_isa04'] = (string) $data['ISA']->element('ISA04');
			$x12Partner['x12_isa05'] = (string) $data['ISA']->element('ISA05');
			$x12Partner['x12_isa07'] = (string) $data['ISA']->element('ISA07');
			$x12Partner['x12_isa14'] = (string) $data['ISA']->element('ISA14');
			$x12Partner['x12_isa15'] = (string) $data['ISA']->element('ISA15');
		}

		if(array_key_exists('GS', $data)) {
			$x12Partner['x12_version'] = (string) $data['GS']->element('GS08'); // '005010X098A1'
			$x12Partner['x12_gs02'] = (string) $data['GS']->element('GS02');
			$x12Partner['x12_gs03'] = (string) $data['GS']->element('GS03');
		}

		if(array_key_exists('NM1', $data)) {
			$x12Partner['name'] = (string) $data['NM1']->element('NM103');
			$x12Partner['id_number'] = (string) $data['NM1']->element('NM109');
		}

		if(count($x12Partner)) {
			return $this->store()->storeX12Partner($x12Partner);
		}
	}

	/**
	 * Add value at specified index in second array if key exists in first array
	 *
	 * @param  string  $indexFind  Index to locate in find array
	 * @param  array   $findArray  Array to check for index
	 * @param  string  $indexAdd   Index to add value at in add array from index find
	 * @param  array   $addArray   Array to add value to
	 */
	protected function existsAdd($indexFind, array &$findArray, $indexAdd, array &$addArray) {
		if(array_key_exists($indexFind, $findArray)) {
			$addArray[$indexAdd] = $findArray[$indexFind];
		}
	}

	/**
	 * Find next segment or next segment matching provided segment names
	 *
	 * @param  array       $segmentGroup    Array of segments to search
	 * @param  array|null  $segmentMatches  Array of segment names to check against or null to return next segment regardles of name
	 *
	 * @return SunCoastConnection\ClaimsToOEMR\Document\Raw\Segment|null  Segment found or null on fail
	 */
	protected function findNextSegment(array &$segmentGroup, array $segmentMatches = null) {
		do {
			$segment = current($segmentGroup);

			if($segment && (is_null($segmentMatches) || in_array($segment->getName(), $segmentMatches))) {
				next($segmentGroup);

				return $segment;
			}
		} while(next($segmentGroup));
	}

	/**
	 * Process an X12N837 document
	 *
	 * @param  \SunCoastConnection\ClaimsToOEMR\X12N837  $document  X12N837 document to process
	 */
	public function processDocument(X12N837 $document) {
		$descendant = $document->getDescendant();

		if(is_array($descendant)) {
			foreach($descendant as $section) {
				$this->processInterchangeControl($section);
			}
		}
	}

	/**
	 * Process the interchange control envelope of the X12N837 document
	 *
	 * @param  \SunCoastConnection\ClaimsToOEMR\X12N837\Envelope\InterchangeControl  $interchangeControl  Interchange control envelope of the X12N837 document
	 */
	protected function processInterchangeControl(Envelope\InterchangeControl $interchangeControl) {
		$data = [];

		$header = $interchangeControl->getHeader();

		$data['ISA'] = $this->findNextSegment(
			$header,
			[ 'ISA' ]
		);

		$descendant = $interchangeControl->getDescendant();

		if(is_array($descendant)) {
			foreach($descendant as $section) {
				$this->processFunctionalGroup($section, $data);
			}
		}
	}

	/**
	 * Process the functional group envelope of the X12N837 document
	 *
	 * @param  \SunCoastConnection\ClaimsToOEMR\X12N837\Envelope\FunctionalGroup  $functionalGroup  Functional group envelope of the X12N837 document
	 * @param  array                                                              $data             Array of data to pass to subsequent envelopes and loops
	 */
	protected function processFunctionalGroup(Envelope\FunctionalGroup $functionalGroup, array &$data) {
		$header = $functionalGroup->getHeader();

		$isa = $data['ISA'];
		$gs = $this->findNextSegment(
			$header,
			[ 'GS' ]
		);

		$descendant = $functionalGroup->getDescendant();

		if(is_array($descendant)) {
			foreach($descendant as $section) {
				$data = [
					'ISA' => $isa,
					'GS' => $gs,
				];

				$this->processTransactionSet($section, $data);
			}
		}
	}

	/**
	 * Process the functional group envelope of the X12N837 document
	 *
	 * @param  \SunCoastConnection\ClaimsToOEMR\X12N837\Envelope\TransactionSet  $transactionSet  Transaction set envelope of the X12N837 document
	 * @param  array                                                             $data            Array of data to pass to subsequent loops
	 */
	protected function processTransactionSet(Envelope\TransactionSet $transactionSet, array &$data) {
		$header = $transactionSet->getHeader();

		$segment = $this->findNextSegment(
			$header,
			[ 'BHT' ]
		);

		// BEGINNING OF HIERARCHICAL TRANSACTION (BHT)
		// Transaction Type Code != Reporting (RP)
		// Allowed Codes:
		//     Subrogation Demand (31)
		//     Chargeable (CH)
		if($segment && !$segment->elementEquals('BHT06', 'RP')) {
			$descendant = $transactionSet->getDescendant();

			if(is_array($descendant)) {
				foreach($descendant as $section) {
					switch($section->getName()) {
						case 'Loop1000':
							$this->processLoop1000($section, $data);
							break;

						case 'Loop2000':
							$this->processLoop2000($section, $data);
							break;
					}
				}
			}
		}
	}

	/**
	 * Process loop 1000 of the X12N837 document
	 *
	 * @param  \SunCoastConnection\ClaimsToOEMR\X12N837\Loop\Loop1000  $loop1000  Loop 1000 of the X12N837 document
	 * @param  array                                                   $data      Array of data to pass to subsequent loops
	 */
	protected function processLoop1000(Loop\Loop1000 $loop1000, array &$data) {
		$header = $loop1000->getHeader();

		do {
			$segment = $this->findNextSegment(
				$header,
				[
					'NM1',
					'N3',
					'N4',
					'REF',
				]
			);

			if($segment) {
				switch($segment->getName()) {
					case 'NM1':
						$data['Loop1000_NM1'] = $segment;
						break;
					case 'N3':
						$data['Loop1000_N3'] = $segment;
						break;
					case 'N4':
						$data['Loop1000_N4'] = $segment;
						break;
					case 'REF':
						$data['Loop1000_REF'] = $segment;
						break;
				}
			}
		} while(!is_null($segment));

		if(array_key_exists('Loop1000_NM1', $data)) {
			switch($data['Loop1000_NM1']->element('NM101')) {
				case '40':
					// 1000B — RECEIVER NAME
					if($data['Loop1000_NM1']->elementEquals('NM102', '2')) {
						$data['CurrentX12Partner'] = $this->storeX12Partner([
							'NM1' => $data['Loop1000_NM1'],
							'ISA' => $data['ISA'],
							'GS' => $data['GS']
						]);
					}
					break;
				case '41':
					// 1000A — SUBMITTER NAME
					$facility = [
						'NM1' => $data['Loop1000_NM1'],
					];

					$this->existsAdd('Loop1000_N3', $data, 'N3', $facility);
					$this->existsAdd('Loop1000_N4', $data, 'N4', $facility);
					$this->existsAdd('Loop1000_REF', $data, 'REF', $facility);

					$data['CurrentFacility'] = $this->storeFacility($facility);
					break;
			}
		}
	}

	/**
	 * Process loop 2000 of the X12N837 document
	 *
	 * @param  \SunCoastConnection\ClaimsToOEMR\X12N837\Loop\Loop2000  $loop2000  Loop 2000 of the X12N837 document
	 * @param  array                                                   $data      Array of data to pass to subsequent loops
	 */
	protected function processLoop2000(Loop\Loop2000 $loop2000, array &$data) {
		$header = $loop2000->getHeader();

		do {
			$segment = $this->findNextSegment(
				$header,
				[
					'PRV',
					'SBR',
					'PAT',
				]
			);

			if($segment) {
				switch($segment->getName()) {
					case 'PRV':
						// 2000A — BILLING PROVIDER HIERARCHICAL LEVEL
						$data['Loop2000_PRV'] = $segment;
						break;
					case 'SBR':
						// 2000B — SUBSCRIBER HIERARCHICAL LEVEL
						$data['Loop2000_SBR'] = $segment;
						break;
					case 'PAT':
						// 2000B — SUBSCRIBER HIERARCHICAL LEVEL
						$data['Loop2000_PAT'] = $segment;
						break;
				}
			}
		} while(!is_null($segment));

		$descendant = $loop2000->getDescendant();

		if(is_array($descendant)) {
			foreach($descendant as $section) {
				switch($section->getName()) {
					case 'Loop2010':
						$this->processLoop2010($section, $data);
						break;

					case 'Loop2300':
						$this->processLoop2300($section, $data);
						break;
				}
			}
		}
	}

	/**
	 * Process loop 2010 of the X12N837 document
	 *
	 * @param  \SunCoastConnection\ClaimsToOEMR\X12N837\Loop\Loop2010  $loop2010  Loop 2010 of the X12N837 document
	 * @param  array                                                   $data      Array of data to pass to subsequent loops
	 */
	protected function processLoop2010(Loop\Loop2010 $loop2010, array &$data) {
		$header = $loop2010->getHeader();

		do {
			$segment = $this->findNextSegment(
				$header,
				[
					'NM1',
					'N3',
					'N4',
					'DMG',
					'REF',
				]
			);

			if($segment) {
				switch($segment->getName()) {
					case 'NM1':
						$data['Loop2010_NM1'] = $segment;
						break;
					case 'N3':
						$data['Loop2010_N3'] = $segment;
						break;
					case 'N4':
						$data['Loop2010_N4'] = $segment;
						break;
					case 'DMG':
						// 2010BA — SUBSCRIBER NAME & 2010CA — PATIENT NAME
						$data['Loop2010_DMG'] = $segment;
						break;
					case 'REF':
						// 2010AA — BILLING PROVIDER NAME & 2010AC — PAY-TO PLAN NAME
						$data['Loop2010_REF'] = $segment;
						break;
				}
			}
		} while(!is_null($segment));

		if(array_key_exists('Loop2010_NM1', $data)) {
			switch((string) $data['Loop2010_NM1']->element('NM101')) {
				case '85':
					// 2010AA — BILLING PROVIDER NAME
					$facility = [
						'NM1' => $data['Loop2010_NM1'],
					];

					$this->existsAdd('Loop2010_N3', $data, 'N3', $facility);
					$this->existsAdd('Loop2010_N4', $data, 'N4', $facility);
					$this->existsAdd('Loop2010_REF', $data, 'REF', $facility);

					$data['CurrentFacility'] = $this->storeFacility($facility);

					$user = [
						'Facility' => $data['CurrentFacility'],
						'NM1' => $data['Loop2010_NM1'],
					];

					$this->existsAdd('Loop2000_PRV', $data, 'PRV', $user);

					$data['CurrentUser'] = $this->storeUser($user);

					$this->storeGroup([
						'NM1' => $data['Loop2010_NM1']
					]);
					break;
				case '87':
					// 2010AB — PAY-TO ADDRESS NAME
					$facility = [
						'NM1' => $data['Loop2010_NM1'],
					];

					$this->existsAdd('Loop2010_N3', $data, 'N3', $facility);
					$this->existsAdd('Loop2010_N4', $data, 'N4', $facility);
					$this->existsAdd('Loop2010_REF', $data, 'REF', $facility);

					$data['CurrentFacility'] = $this->storeFacility($facility);

					$user = [
						'Facility' => $data['CurrentFacility'],
						'NM1' => $data['Loop2010_NM1'],
					];

					$this->existsAdd('Loop2000_PRV', $data, 'PRV', $user);

					$data['CurrentUser'] = $this->storeUser($user);

					$this->storeGroup([
						'NM1' => $data['Loop2010_NM1']
					]);
					break;
				case 'IL':
					// 2010BA — SUBSCRIBER NAME
					if(array_key_exists('Loop2000_SBR', $data) && $data['Loop2000_SBR']->elementEquals('SBR02', '18')) {
						$patientData = [
							'NM1' => $data['Loop2010_NM1'],
						];

						$this->existsAdd('Loop2010_N3', $data, 'N3', $patientData);
						$this->existsAdd('Loop2010_N4', $data, 'N4', $patientData);
						$this->existsAdd('Loop2010_DMG', $data, 'DMG', $patientData);

						$data['CurrentPatientData'] = $this->storePatientData($patientData);

						$insuranceData = [
							'PatientData' => $data['CurrentPatientData'],
							'NM1' => $data['Loop2010_NM1'],
							'SBR' => $data['Loop2000_SBR']
						];

						$this->existsAdd('Loop2010_N3', $data, 'N3', $insuranceData);
						$this->existsAdd('Loop2010_N4', $data, 'N4', $insuranceData);
						$this->existsAdd('Loop2010_DMG', $data, 'DMG', $insuranceData);

						$data['CurrentInsuranceData'] = $this->storeInsuranceData($insuranceData);
					}
					break;
				case 'PR':
					// 2010BB — PAYER NAME
					$data['CurrentInsuranceCompany'] = $this->storeInsuranceCompany([
						'X12Partner' => $data['CurrentX12Partner'],
						'NM1' => $data['Loop2010_NM1']
					]);

					$address = [
						'InsuranceCompany' => $data['CurrentInsuranceCompany'],
					];

					$this->existsAdd('Loop2010_N3', $data, 'N3', $address);
					$this->existsAdd('Loop2010_N4', $data, 'N4', $address);

					$this->storeAddress($address);

					$this->storePhoneNumber([
						'InsuranceCompany' => $data['CurrentInsuranceCompany']
					]);
					break;
				case 'QC':
					// 2010CA — PATIENT NAME
					$patientData = [
						'NM1' => $data['Loop2010_NM1']
					];

					$this->existsAdd('CurrentUser', $data, 'User', $patientData);
					$this->existsAdd('Loop2010_N3', $data, 'N3', $patientData);
					$this->existsAdd('Loop2010_N4', $data, 'N4', $patientData);

					$data['CurrentPatientData'] = $this->storePatientData($patientData);
					break;
			}
		}
	}

	/**
	 * Process loop 2300 of the X12N837 document
	 *
	 * @param  \SunCoastConnection\ClaimsToOEMR\X12N837\Loop\Loop2300  $loop2300  Loop 2300 of the X12N837 document
	 * @param  array                                                   $data      Array of data to pass to subsequent loops
	 */
	protected function processLoop2300(Loop\Loop2300 $loop2300, array &$data) {
		$header = $loop2300->getHeader();

		do {
			$segment = $this->findNextSegment(
				$header,
				[
					'CLM',
					'DTP',
					'REF',
					'NTE',
					'HI',
				]
			);

			if($segment) {
				switch($segment->getName()) {
					case 'CLM':
						// 2300 — CLAIM INFORMATION
						$data['Loop2300_CLM'] = $segment;
						break;
					case 'DTP':
						// 2300 — CLAIM INFORMATION
						$data['Loop2300_DTP'] = $segment;
						break;
					case 'REF':
						// 2300 — CLAIM INFORMATION
						$data['Loop2300_REF'] = $segment;
						break;
					case 'NTE':
						// 2300 — CLAIM INFORMATION
						$data['Loop2300_NTE'] = $segment;
						break;
					case 'HI':
						// 2300 — CLAIM INFORMATION
						if(!array_key_exists('Loop2300_HI', $data)) {
							$data['Loop2300_HI'] = [];
						}

						$data['Loop2300_HI'][] = $segment;
						break;
				}
			}
		} while(!is_null($segment));

		if(array_key_exists('Loop2300_CLM', $data)) {
			$formEncounter = [
				'CLM' => $data['Loop2300_CLM'],
			];

			$this->existsAdd('CurrentUser', $data, 'User', $formEncounter);
			$this->existsAdd('CurrentPatient', $data, 'Patient', $formEncounter);
			$this->existsAdd('CurrentFacility', $data, 'Facility', $formEncounter);
			$this->existsAdd('Loop2010_MN1', $data, 'MN1', $formEncounter);

			$data['CurrentFormEncounter'] = $this->storeFormEncounter($formEncounter);

			$form = [
				'CLM' => $data['Loop2300_CLM'],
				'FormEncounter' => $data['CurrentFormEncounter'],
			];

			$this->existsAdd('CurrentUser', $data, 'User', $form);
			$this->existsAdd('CurrentPatient', $data, 'Patient', $form);

			$this->storeForm($form);
		}

		if(array_key_exists('Loop2300_HI', $data)) {
			foreach ($data['Loop2300_HI'] as $segmentHI) {
				if($segmentHI->element('HI01')->subElementEquals(0, [ 'ABK', 'BK' ])) {
					$billing = [
						'HI' => $segmentHI
					];

					$this->existsAdd('CurrentUser', $data, 'User', $billing);
					$this->existsAdd('CurrentPatient', $data, 'Patient', $billing);
					$this->existsAdd('CurrentInsuranceCompany', $data, 'InsuranceCompany', $billing);
					$this->existsAdd('Loop2300_CLM', $data, 'CLM', $billing);

					$this->storeBilling($billing);
				}
			}
		}

		$descendant = $loop2300->getDescendant();

		if(is_array($descendant)) {
			foreach($descendant as $section) {
				switch($section->getName()) {
					// case 'Loop2305':
					// 	$this->processLoop2305($section, $data);
					// 	break;
					case 'Loop2310':
						$this->processLoop2310($section, $data);
						break;

					case 'Loop2320':
						$this->processLoop2320($section, $data);
						break;

					case 'Loop2400':
						$this->processLoop2400($section, $data);
						break;
				}
			}
		}
	}

	/**
	 * Process loop 2305 of the X12N837 document
	 *
	 * @param  \SunCoastConnection\ClaimsToOEMR\X12N837\Loop\Loop2305  $loop2305  Loop 2305 of the X12N837 document
	 * @param  array                                                   $data      Array of data to pass to subsequent loops
	 */
	// protected function processLoop2305(Loop\Loop2305 $loop2305, array &$data) {
	// 	$header = $loop2305->getHeader();
	// }

	/**
	 * Process loop 2310 of the X12N837 document
	 *
	 * @param  \SunCoastConnection\ClaimsToOEMR\X12N837\Loop\Loop2310  $loop2310  Loop 2310 of the X12N837 document
	 * @param  array                                                   $data      Array of data to pass to subsequent loops
	 */
	protected function processLoop2310(Loop\Loop2310 $loop2310, array &$data) {
		$header = $loop2310->getHeader();

		do {
			$segment = $this->findNextSegment(
				$header,
				[
					'NM1',
					'N3',
					'N4',
					'PRV',
				]
			);

			if($segment) {
				switch($segment->getName()) {
					case 'NM1':
						$data['Loop2310_NM1'] = $segment;
						break;
					case 'N3':
						// 2310C — SERVICE FACILITY LOCATION NAME
						$data['Loop2310_N3'] = $segment;
						break;
					case 'N4':
						// 2310C — SERVICE FACILITY LOCATION NAME
						$data['Loop2310_N4'] = $segment;
						break;
					case 'PRV':
						// 2310B — RENDERING PROVIDER NAME
						$data['Loop2310_PRV'] = $segment;
						break;
				}
			}
		} while(!is_null($segment));

		if(array_key_exists('Loop2310_NM1', $data)) {
			switch($data['Loop2310_NM1']->element('NM101')) {
				case '77':
					// 2310C — SERVICE FACILITY LOCATION NAME
					if($data['Loop2310_NM1']->elementEquals('NM102', '2')) {
						$facility = [
							'NM1' => $data['Loop2310_NM1'],
						];

						$this->existsAdd('Loop2310_N3', $data, 'N3', $facility);
						$this->existsAdd('Loop2310_N4', $data, 'N4', $facility);
						$this->existsAdd('Loop2300_CLM', $data, 'CLM', $facility);

						$data['CurrentFacility'] = $this->storeFacility($facility);
					}
					break;
				case '82':
					// 2310B — RENDERING PROVIDER NAME
					$user = [
						'NM1' => $data['Loop2310_NM1'],
					];

					$this->existsAdd('CurrentFacility', $data, 'Facility', $user);

					$data['CurrentUser'] = $this->storeUser($user);

					$this->storeGroup([
						'NM1' => $data['Loop2310_NM1']
					]);
					break;
				case 'DN':
					// 2310A — REFERRING PROVIDER NAME
					$user = [
						'NM1' => $data['Loop2310_NM1'],
					];

					$this->existsAdd('CurrentFacility', $data, 'Facility', $user);

					$data['CurrentUser'] = $this->storeUser($user);

					$this->storeGroup([
						'NM1' => $data['Loop2310_NM1']
					]);
					break;
				case 'DQ':
					// 2310D — SUPERVISING PROVIDER NAME
			}
		}
	}

	/**
	 * Process loop 2320 of the X12N837 document
	 *
	 * @param  \SunCoastConnection\ClaimsToOEMR\X12N837\Loop\Loop2320  $loop2320  Loop 2320 of the X12N837 document
	 * @param  array                                                   $data      Array of data to pass to subsequent loops
	 */
	protected function processLoop2320(Loop\Loop2320 $loop2320, array &$data) {
		$header = $loop2320->getHeader();

		do {
			$segment = $this->findNextSegment(
				$header,
				[
					'SBR',
				]
			);

			if($segment) {
				switch($segment->getName()) {
					case 'SBR':
						// 2320 — OTHER SUBSCRIBER INFORMATION
						$data['Loop2320_SBR'] = $segment;
						break;
				}
			}
		} while(!is_null($segment));

		$descendant = $loop2320->getDescendant();

		if(is_array($descendant)) {
			foreach($descendant as $section) {
				switch($section->getName()) {
					case 'Loop2330':
						$this->processLoop2330($section, $data);
						break;
				}
			}
		}
	}

	/**
	 * Process loop 2330 of the X12N837 document
	 *
	 * @param  \SunCoastConnection\ClaimsToOEMR\X12N837\Loop\Loop2330  $loop2330  Loop 2330 of the X12N837 document
	 * @param  array                                                   $data      Array of data to pass to subsequent loops
	 */
	protected function processLoop2330(Loop\Loop2330 $loop2330, array &$data) {
		$header = $loop2330->getHeader();

		do {
			$segment = $this->findNextSegment(
				$header,
				[
					'NM1',
					'N3',
					'N4',
					'REF',
				]
			);

			if($segment) {
				switch($segment->getName()) {
					case 'NM1':
						$data['Loop2330_NM1'] = $segment;
						break;
					case 'N3':
						$data['Loop2330_N3'] = $segment;
						break;
					case 'N4':
						$data['Loop2330_N4'] = $segment;
						break;
					case 'REF':
						// 2330B — OTHER PAYER NAME
						$data['Loop2330_REF'] = $segment;
						break;
				}
			}
		} while(!is_null($segment));

		if(array_key_exists('Loop2330_NM1', $data)) {
			switch($data['Loop2330_NM1']->element('NM101')) {
				case '77':
					// 2330E — OTHER PAYER SERVICE FACILITY LOCATION
					if($data['Loop2330_NM1']->elementEquals('NM102', '2')) {
						$facility = [
							'NM1' => $data['Loop2330_NM1'],
						];

						$this->existsAdd('Loop2330_N3', $data, 'N3', $facility);
						$this->existsAdd('Loop2330_N4', $data, 'N4', $facility);
						$this->existsAdd('Loop2300_CLM', $data, 'CLM', $facility);

						$data['CurrentFacility'] = $this->storeFacility($facility);
					}
					break;
				case '82':
					// 2330D — OTHER PAYER RENDERING PROVIDER
					$user = [
						'NM1' => $data['Loop2330_NM1'],
					];

					$this->existsAdd('CurrentFacility', $data, 'Facility', $user);

					$data['CurrentUser'] = $this->storeUser($user);

					$this->storeGroup([
						'NM1' => $data['Loop2330_NM1']
					]);
					break;
				case '85':
					// 2330G — OTHER PAYER BILLING PROVIDER
					$facility = [
						'NM1' => $data['Loop2330_NM1'],
					];

					$this->existsAdd('Loop2330_N3', $data, 'N3', $facility);
					$this->existsAdd('Loop2330_N4', $data, 'N4', $facility);
					$this->existsAdd('Loop2330_REF', $data, 'REF', $facility);
					$this->existsAdd('Loop2300_CLM', $data, 'CLM', $facility);

					$data['CurrentFacility'] = $this->storeFacility($facility);

					$user = [
						'NM1' => $data['Loop2330_NM1'],
					];

					$this->existsAdd('CurrentFacility', $data, 'Facility', $user);
					$this->existsAdd('Loop2000_PRV', $data, 'PRV', $user);

					$data['CurrentUser'] = $this->storeUser($user);

					$this->storeGroup([
						'NM1' => $data['Loop2330_NM1']
					]);
					break;
				case 'DN':
					// 2330C — OTHER PAYER REFERRING PROVIDER
					$user = [
						'NM1' => $data['Loop2330_NM1'],
					];

					$this->existsAdd('CurrentFacility', $data, 'Facility', $user);

					$data['CurrentUser'] = $this->storeUser($user);

					$this->storeGroup([
						'NM1' => $data['Loop2330_NM1']
					]);
					break;
				case 'DQ':
					// 2330F — OTHER PAYER SUPERVISING PROVIDER
					break;
				case 'IL':
					// 2330A — OTHER SUBSCRIBER NAME
					if(array_key_exists('Loop2320_SBR', $data) &&
						$data['Loop2320_SBR']->elementEquals('SBR02', '18')
					) {
						$patientData = [];

						$this->existsAdd('CurrentUser', $data, 'User', $patientData);
						$this->existsAdd('Loop2330_NM1', $data, 'NM1', $patientData);
						$this->existsAdd('Loop2330_N3', $data, 'N3', $patientData);
						$this->existsAdd('Loop2330_N4', $data, 'N4', $patientData);
						$this->existsAdd('Loop2330_DMG', $data, 'DMG', $patientData);

						$data['CurrentPatientData'] = $this->storePatientData($patientData);

						$insuranceData = [
							'PatientData' => $data['CurrentPatientData'],
						];

						$this->existsAdd('Loop2330_SBR', $data, 'SBR', $patientData);
						$this->existsAdd('Loop2330_NM1', $data, 'NM1', $patientData);
						$this->existsAdd('Loop2330_CLM', $data, 'CLM', $patientData);
						$this->existsAdd('Loop2330_N3', $data, 'N3', $patientData);
						$this->existsAdd('Loop2330_N4', $data, 'N4', $patientData);

						$data['CurrentInsuranceData'] = $this->storeInsuranceData($insuranceData);
					}
					break;
				case 'PR':
					// 2330B — OTHER PAYER NAME
					$insuranceCompany = [
						'NM1' => $data['Loop2330_NM1']
					];

					$this->existsAdd('CurrentX12Partner', $data, 'X12Partner', $insuranceCompany);

					$data['CurrentInsuranceCompany'] = $this->storeInsuranceCompany($insuranceCompany);

					$address = [
						'InsuranceCompany' => $data['CurrentInsuranceCompany'],
					];

					$this->existsAdd('Loop2330_N3', $data, 'N3', $address);
					$this->existsAdd('Loop2330_N4', $data, 'N4', $address);

					$this->storeAddress($address);

					$this->storePhoneNumber([
						'InsuranceCompany' => $data['CurrentInsuranceCompany']
					]);
					break;
			}
		}
	}

	/**
	 * Process loop 2400 of the X12N837 document
	 *
	 * @param  \SunCoastConnection\ClaimsToOEMR\X12N837\Loop\Loop2400  $loop2400  Loop 2400 of the X12N837 document
	 * @param  array                                                   $data      Array of data to pass to subsequent loops
	 */
	protected function processLoop2400(Loop\Loop2400 $loop2400, array &$data) {
		$header = $loop2400->getHeader();

		do {
			$segment = $this->findNextSegment(
				$header,
				[
					'SV1',
					'DTP',
					'NTE',
				]
			);

			if($segment) {
				switch($segment->getName()) {
					case 'SV1':
						// 2400 — SERVICE LINE NUMBER
						if(!array_key_exists('Loop2400_SV1', $data)) {
							$data['Loop2400_SV1'] = [];
						}

						$data['Loop2400_SV1'][] = $segment;
						break;
					case 'DTP':
						// 2400 — SERVICE LINE NUMBER
						$data['Loop2400_DTP'] = $segment;
						break;
					case 'NTE':
						// 2400 — SERVICE LINE NUMBER
						$data['Loop2400_NTE'] = $segment;
						break;
				}
			}
		} while(!is_null($segment));

		if(array_key_exists('Loop2400_SV1', $data)) {
			$billing = [];

			$this->existsAdd('CurrentUser', $data, 'User', $billing);
			$this->existsAdd('CurrentPatient', $data, 'Patient', $billing);
			$this->existsAdd('CurrentInsuranceCompany', $data, 'InsuranceCompany', $billing);
			$this->existsAdd('Loop2300_HI', $data, 'HI', $billing);
			$this->existsAdd('Loop2300_CLM', $data, 'CLM', $billing);

			foreach($data['Loop2400_SV1'] as $segmentSV1) {
				if($segmentSV1->element('SV101')->subElementEquals(0, [ 'HC', 'WK' ])) {
					$billing['SV1'] = $segmentSV1;

					$this->storeBilling($billing);
				}
			}
		}


		$descendant = $loop2400->getDescendant();

		if(is_array($descendant)) {
			foreach($descendant as $section) {
				switch($section->getName()) {
					// case 'Loop2410':
					// 	$this->processLoop2410($section, $data);
					// 	break;

					case 'Loop2420':
						$this->processLoop2420($section, $data);
						break;

					// case 'Loop2430':
					// 	$this->processLoop2430($section, $data);
					// 	break;

					// case 'Loop2440':
					// 	$this->processLoop2440($section, $data);
					// 	break;
				}
			}
		}
	}

	/**
	 * Process loop 2410 of the X12N837 document
	 *
	 * @param  \SunCoastConnection\ClaimsToOEMR\X12N837\Loop\Loop2410  $loop2410  Loop 2410 of the X12N837 document
	 * @param  array                                                   $data      Array of data to pass to subsequent loops
	 */
	// protected function processLoop2410(Loop\Loop2410 $loop2410, array &$data) {
	// 	$header = $loop2410->getHeader();
	// }

	/**
	 * Process loop 2420 of the X12N837 document
	 *
	 * @param  \SunCoastConnection\ClaimsToOEMR\X12N837\Loop\Loop2420  $loop2420  Loop 2420 of the X12N837 document
	 * @param  array                                                   $data      Array of data to pass to subsequent loops
	 */
	protected function processLoop2420(Loop\Loop2420 $loop2420, array &$data) {
		$header = $loop2420->getHeader();

		do {
			$segment = $this->findNextSegment(
				$header,
				[
					'NM1',
					'N3',
					'N4',
					'PRV',
				]
			);

			if($segment) {
				switch($segment->getName()) {
					case 'NM1':
						$data['Loop2420_NM1'] = $segment;
						break;
					case 'N3':
						$data['Loop2420_N3'] = $segment;
						break;
					case 'N4':
						$data['Loop2420_N4'] = $segment;
						break;
					case 'PRV':
						// 2420A — RENDERING PROVIDER NAME
						$data['Loop2420_PRV'] = $segment;
						break;
				}
			}
		} while(!is_null($segment));

		if(array_key_exists('Loop2420_NM1', $data)) {
			switch($data['Loop2420_NM1']->element('NM101')) {
				case '77':
					// 2420C — SERVICE FACILITY LOCATION NAME
					if($data['Loop2420_NM1']->elementEquals('NM102', '2')) {
						$facility = [
							'NM1' => $data['Loop2420_NM1'],
						];

						$this->existsAdd('Loop2420_N3', $data, 'N3', $facility);
						$this->existsAdd('Loop2420_N4', $data, 'N4', $facility);
						$this->existsAdd('Loop2300_CLM', $data, 'CLM', $facility);

						$data['CurrentFacility'] = $this->storeFacility($facility);
					}
					break;
				case '82':
					// 2420A — RENDERING PROVIDER NAME
					$user = [
						'NM1' => $data['Loop2420_NM1'],
					];

					$this->existsAdd('CurrentFacility', $data, 'Facility', $user);

					$data['CurrentUser'] = $this->storeUser($user);

					$this->storeGroup([
						'NM1' => $data['Loop2420_NM1']
					]);
					break;
				case 'DK':
					// 2420E — ORDERING PROVIDER NAME
					$facility = [
						'NM1' => $data['Loop2420_NM1'],
					];

					$this->existsAdd('Loop2420_N3', $data, 'N3', $facility);
					$this->existsAdd('Loop2420_N4', $data, 'N4', $facility);
					$this->existsAdd('Loop2300_CLM', $data, 'CLM', $facility);

					$data['CurrentFacility'] = $this->storeFacility($facility);

					$user = [
						'NM1' => $data['Loop2420_NM1'],
					];

					$this->existsAdd('CurrentFacility', $data, 'Facility', $user);

					$data['CurrentUser'] = $this->storeUser($user);

					$this->storeGroup([
						'NM1' => $data['Loop2420_NM1']
					]);
					break;
				case 'DN':
					// 2420F — REFERRING PROVIDER NAME
					$user = [
						'NM1' => $data['Loop2420_NM1'],
					];

					$this->existsAdd('CurrentFacility', $data, 'Facility', $user);

					$data['CurrentUser'] = $this->storeUser($user);

					$this->storeGroup([
						'NM1' => $data['Loop2420_NM1']
					]);
					break;
			}
		}
	}

	/**
	 * Process loop 2430 of the X12N837 document
	 *
	 * @param  \SunCoastConnection\ClaimsToOEMR\X12N837\Loop\Loop2430  $loop2430  Loop 2430 of the X12N837 document
	 * @param  array                                                   $data      Array of data to pass to subsequent loops
	 */
	// protected function processLoop2430(Loop\Loop2430 $loop2430, array &$data) {
	// 	$header = $loop2430->getHeader();
	// }

	/**
	 * Process loop 2440 of the X12N837 document
	 *
	 * @param  \SunCoastConnection\ClaimsToOEMR\X12N837\Loop\Loop2440  $loop2440  Loop 2440 of the X12N837 document
	 * @param  array                                                   $data      Array of data to pass to subsequent loops
	 */
	// protected function processLoop2440(Loop\Loop2440 $loop2440, array &$data) {
	// 	$header = $loop2440->getHeader();
	// }
}