<?php

namespace SunCoastConnection\ClaimsToOEMR\X12N837;

use \SunCoastConnection\ClaimsToOEMR\Store,
	\SunCoastConnection\ClaimsToOEMR\X12N837,
	\SunCoastConnection\ClaimsToOEMR\X12N837\Envelope,
	\SunCoastConnection\ClaimsToOEMR\X12N837\Loop,
	\SunCoastConnection\ClaimsToOEMR\X12N837\Segment;

class Cache {

	protected $store;

	static public function getInstance(Store $store) {
		return new static($store);
	}

	public function __construct(Store $store) {
		$this->store($store);
	}

	protected function store(Store $setStore = null) {
		static $store = null;

		if(is_null($store) && !is_null($setStore)) {
			$store = $setStore;
		}

		return $store;
	}

	public function processDocument(X12N837 $document) {
		$descendant = $document->getDescendant();

		if(is_array($descendant)) {
			foreach($descendant as $section) {
				$this->processInterchangeControl($section);
			}
		}
	}

	protected function findNextSegment(array &$segmentGroup, array $segmentMatches = null) {
		do {
			$segment = current($segmentGroup);

			if($segment && (is_null($segmentMatches) || in_array($segment->getName(), $segmentMatches))) {
				next($segmentGroup);

				return $segment;
			}
		} while(next($segmentGroup));
	}

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

	protected function processTransactionSet(Envelope\TransactionSet $transactionSet, array &$data) {
		$header = $transactionSet->getHeader();

		$segment = $this->findNextSegment(
			$header,
			[ 'BHT' ]
		);

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

	protected function processLoop1000(Loop\Loop1000 $loop1000, array &$data) {
		$header = $loop1000->getHeader();

		do {
			$segment = $this->findNextSegment(
				$header,
				[
					'NM1',
					'N3',
					'N4',
				]
			);

			if($segment) {
				switch($segment->getName()) {
					case 'NM1':
						$data['LastNM1'] = $segment;

						switch($segment->element('NM101')) {
							case '40':
								// 1000B — RECEIVER NAME
								// storeX12Partners
								if($segment->elementEquals('NM102', '2')) {
									$data['CurrentX12Partner'] = $this->store()->storeX12Partner([
										'name' => (string) $segment->element('NM103'),
										'id_number' => (string) $segment->element('NM109'),
										'x12_sender_id' => (string) $data['ISA']->element('ISA06'),
										'x12_receiver_id' => (string) $data['ISA']->element('ISA08'),
										'x12_version' => (string) $data['GS']->element('GS08'),	// '005010X098A1'
										'x12_isa01' => (string) $data['ISA']->element('ISA01'),
										'x12_isa02' => (string) $data['ISA']->element('ISA02'),
										'x12_isa03' => (string) $data['ISA']->element('ISA03'),
										'x12_isa04' => (string) $data['ISA']->element('ISA04'),
										'x12_isa05' => (string) $data['ISA']->element('ISA05'),
										'x12_isa07' => (string) $data['ISA']->element('ISA07'),
										'x12_isa14' => (string) $data['ISA']->element('ISA14'),
										'x12_isa15' => (string) $data['ISA']->element('ISA15'),
										'x12_gs02' => (string) $data['GS']->element('GS02'),
										'x12_gs03' => (string) $data['GS']->element('GS03'),
									]);
								}
								break;
							case '41':
								// 1000A — SUBMITTER NAME
								$data['SubmitterName'] = $segment->element('NM103');	// ?
								$data['SubmitterId'] = $segment->element('NM109');		// ?
								break;
						}
						break;
					case 'N3':
						// if($data['LastNM1']->elementEquals('NM101', '41')) {
						// 	$data['SubmitterAddress1'] = $segment->element('N301');	// ?
						// 	$data['SubmitterAddress2'] = $segment->element('N302');	// ?
						// }
						break;
					case 'N4':
						// if($data['LastNM1']->elementEquals('NM101', '41')) {
						// 	$data['SubmitterCity'] = $segment->element('N401');		// ?
						// 	$data['SubmitterState'] = $segment->element('N402');	// ?
						// 	$data['SubmitterZip'] = $segment->element('N403');		// ?
						// }
						break;
				}
			}
		} while(!is_null($segment));
	}

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
						if($segment->elementEquals('PRV01', 'BI')) {
							$data['BillingProviderTaxonomy'] = $segment->element('PRV03');	// storeUsers
						}
						break;
					case 'SBR':
						// 2000B — SUBSCRIBER HIERARCHICAL LEVEL
						switch($segment->element('SBR01')) {
							case 'P':
								$data['CurrentInsuranceType'] = 1;

								$data['PrimarySubscriberRelation'] = ($segment->elementEquals('SBR02', '18') ? 'self' : '');	// storeInsuranceData
								$data['PrimaryPolicy'] = $segment->element('SBR03');	// storeInsuranceData
								$data['PrimaryPlanName'] = $segment->element('SBR04');	// storeInsuranceData
								break;
							case 'S':
								$data['CurrentInsuranceType'] = 2;

								$data['SecondarySubscriberRelation'] = ($segment->elementEquals('SBR02', '18') ? 'self' : '');	// storeInsuranceData
								$data['SecondaryPolicy'] = $segment->element('SBR03');	// storeInsuranceData
								$data['SecondaryPlanName'] = $segment->element('SBR04');	// storeInsuranceData
								break;
							case 'T':
								$data['CurrentInsuranceType'] = 3;

								$data['TertiarySubscriberRelation'] = ($segment->elementEquals('SBR02', '18') ? 'self' : '');	// storeInsuranceData
								$data['TertiaryPolicy'] = $segment->element('SBR03');	// storeInsuranceData
								$data['TertiaryPlanName'] = $segment->element('SBR04');	// storeInsuranceData
								break;
						}
						break;
					case 'PAT':
						// 2000B — SUBSCRIBER HIERARCHICAL LEVEL
						if(array_key_exists('CurrentInsuranceType', $data)) {
							switch($data['CurrentInsuranceType']) {
								case 1:
									if($data['PrimarySubscriberRelation'] != 'self') {
										$data['PrimaryPatientRelation'] = $segment->element('PAT01');	// ?
									}
									break;
								case 2:
									if($data['SecondarySubscriberRelation'] != 'self') {
										$data['SecondaryPatientRelation'] = $segment->element('PAT01');	// ?
									}
									break;
								case 3:
									if($data['TertiarySubscriberRelation'] != 'self') {
										$data['TertiaryPatientRelation'] = $segment->element('PAT01');	// ?
									}
									break;
							}
						} else {
							echo 'Missing: CurrentInsuranceType ['.__FILE__.':'.__LINE__.']'.PHP_EOL;
						}
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
						$data['LastNM1'] = $segment;

						switch($segment->element('NM101')) {
							case '85':
								// 2010AA — BILLING PROVIDER NAME
								$data['BillingType'] = $segment->element('NM102');	// ?
								$data['BillingProviderLastName'] = $segment->element('NM103');	// storeFacility	// storeUser	// storeGroup	// storeFormEncounter
								$data['BillingProviderFirstName'] = $segment->element('NM104');	// storeUser	// storeGroup
								$data['BillingProviderMiddleName'] = $segment->element('NM105');	// storeUser
								$data['BillingProviderSuffix'] = $segment->element('NM107');	// ?
								$data['BillingProviderId'] = $segment->element('NM109');	// storeFacility	// storeUser	// storeGroup
								break;
							case '87':
								// 2010AB — PAY-TO ADDRESS NAME
								$data['PayToType'] = $segment->element('NM102');	// ?
								$data['PayToProviderLastName'] = $segment->element('NM103');	// storeUser	// storeGroup
								$data['PayToProviderFirstName'] = $segment->element('NM104');	// storeUser	// storeGroup
								$data['PayToProviderMiddleName'] = $segment->element('NM105');	// storeUser
								$data['PayToProviderSuffix'] = $segment->element('NM107');	// ?
								$data['PayToProviderId'] = $segment->element('NM109');	// storeUser	// storeGroup
								break;
							case 'IL':
								// 2010BA — SUBSCRIBER NAME
								if(array_key_exists('CurrentInsuranceType', $data)) {
									switch($data['CurrentInsuranceType']) {
										case 1:
											$data['PrimarySubscriberLastName'] = $segment->element('NM103');	// storeInsuranceData
											$data['PrimarySubscriberFirstName'] = $segment->element('NM104');	// storeInsuranceData
											$data['PrimarySubscriberMiddleName'] = $segment->element('NM105');	// storeInsuranceData
											$data['PrimarySubscriberSuffix'] = $segment->element('NM107');	// ?
											$data['PrimarySubscriberId'] = $segment->element('NM109');	// storeInsuranceData

											if($data['PrimarySubscriberRelation'] == 'self') {
												$data['PatientLastName'] = $segment->element('NM103');	// storePatientData
												$data['PatientFirstName'] = $segment->element('NM104');	// storePatientData
												$data['PatientMiddleName'] = $segment->element('NM105');	// storePatientData
												$data['PatientSuffix'] = $segment->element('NM107');	// ?
												$data['PatientId'] = $segment->element('NM109');	// ?
											}
											break;
										case 2:
											$data['SecondarySubscriberLastName'] = $segment->element('NM103');	// storeInsuranceData
											$data['SecondarySubscriberFirstName'] = $segment->element('NM104');	// storeInsuranceData
											$data['SecondarySubscriberMiddleName'] = $segment->element('NM105');	// storeInsuranceData
											$data['SecondarySubscriberSuffix'] = $segment->element('NM107');	// ?
											$data['SecondarySubscriberId'] = $segment->element('NM109');	// storeInsuranceData

											if($data['SecondarySubscriberRelation'] == 'self' && (!array_key_exists('PatientLastName', $data) || $data['PatientLastName'] == '')) {
												$data['PatientLastName'] = $segment->element('NM103');	// storePatientData
												$data['PatientFirstName'] = $segment->element('NM104');	// storePatientData
												$data['PatientMiddleName'] = $segment->element('NM105');	// storePatientData
												$data['PatientSuffix'] = $segment->element('NM107');	// ?
												$data['PatientId'] = $segment->element('NM109');	// ?
											}
											break;
										case 3:
											$data['TertiarySubscriberLastName'] = $segment->element('NM103');	// storeInsuranceData
											$data['TertiarySubscriberFirstName'] = $segment->element('NM104');	// storeInsuranceData
											$data['TertiarySubscriberMiddleName'] = $segment->element('NM105');	// storeInsuranceData
											$data['TertiarySubscriberSuffix'] = $segment->element('NM107');	// ?
											$data['TertiarySubscriberId'] = $segment->element('NM109');	// storeInsuranceData

											if($data['TertiarySubscriberRelation'] == 'self' && (!array_key_exists('PatientLastName', $data) || $data['PatientLastName'] == '')) {
												$data['PatientLastName'] = $segment->element('NM103');	// storePatientData
												$data['PatientFirstName'] = $segment->element('NM104');	// storePatientData
												$data['PatientMiddleName'] = $segment->element('NM105');	// storePatientData
												$data['PatientSuffix'] = $segment->element('NM107');	// ?
												$data['PatientId'] = $segment->element('NM109');	// ?
											}
											break;
									}
								} else {
									echo 'Missing: CurrentInsuranceType ['.__FILE__.':'.__LINE__.']'.PHP_EOL;
								}
								break;
							case 'PR':
								// 2010BB — PAYER NAME
								if(array_key_exists('CurrentInsuranceType', $data)) {
									switch($data['CurrentInsuranceType']) {
										case 1:
											$data['PrimaryPayerName'] = $segment->element('NM103');	// storeInsuranceCompany
											$data['PrimaryPayerId'] = $segment->element('NM109');	// storeInsuranceCompany
											break;
										case 2:
											$data['SecondaryPayerName'] = $segment->element('NM103');	// storeInsuranceCompany
											$data['SecondaryPayerId'] = $segment->element('NM109');	// storeInsuranceCompany
											break;
										case 3:
											$data['TertiaryPayerName'] = $segment->element('NM103');	// storeInsuranceCompany
											$data['TertiaryPayerId'] = $segment->element('NM109');	// storeInsuranceCompany
											break;
									}
								} else {
									echo 'Missing: CurrentInsuranceType ['.__FILE__.':'.__LINE__.']'.PHP_EOL;
								}
								break;
							case 'QC':
								// 2010CA — PATIENT NAME
								$data['PatientLastName'] = $segment->element('NM103');	// storePatientData
								$data['PatientFirstName'] = $segment->element('NM104');	// storePatientData
								$data['PatientMiddleName'] = $segment->element('NM105');	// storePatientData
								$data['PatientSuffix'] = $segment->element('NM107');	// ?
								$data['PatientId'] = $segment->element('NM109');	// ?
								break;
						}
						break;
					case 'N3':
						switch($data['LastNM1']->element('NM101')) {
							case '85':
								// 2010AA — BILLING PROVIDER NAME
								$data['BillingProviderAddress1'] = $segment->element('N301');	// storeFacility
								$data['BillingProviderAddress2'] = $segment->element('N302');	// storeFacility
								break;
							case '87':
								// 2010AB — PAY-TO ADDRESS NAME
								$data['PayToProviderAddress1'] = $segment->element('N301');	// ?
								$data['PayToProviderAddress2'] = $segment->element('N302');	// ?
								break;
							case 'IL':
								// 2010BA — SUBSCRIBER NAME
								if(array_key_exists('CurrentInsuranceType', $data)) {
									switch($data['CurrentInsuranceType']) {
										case 1:
											$data['PrimarySubscriberAddress1'] = $segment->element('N301');	// storeInsuranceData
											$data['PrimarySubscriberAddress2'] = $segment->element('N302');	// storeInsuranceData

											if($data['PrimarySubscriberRelation'] == 'self') {
												$data['PatientAddress1'] = $segment->element('N301');	// storePatientData
												$data['PatientAddress2'] = $segment->element('N302');	// storePatientData
											}
											break;
										case 2:
											$data['SecondarySubscriberAddress1'] = $segment->element('N301');	// storeInsuranceData
											$data['SecondarySubscriberAddress2'] = $segment->element('N302');	// storeInsuranceData

											if($data['SecondarySubscriberRelation'] == 'self' && (!array_key_exists('PatientAddress1', $data) || $data['PatientAddress1'] == '')) {
												$data['PatientAddress1'] = $segment->element('N301');	// storePatientData
												$data['PatientAddress2'] = $segment->element('N302');	// storePatientData
											}
											break;
										case 3:
											$data['TertiarySubscriberAddress1'] = $segment->element('N301');	// storeInsuranceData
											$data['TertiarySubscriberAddress2'] = $segment->element('N302');	// storeInsuranceData

											if($data['TertiarySubscriberRelation'] == 'self' && (!array_key_exists('PatientAddress1', $data) || $data['PatientAddress1'] == '')) {
												$data['PatientAddress1'] = $segment->element('N301');	// storePatientData
												$data['PatientAddress2'] = $segment->element('N302');	// storePatientData
											}
											break;
									}
								} else {
									echo 'Missing: CurrentInsuranceType ['.__FILE__.':'.__LINE__.']'.PHP_EOL;
								}
								break;
							case 'PR':
								// 2010BB — PAYER NAME
								if(array_key_exists('CurrentInsuranceType', $data)) {
									switch($data['CurrentInsuranceType']) {
										case 1:
											$data['PrimaryPayerAddress1'] = $segment->element('N301');	// storeAddresses
											$data['PrimaryPayerAddress2'] = $segment->element('N302');	// storeAddresses
											break;
										case 2:
											$data['SecondaryPayerAddress1'] = $segment->element('N301');	// storeAddresses
											$data['SecondaryPayerAddress2'] = $segment->element('N302');	// storeAddresses
											break;
										case 3:
											$data['TertiaryPayerAddress1'] = $segment->element('N301');	// storeAddresses
											$data['TertiaryPayerAddress2'] = $segment->element('N302');	// storeAddresses
											break;
									}
								} else {
									echo 'Missing: CurrentInsuranceType ['.__FILE__.':'.__LINE__.']'.PHP_EOL;
								}
								break;
							case 'QC':
								// 2010CA — PATIENT NAME
								$data['PatientAddress1'] = $segment->element('N301');	// storePatientData
								$data['PatientAddress2'] = $segment->element('N302');	// storePatientData
								break;
						}
						break;
					case 'N4':
						switch($data['LastNM1']->element('NM101')) {
							case '85':
								// 2010AA — BILLING PROVIDER NAME
								$data['BillingProviderCity'] = $segment->element('N401');	// storeFacility
								$data['BillingProviderState'] = $segment->element('N402');	// storeFacility
								$data['BillingProviderZip'] = $segment->element('N403');	// storeFacility
								break;
							case '87':
								// 2010AB — PAY-TO ADDRESS NAME
								$data['PayToProviderCity'] = $segment->element('N401');	// ?
								$data['PayToProviderState'] = $segment->element('N402');	// ?
								$data['PayToProviderZip'] = $segment->element('N403');	// ?
								break;
							case 'IL':
								// 2010BA — SUBSCRIBER NAME
								if(array_key_exists('CurrentInsuranceType', $data)) {
									switch($data['CurrentInsuranceType']) {
										case 1:
											$data['PrimarySubscriberCity'] = $segment->element('N401');	// storeInsuranceData
											$data['PrimarySubscriberState'] = $segment->element('N402');	// storeInsuranceData
											$data['PrimarySubscriberZip'] = $segment->element('N403');	// storeInsuranceData

											if($data['PrimarySubscriberRelation'] == 'self') {
												$data['PatientCity'] = $segment->element('N401');	// storePatientData
												$data['PatientState'] = $segment->element('N402');	// storePatientData
												$data['PatientZip'] = $segment->element('N403');	// storePatientData
											}
											break;
										case 2:
											$data['SecondarySubscriberCity'] = $segment->element('N401');	// storeInsuranceData
											$data['SecondarySubscriberState'] = $segment->element('N402');	// storeInsuranceData
											$data['SecondarySubscriberZip'] = $segment->element('N403');	// storeInsuranceData

											if($data['SecondarySubscriberRelation'] == 'self' && (!array_key_exists('PatientCity', $data) || $data['PatientCity'] == '')) {
												$data['PatientCity'] = $segment->element('N401');	// storePatientData
												$data['PatientState'] = $segment->element('N402');	// storePatientData
												$data['PatientZip'] = $segment->element('N403');	// storePatientData
											}
											break;
										case 3:
											$data['TertiarySubscriberCity'] = $segment->element('N401');	// storeInsuranceData
											$data['TertiarySubscriberState'] = $segment->element('N402');	// storeInsuranceData
											$data['TertiarySubscriberZip'] = $segment->element('N403');	// storeInsuranceData

											if($data['TertiarySubscriberRelation'] == 'self' && (!array_key_exists('PatientCity', $data) || $data['PatientCity'] == '')) {
												$data['PatientCity'] = $segment->element('N401');	// storePatientData
												$data['PatientState'] = $segment->element('N402');	// storePatientData
												$data['PatientZip'] = $segment->element('N403');	// storePatientData
											}
											break;
									}
								} else {
									echo 'Missing: CurrentInsuranceType ['.__FILE__.':'.__LINE__.']'.PHP_EOL;
								}
								break;
							case 'PR':
								// 2010BB — PAYER NAME
								if(array_key_exists('CurrentInsuranceType', $data)) {
									switch($data['CurrentInsuranceType']) {
										case 1:
											$data['PrimaryPayerCity'] = $segment->element('N401');	// storeAddress
											$data['PrimaryPayerState'] = $segment->element('N402');	// storeAddress
											$data['PrimaryPayerZip'] = $segment->element('N403');	// storeAddress
											break;
										case 2:
											$data['SecondaryPayerCity'] = $segment->element('N401');	// storeAddress
											$data['SecondaryPayerState'] = $segment->element('N402');	// storeAddress
											$data['SecondaryPayerZip'] = $segment->element('N403');	// storeAddress
											break;
										case 3:
											$data['TertiaryPayerCity'] = $segment->element('N401');	// storeAddress
											$data['TertiaryPayerState'] = $segment->element('N402');	// storeAddress
											$data['TertiaryPayerZip'] = $segment->element('N403');	// storeAddress
											break;
									}
								} else {
									echo 'Missing: CurrentInsuranceType ['.__FILE__.':'.__LINE__.']'.PHP_EOL;
								}
								break;
							case 'QC':
								// 2010CA — PATIENT NAME
								$data['PatientCity'] = $segment->element('N401');	// storePatientData
								$data['PatientState'] = $segment->element('N402');	// storePatientData
								$data['PatientZip'] = $segment->element('N403');	// storePatientData
								break;
						}
						break;
					case 'DMG':
						// 2010BA — SUBSCRIBER NAME & 2010CA — PATIENT NAME
						$data['SubDOB'] = $segment->element('DMG02');	// ?
						$data['SubSex'] = $segment->element('DMG03');	// ?

						if(array_key_exists('CurrentInsuranceType', $data)) {
							switch($data['CurrentInsuranceType']) {
								case 1:
									$data['PrimarySubscriberDOB'] = $segment->element('DMG02');	// storeInsuranceData
									$data['PrimarySubscriberSex'] = $segment->element('DMG03');	// storeInsuranceData

									if($data['PrimarySubscriberRelation'] == 'self') {
										$data['PatientDOB'] = $segment->element('DMG02');	// storePatientData
										$data['PatientSex'] = $segment->element('DMG03');	// storePatientData
									}
									break;
								case 2:
									$data['SecondarySubscriberDOB'] = $segment->element('DMG02');	// storeInsuranceData
									$data['SecondarySubscriberSex'] = $segment->element('DMG03');	// storeInsuranceData

									if($data['SecondarySubscriberRelation'] == 'self') {
										$data['PatientDOB'] = $segment->element('DMG02');	// storePatientData
										$data['PatientSex'] = $segment->element('DMG03');	// storePatientData
									}
									break;
								case 3:
									$data['TertiarySubscriberDOB'] = $segment->element('DMG02');	// storeInsuranceData
									$data['TertiarySubscriberSex'] = $segment->element('DMG03');	// storeInsuranceData

									if($data['TertiarySubscriberRelation'] == 'self') {
										$data['PatientDOB'] = $segment->element('DMG02');	// storePatientData
										$data['PatientSex'] = $segment->element('DMG03');	// storePatientData
									}
									break;
							}
						} else {
							echo 'Missing: CurrentInsuranceType ['.__FILE__.':'.__LINE__.']'.PHP_EOL;
						}
						break;
					case 'REF':
						// 2010AA — BILLING PROVIDER NAME & 2010AC — PAY-TO PLAN NAME
						$data['BillingProviderEIN'] = $segment->element('REF02');	// storeFacility
						break;
				}
			}
		} while(!is_null($segment));
	}

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
						$data['ClaimId'] = $segment->element('CLM01');	// storeFormEncounter	// storeForms	// storeBilling
						$data['ClaimAmount'] = $segment->element('CLM02');	// ?

						$data['FacilityCodeValue'] = $segment->element('CLM05')->subElement(0);	// storeFacility
						$data['FacilityCodeQualifier'] = $segment->element('CLM05')->subElement(1);	// ?
						$data['FrequencyTypeCode'] = $segment->element('CLM05')->subElement(2);	// ?

						$data['ProviderSignatureOnFile'] = $segment->element('CLM07');	// ?
						$data['ProviderAcceptAssignmentCode'] = ($segment->elementEquals('CLM08', 'A') ? 'true' : '');	// storeInsuranceData
						$data['BenefitIndicator'] = $segment->element('CLM09');	// ?
						$data['ReleaseOfInformation'] = $segment->element('CLM10');	// ?
						break;
					case 'DTP':
						// 2300 — CLAIM INFORMATION
						if($segment->elementEquals('DTP01', '431')) {
							$data['Dos2'] = $segment->element('DTP03');	// ?
						}
						break;
					case 'REF':
						// 2300 — CLAIM INFORMATION
						if($segment->elementEquals('REF01', 'EA')) {
							$data['MedicalRecordNumber'] = $segment->element('REF02');	// ?
						}
						break;
					case 'NTE':
						// 2300 — CLAIM INFORMATION
						if($segment->elementEquals('NTE01', 'ADD')) {
							$data['NoteDesc'] = $segment->element('NTE02');	// ?
						}
						break;
					case 'HI':
						// 2300 — CLAIM INFORMATION
						if($segment->elementExists('HI01') && $segment->element('HI01')->subElementEquals(0, [ 'ABK', 'BK' ])) {
							$elements = [
								'HI01', 'HI02', 'HI03',
								'HI04', 'HI05', 'HI06',
								'HI07', 'HI08', 'HI09',
								'HI10', 'HI11', 'HI12'
							];

							foreach($elements as $element) {
								if($segment->elementExists($element) && $segment->element($element)->subElementCount() > 1) {
									array_key_exists('DxType', $data) || $data['DxType'] = [];
									$data['DxType'][] = $segment->element($element)->subElement(0);	// storeBilling

									array_key_exists('Dx', $data) || $data['Dx'] = [];
									$data['Dx'][] = $segment->element($element)->subElement(1);	// storeBilling

									$data['DxCount'] = count($data['DxType']);
								}
							}
						}
						break;
				}
			}
		} while(!is_null($segment));

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

	// protected function processLoop2305(Loop\Loop2305 $loop2305, array &$data) {
	// 	$header = $loop2305->getHeader();
	// }

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
						$data['LastNM1'] = $segment;

						switch($segment->element('NM101')) {
							case 'DN':
								// 2310A — REFERRING PROVIDER NAME
								$data['ReferringType'] = $segment->element('NM102');	// ?
								$data['ReferringLastName'] = $segment->element('NM103');	// storeUser	// storeGroup
								$data['ReferringFirstName'] = $segment->element('NM104');	// storeUser	// storeGroup
								$data['ReferringMiddleName'] = $segment->element('NM105');	// storeUser	// storeGroup
								$data['ReferringSuffix'] = $segment->element('NM107');	// ?
								$data['ReferringId'] = $segment->element('NM109');	// storeUser	// storeGroup
								break;
							case '82':
								// 2310B — RENDERING PROVIDER NAME
								$data['RenderingType'] = $segment->element('NM102');	// ?
								$data['RenderingLastName'] = $segment->element('NM103');	// storeUser	// storeGroup
								$data['RenderingFirstName'] = $segment->element('NM104');	// storeUser	// storeGroup
								$data['RenderingMiddleName'] = $segment->element('NM105');	// storeUser	// storeGroup
								$data['RenderingSuffix'] = $segment->element('NM107');	// ?
								$data['RenderingId'] = $segment->element('NM109');	// storeUser	// storeGroup
								break;
							case '77':
								// 2310C — SERVICE FACILITY LOCATION NAME
								if($segment->elementEquals('NM102', '2')) {
									$data['ServiceFacilityName'] = $segment->element('NM103');	// storeUser	// storeFacility
									$data['ServiceFacilityId'] = $segment->element('NM109');	// storeUser	// storeFacility
								}
								break;
							case 'DQ':
								// 2310D — SUPERVISING PROVIDER NAME
								$data['SupervisingType'] = $segment->element('NM102');	// ?
								$data['SupervisingLastName'] = $segment->element('NM103');	// ?
								$data['SupervisingFirstName'] = $segment->element('NM104');	// ?
								$data['SupervisingMiddleName'] = $segment->element('NM105');	// ?
								$data['SupervisingSuffix'] = $segment->element('NM107');	// ?
								$data['SupervisingId'] = $segment->element('NM109');	// ?
								break;
						}
						break;
					case 'N3':
						// 2310C — SERVICE FACILITY LOCATION NAME
						if($data['LastNM1']->elementEquals('NM101', '77')) {
							$data['ServiceFacilityAddress1'] = $segment->element('N301');	// storeFacility
							$data['ServiceFacilityAddress2'] = $segment->element('N302');	// storeFacility
						}
						break;
					case 'N4':
						// 2310C — SERVICE FACILITY LOCATION NAME
						if($data['LastNM1']->elementEquals('NM101', '77')) {
							$data['ServiceFacilityCity'] = $segment->element('N401');	// storeFacility
							$data['ServiceFacilityState'] = $segment->element('N402');	// storeFacility
							$data['ServiceFacilityZip'] = $segment->element('N403');	// storeFacility
						}
						break;
					case 'PRV':
						// 2310B — RENDERING PROVIDER NAME
						if($segment->elementEquals('PRV01', 'PE')) {
							$data['RenderingTaxonomy'] = $segment->element('PRV03');	// storeUser
						}
						break;
				}
			}
		} while(!is_null($segment));
	}

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
						switch($segment->element('SBR01')) {
							case 'P':
								$data['CurrentInsuranceType'] = 1;
								$data['PrimarySubscriberRelation'] = ($segment->elementEquals('SBR02', '18') ? 'self' : '');	// storeInsuranceData
								$data['PrimaryPolicy'] = $segment->element('SBR03');	// storeInsuranceData
								$data['PrimaryPlanName'] = $segment->element('SBR04');	// storeInsuranceData
								break;
							case 'S':
								$data['CurrentInsuranceType'] = 2;
								$data['SecondarySubscriberRelation'] = ($segment->elementEquals('SBR02', '18') ? 'self' : '');	// storeInsuranceData
								$data['SecondaryPolicy'] = $segment->element('SBR03');	// storeInsuranceData
								$data['SecondaryPlanName'] = $segment->element('SBR04');	// storeInsuranceData
								break;
							case 'T':
								$data['CurrentInsuranceType'] = 3;
								$data['TertiarySubscriberRelation'] = ($segment->elementEquals('SBR02', '18') ? 'self' : '');	// storeInsuranceData
								$data['TertiaryPolicy'] = $segment->element('SBR03');	// storeInsuranceData
								$data['TertiaryPlanName'] = $segment->element('SBR04');	// storeInsuranceData
								break;
						}
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
				switch ($segment->getName()) {
					case 'NM1':
						$data['LastNM1'] = $segment;

						switch($segment->element('NM101')) {
							case 'IL':
								// 2330A — OTHER SUBSCRIBER NAME
								if(array_key_exists('CurrentInsuranceType', $data)) {
									switch($data['CurrentInsuranceType']) {
										case 1:
											$data['PrimarySubscriberLastName'] = $segment->element('NM103');	// storeInsuranceData
											$data['PrimarySubscriberFirstName'] = $segment->element('NM104');	// storeInsuranceData
											$data['PrimarySubscriberMiddleName'] = $segment->element('NM105');	// storeInsuranceData
											$data['PrimarySubscriberSuffix'] = $segment->element('NM107');	// ?
											$data['PrimarySubscriberId'] = $segment->element('NM109');	// storeInsuranceData

											if($data['PrimarySubscriberRelation'] == 'self') {
												$data['PatientLastName'] = $segment->element('NM103');	// storePatientData
												$data['PatientFirstName'] = $segment->element('NM104');	// storePatientData
												$data['PatientMiddleName'] = $segment->element('NM105');	// storePatientData
												$data['PatientSuffix'] = $segment->element('NM107');	// ?
												$data['PatientId'] = $segment->element('NM109');	// ?
											}
											break;
										case 2:
											$data['SecondarySubscriberLastName'] = $segment->element('NM103');	// storeInsuranceData
											$data['SecondarySubscriberFirstName'] = $segment->element('NM104');	// storeInsuranceData
											$data['SecondarySubscriberMiddleName'] = $segment->element('NM105');	// storeInsuranceData
											$data['SecondarySubscriberSuffix'] = $segment->element('NM107');	// ?
											$data['SecondarySubscriberId'] = $segment->element('NM109');	// storeInsuranceData

											if($data['SecondarySubscriberRelation'] == 'self' && (!array_key_exists('PatientLastName', $data) || $data['PatientLastName'] == '')) {
												$data['PatientLastName'] = $segment->element('NM103');	// storePatientData
												$data['PatientFirstName'] = $segment->element('NM104');	// storePatientData
												$data['PatientMiddleName'] = $segment->element('NM105');	// storePatientData
												$data['PatientSuffix'] = $segment->element('NM107');	// ?
												$data['PatientId'] = $segment->element('NM109');	// ?
											}
											break;
										case 3:
											$data['TertiarySubscriberLastName'] = $segment->element('NM103');	// storeInsuranceData
											$data['TertiarySubscriberFirstName'] = $segment->element('NM104');	// storeInsuranceData
											$data['TertiarySubscriberMiddleName'] = $segment->element('NM105');	// storeInsuranceData
											$data['TertiarySubscriberSuffix'] = $segment->element('NM107');	// ?
											$data['TertiarySubscriberId'] = $segment->element('NM109');	// storeInsuranceData

											if($data['TertiarySubscriberRelation'] == 'self' && (!array_key_exists('PatientLastName', $data) || $data['PatientLastName'] == '')) {
												$data['PatientLastName'] = $segment->element('NM103');	// storePatientData
												$data['PatientFirstName'] = $segment->element('NM104');	// storePatientData
												$data['PatientMiddleName'] = $segment->element('NM105');	// storePatientData
												$data['PatientSuffix'] = $segment->element('NM107');	// ?
												$data['PatientId'] = $segment->element('NM109');	// ?
											}
											break;
									}
								} else {
									echo 'Missing: CurrentInsuranceType ['.__FILE__.':'.__LINE__.']'.PHP_EOL;
								}
								break;
							case 'PR':
								// 2330B — OTHER PAYER NAME
								if(array_key_exists('CurrentInsuranceType', $data)) {
									switch($data['CurrentInsuranceType']) {
										case 1:
											$data['PrimaryPayerName'] = $segment->element('NM103');	// storeInsuranceCompany
											$data['PrimaryPayerId'] = $segment->element('NM109');	// storeInsuranceCompany
											break;
										case 2:
											$data['SecondaryPayerName'] = $segment->element('NM103');	// storeInsuranceCompany
											$data['SecondaryPayerId'] = $segment->element('NM109');	// storeInsuranceCompany
											break;
										case 3:
											$data['TertiaryPayerName'] = $segment->element('NM103');	// storeInsuranceCompany
											$data['TertiaryPayerId'] = $segment->element('NM109');	// storeInsuranceCompany
											break;
									}
								} else {
									echo 'Missing: CurrentInsuranceType ['.__FILE__.':'.__LINE__.']'.PHP_EOL;
								}
								break;
							case 'DN':
								// 2330C — OTHER PAYER REFERRING PROVIDER
								$data['ReferringType'] = $segment->element('NM102');	// ?
								$data['ReferringLastName'] = $segment->element('NM103');	// storeUser	// storeGroup
								$data['ReferringFirstName'] = $segment->element('NM104');	// storeUser	// storeGroup
								$data['ReferringMiddleName'] = $segment->element('NM105');	// storeUser
								$data['ReferringSuffix'] = $segment->element('NM107');	// ?
								$data['ReferringId'] = $segment->element('NM109');	// storeUser	// storeGroup
								break;
							case '82':
								// 2330D — OTHER PAYER RENDERING PROVIDER
								$data['RenderingType'] = $segment->element('NM102');	// ?
								$data['RenderingLastName'] = $segment->element('NM103');	// storeUser	// storeGroup
								$data['RenderingFirstName'] = $segment->element('NM104');	// storeUser	// storeGroup
								$data['RenderingMiddleName'] = $segment->element('NM105');	// storeUser
								$data['RenderingSuffix'] = $segment->element('NM107');	// ?
								$data['RenderingId'] = $segment->element('NM109');	// storeUser	// storeGroup
								break;
							case '77':
								// 2330E — OTHER PAYER SERVICE FACILITY LOCATION
								if($segment->elementEquals('NM102', '2')) {
									$data['ServiceFacilityName'] = $segment->element('NM103');	// storeFacility	// storeUser
									$data['ServiceFacilityId'] = $segment->element('NM109');	// storeFacility
								}
								break;
							case 'DQ':
								// 2330F — OTHER PAYER SUPERVISING PROVIDER
								$data['SupervisingType'] = $segment->element('NM102');	// ?
								$data['SupervisingLastName'] = $segment->element('NM103');	// ?
								$data['SupervisingFirstName'] = $segment->element('NM104');	// ?
								$data['SupervisingMiddleName'] = $segment->element('NM105');	// ?
								$data['SupervisingSuffix'] = $segment->element('NM107');	// ?
								$data['SupervisingId'] = $segment->element('NM109');	// ?
								break;
							case '85':
								// 2330G — OTHER PAYER BILLING PROVIDER
								$data['BillingType'] = $segment->element('NM102');	// ?
								$data['BillingProviderLastName'] = $segment->element('NM103');	// storeFacility	// storeUser	// storeGroup
								$data['BillingProviderFirstName'] = $segment->element('NM104');	// storeUser	// storeGroup
								$data['BillingProviderMiddleName'] = $segment->element('NM105');	// storeUser
								$data['BillingProviderSuffix'] = $segment->element('NM107');	// ?
								$data['BillingProviderId'] = $segment->element('NM109');	// storeFacility	// storeUser	// storeGroup
								break;
						}
						break;
					case 'N3':
						switch($data['LastNM1']->element('NM101')) {
							case 'IL':
								// 2330A — OTHER SUBSCRIBER NAME
								if(array_key_exists('CurrentInsuranceType', $data)) {
									switch($data['CurrentInsuranceType']) {
										case 2:
											$data['SecondarySubscriberAddress1'] = $segment->element('N301');	// storeInsuranceData
											$data['SecondarySubscriberAddress2'] = $segment->element('N302');	// storeInsuranceData

											if($data['SecondarySubscriberRelation'] == 'self' && (!array_key_exists('PatientAddress1', $data) || $data['PatientAddress1'] == '')) {
												$data['PatientAddress1'] = $segment->element('N301');	// storePatientData
												$data['PatientAddress2'] = $segment->element('N302');	// storePatientData
											}
											break;
										case 3:
											$data['TertiarySubscriberAddress1'] = $segment->element('N301');	// storeInsuranceData
											$data['TertiarySubscriberAddress2'] = $segment->element('N302');	// storeInsuranceData

											if($data['TertiarySubscriberRelation'] == 'self' && (!array_key_exists('PatientAddress1', $data) || $data['PatientAddress1'] == '')) {
												$data['PatientAddress1'] = $segment->element('N301');	// storePatientData
												$data['PatientAddress2'] = $segment->element('N302');	// storePatientData
											}
											break;
									}
								} else {
									echo 'Missing: CurrentInsuranceType ['.__FILE__.':'.__LINE__.']'.PHP_EOL;
								}
								break;
							case 'PR':
								// 2330B — OTHER PAYER NAME
								if(array_key_exists('CurrentInsuranceType', $data)) {
									switch($data['CurrentInsuranceType']) {
										case 1:
											$data['PrimaryPayerAddress1'] = $segment->element('N301');	// storeAddresses
											$data['PrimaryPayerAddress2'] = $segment->element('N302');	// storeAddresses
											break;
										case 2:
											$data['SecondaryPayerAddress1'] = $segment->element('N301');	// storeAddresses
											$data['SecondaryPayerAddress2'] = $segment->element('N302');	// storeAddresses
											break;
										case 3:
											$data['TertiaryPayerAddress1'] = $segment->element('N301');	// storeAddresses
											$data['TertiaryPayerAddress2'] = $segment->element('N302');	// storeAddresses
											break;
									}
								} else {
									echo 'Missing: CurrentInsuranceType ['.__FILE__.':'.__LINE__.']'.PHP_EOL;
								}
								break;
							case '77':
								// 2330E — OTHER PAYER SERVICE FACILITY LOCATION
								$data['ServiceFacilityAddress1'] = $segment->element('N301');	// storeFacility
								$data['ServiceFacilityAddress2'] = $segment->element('N302');	// storeFacility
								break;
							case '85':
								// 2330G — OTHER PAYER BILLING PROVIDER
								$data['BillingProviderAddress1'] = $segment->element('N301');	// storeFacility
								$data['BillingProviderAddress2'] = $segment->element('N302');	// storeFacility
								break;
						}
						break;
					case 'N4':
						switch($data['LastNM1']->element('NM101')) {
							case 'IL':
								// 2330A — OTHER SUBSCRIBER NAME
								if(array_key_exists('CurrentInsuranceType', $data)) {
									switch($data['CurrentInsuranceType']) {
										case 1:
											$data['PrimarySubscriberCity'] = $segment->element('N401');	// storeInsuranceData
											$data['PrimarySubscriberState'] = $segment->element('N402');	// storeInsuranceData
											$data['PrimarySubscriberZip'] = $segment->element('N403');	// storeInsuranceData

											if($data['PrimarySubscriberRelation'] == 'self') {
												$data['PatientCity'] = $segment->element('N401');	// storePatientData
												$data['PatientState'] = $segment->element('N402');	// storePatientData
												$data['PatientZip'] = $segment->element('N403');	// storePatientData
											}
											break;
										case 2:
											$data['SecondarySubscriberCity'] = $segment->element('N401');	// storeInsuranceData
											$data['SecondarySubscriberState'] = $segment->element('N402');	// storeInsuranceData
											$data['SecondarySubscriberZip'] = $segment->element('N403');	// storeInsuranceData

											if($data['SecondarySubscriberRelation'] == 'self' && (!array_key_exists('PatientCity', $data) || $data['PatientCity'] == '')) {
												$data['PatientCity'] = $segment->element('N401');	// storePatientData
												$data['PatientState'] = $segment->element('N402');	// storePatientData
												$data['PatientZip'] = $segment->element('N403');	// storePatientData
											}
											break;
										case 3:
											$data['TertiarySubscriberCity'] = $segment->element('N401');	// storeInsuranceData
											$data['TertiarySubscriberState'] = $segment->element('N402');	// storeInsuranceData
											$data['TertiarySubscriberZip'] = $segment->element('N403');	// storeInsuranceData

											if($data['TertiarySubscriberRelation'] == 'self' && (!array_key_exists('PatientCity', $data) || $data['PatientCity'] == '')) {
												$data['PatientCity'] = $segment->element('N401');	// storePatientData
												$data['PatientState'] = $segment->element('N402');	// storePatientData
												$data['PatientZip'] = $segment->element('N403');	// storePatientData
											}
											break;
									}
								} else {
									echo 'Missing: CurrentInsuranceType ['.__FILE__.':'.__LINE__.']'.PHP_EOL;
								}
								break;
							case 'PR':
								// 2330B — OTHER PAYER NAME
								if(array_key_exists('CurrentInsuranceType', $data)) {
									switch($data['CurrentInsuranceType']) {
										case 1:
											$data['PrimaryPayerCity'] = $segment->element('N401');	// storeAddress
											$data['PrimaryPayerState'] = $segment->element('N402');	// storeAddress
											$data['PrimaryPayerZip'] = $segment->element('N403');	// storeAddress
											break;
										case 2:
											$data['SecondaryPayerCity'] = $segment->element('N401');	// storeAddress
											$data['SecondaryPayerState'] = $segment->element('N402');	// storeAddress
											$data['SecondaryPayerZip'] = $segment->element('N403');	// storeAddress
											break;
										case 3:
											$data['TertiaryPayerCity'] = $segment->element('N401');	// storeAddress
											$data['TertiaryPayerState'] = $segment->element('N402');	// storeAddress
											$data['TertiaryPayerZip'] = $segment->element('N403');	// storeAddress
											break;
									}
								} else {
									echo 'Missing: CurrentInsuranceType ['.__FILE__.':'.__LINE__.']'.PHP_EOL;
								}
								break;
							case '77':
								// 2330E — OTHER PAYER SERVICE FACILITY LOCATION
								$data['ServiceFacilityCity'] = $segment->element('N401');	// storeFacility
								$data['ServiceFacilityState'] = $segment->element('N402');	// storeFacility
								$data['ServiceFacilityZip'] = $segment->element('N403');	// storeFacility
								break;
							case '85':
								// 2330G — OTHER PAYER BILLING PROVIDER
								$data['BillingProviderCity'] = $segment->element('N401');	// storeFacility
								$data['BillingProviderState'] = $segment->element('N402');	// storeFacility
								$data['BillingProviderZip'] = $segment->element('N403');	// storeFacility
								break;
						}
						break;
					case 'REF':
						// 2330B — OTHER PAYER NAME
						if($segment->elementEquals('REF01', 'EI')) {
							$data['BillingProviderEIN'] = $segment->element('REF02');	// storeFacility
						}
						break;
				}
			}
		} while(!is_null($segment));
	}

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
						if($segment->elementExists('SV101') && $segment->element('SV101')->subElementEquals(0, [ 'HC', 'WK' ])) {
							array_key_exists('Tx', $data) || $data['Tx'] = [];
							$data['Tx'][] = $segment->element('SV101')->subElement(1);	// storeBilling

							if($segment->element('SV101')->subElementExists(2)) {
								array_key_exists('TxMod', $data) || $data['TxMod'] = [];
								$data['TxMod'][] = $segment->element('SV101')->subElement(2);	// storeBilling
							}

							array_key_exists('TxAmount', $data) || $data['TxAmount'] = [];
							$data['TxAmount'][] = $segment->element('SV102');	// storeBilling

							array_key_exists('TxUnits', $data) || $data['TxUnits'] = [];
							$data['TxUnits'][] = $segment->element('SV104');	// storeBilling

							$data['TxCount'] = count($data['Tx']);
						}
						break;
					case 'DTP':
						// 2400 — SERVICE LINE NUMBER
						if($segment->elementEquals('DTP01', '472')) {
							$data['Dos1'] = $segment->element('DTP02').rand(10, 20).rand(10, 59).rand(10, 59);	// storePatientData
						}
						break;
					case 'NTE':
						// 2400 — SERVICE LINE NUMBER
						if($segment->elementEquals('NTE01', 'ADD')) {
							$data['NoteDesc'] = $segment->element('NTE02');	// ?
						}
						break;
				}
			}
		} while(!is_null($segment));

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

	// protected function processLoop2410(Loop\Loop2410 $loop2410, array &$data) {
	// 	$header = $loop2410->getHeader();
	// }

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
						$data['LastNM1'] = $segment;

						switch($segment->element('NM101')) {
							case '82':
								// 2420A — RENDERING PROVIDER NAME
								$data['RenderingType'] = $segment->element('NM102');	// ?
								$data['RenderingLastName'] = $segment->element('NM103');	// storeUser	// storeGroup
								$data['RenderingFirstName'] = $segment->element('NM104');	// storeUser	// storeGroup
								$data['RenderingMiddleName'] = $segment->element('NM105');	// storeUser
								$data['RenderingSuffix'] = $segment->element('NM107');	// ?
								$data['RenderingId'] = $segment->element('NM109');	// storeUser	// storeGroup
								break;
							case '77':
								// 2420C — SERVICE FACILITY LOCATION NAME
								if($segment->elementEquals('NM102', '2')) {
									$data['ServiceFacilityName'] = $segment->element('NM103');	// storeFacility	// storeUser
									$data['ServiceFacilityId'] = $segment->element('NM109');	// storeFacility
								}
								break;
							case 'DQ':
								// 2420D — SUPERVISING PROVIDER NAME
								$data['SupervisingType'] = $segment->element('NM102');	// ?
								$data['SupervisingLastName'] = $segment->element('NM103');	// ?
								$data['SupervisingFirstName'] = $segment->element('NM104');	// ?
								$data['SupervisingMiddleName'] = $segment->element('NM105');	// ?
								$data['SupervisingSuffix'] = $segment->element('NM107');	// ?
								$data['SupervisingId'] = $segment->element('NM109');	// ?
								break;
							case 'DK':
								// 2420E — ORDERING PROVIDER NAME
								$data['OrderingType'] = $segment->element('NM102');	// ?
								$data['OrderingLastName'] = $segment->element('NM103');	// storeUser	// storeGroup
								$data['OrderingFirstName'] = $segment->element('NM104');	// storeUser	// storeGroup
								$data['OrderingMiddleName'] = $segment->element('NM105');	// storeUser
								$data['OrderingSuffix'] = $segment->element('NM107');	// ?
								$data['OrderingId'] = $segment->element('NM109');	// storeUser	// storeGroup
								break;
							case 'DN':
								// 2420F — REFERRING PROVIDER NAME
								$data['ReferringType'] = $segment->element('NM102');	// ?
								$data['ReferringLastName'] = $segment->element('NM103');	// storeUser	// storeGroup
								$data['ReferringFirstName'] = $segment->element('NM104');	// storeUser	// storeGroup
								$data['ReferringMiddleName'] = $segment->element('NM105');	// storeUser
								$data['ReferringSuffix'] = $segment->element('NM107');	// ?
								$data['ReferringId'] = $segment->element('NM109');	// storeUser	// storeGroup
								break;
						}
						break;
					case 'N3':
						switch($data['LastNM1']->element('NM101')) {
							case '77':
								// 2420C — SERVICE FACILITY LOCATION NAME
								$data['ServiceFacilityAddress1'] = $segment->element('N301');	// storeFacility
								$data['ServiceFacilityAddress2'] = $segment->element('N302');	// storeFacility
								break;
							case 'DK':
								// 2420E — ORDERING PROVIDER NAME
								$data['OrderingAddress1'] = $segment->element('N301');	// ?
								$data['OrderingAddress2'] = $segment->element('N302');	// ?
								break;
						}
						break;
					case 'N4':
						switch($data['LastNM1']->element('NM101')) {
							case '77':
								// 2420C — SERVICE FACILITY LOCATION NAME
								$data['ServiceFacilityCity'] = $segment->element('N401');	// storeFacility
								$data['ServiceFacilityState'] = $segment->element('N402');	// storeFacility
								$data['ServiceFacilityZip'] = $segment->element('N403');	// storeFacility
								break;
							case 'DK':
								// 2420E — ORDERING PROVIDER NAME
								$data['OrderingCity'] = $segment->element('N401');	// ?
								$data['OrderingState'] = $segment->element('N402');	// ?
								$data['OrderingZip'] = $segment->element('N403');	// ?
								break;
						}
						break;
					case 'PRV':
						// 2420A — RENDERING PROVIDER NAME
						if($segment->elementEquals('PRV01', 'PE') &&
							$segment->elementEquals('PRV02', 'PXC')
						) {
							$data['RenderingTaxonomy'] = $segment->element('PRV03');	// storeUsers
						}
						break;
				}
			}
		} while(!is_null($segment));
	}

	// protected function processLoop2430(Loop\Loop2430 $loop2430, array &$data) {
	// 	$header = $loop2430->getHeader();
	// }

	// protected function processLoop2440(Loop\Loop2440 $loop2440, array &$data) {
	// 	$header = $loop2440->getHeader();
	// }

}