<?php

namespace SunCoastConnection\ClaimsToOEMR\Database;

use \SunCoastConnection\ClaimsToOEMR\Database,
	\SunCoastConnection\ClaimsToOEMR\X12N837,
	\SunCoastConnection\ClaimsToOEMR\X12N837\Envelop\FunctionalGroup,
	\SunCoastConnection\ClaimsToOEMR\X12N837\Envelop\InterchangeControl,
	\SunCoastConnection\ClaimsToOEMR\X12N837\Envelop\TransactionSet,
	\SunCoastConnection\ClaimsToOEMR\X12N837\Loop\Loop1000,
	\SunCoastConnection\ClaimsToOEMR\X12N837\Loop\Loop2000,
	\SunCoastConnection\ClaimsToOEMR\X12N837\Loop\Loop2010,
	\SunCoastConnection\ClaimsToOEMR\X12N837\Loop\Loop2300,
	\SunCoastConnection\ClaimsToOEMR\X12N837\Loop\Loop2305,
	\SunCoastConnection\ClaimsToOEMR\X12N837\Loop\Loop2310,
	\SunCoastConnection\ClaimsToOEMR\X12N837\Loop\Loop2320,
	\SunCoastConnection\ClaimsToOEMR\X12N837\Loop\Loop2330,
	\SunCoastConnection\ClaimsToOEMR\X12N837\Loop\Loop2400,
	\SunCoastConnection\ClaimsToOEMR\X12N837\Loop\Loop2410,
	\SunCoastConnection\ClaimsToOEMR\X12N837\Loop\Loop2420,
	\SunCoastConnection\ClaimsToOEMR\X12N837\Loop\Loop2430,
	\SunCoastConnection\ClaimsToOEMR\X12N837\Loop\Loop2440,
	\SunCoastConnection\ClaimsToOEMR\X12N837\Segment\BHT,
	\SunCoastConnection\ClaimsToOEMR\X12N837\Segment\CLM,
	\SunCoastConnection\ClaimsToOEMR\X12N837\Segment\DMG,
	\SunCoastConnection\ClaimsToOEMR\X12N837\Segment\DTP,
	\SunCoastConnection\ClaimsToOEMR\X12N837\Segment\GS,
	\SunCoastConnection\ClaimsToOEMR\X12N837\Segment\HI,
	\SunCoastConnection\ClaimsToOEMR\X12N837\Segment\ISA,
	\SunCoastConnection\ClaimsToOEMR\X12N837\Segment\N3,
	\SunCoastConnection\ClaimsToOEMR\X12N837\Segment\N4,
	\SunCoastConnection\ClaimsToOEMR\X12N837\Segment\NM1,
	\SunCoastConnection\ClaimsToOEMR\X12N837\Segment\NTE,
	\SunCoastConnection\ClaimsToOEMR\X12N837\Segment\PAT,
	\SunCoastConnection\ClaimsToOEMR\X12N837\Segment\PRV,
	\SunCoastConnection\ClaimsToOEMR\X12N837\Segment\REF,
	\SunCoastConnection\ClaimsToOEMR\X12N837\Segment\SBR,
	\SunCoastConnection\ClaimsToOEMR\X12N837\Segment\ST,
	\SunCoastConnection\ClaimsToOEMR\X12N837\Segment\SV1;

class Cache {

	protected $database;

	static public function getNew(Database $database) {
		return new static($database);
	}

	public function __construct(Database $database) {
		$this->database = $database;
	}

	protected function getDatabase() {
		return $this->database;
	}

	public function processDocument(X12N837 $document) {
		echo " - Document Length:\t".strlen($document).PHP_EOL;

		echo " - Function:\t\t".__FUNCTION__.PHP_EOL;

		$descendant = $document->getDescendant();

		if(is_array($descendant)) {
			foreach($descendant as $section) {
				$this->processInterchangeControl($section);
			}
		}
	}

	protected function findNextSegment(array &$segmentGroup, array $segmentMatches = null, $reset = false) {
		if($reset) {
			reset($segmentGroup);
		}

		do {
			$segment = current($segmentGroup);

			if($segment && (is_null($segmentMatches) || in_array(get_class($segment), $segmentMatches))) {
				next($segmentGroup);

				echo " - Segment:\t\t".$segment->getName(true).PHP_EOL;

				return $segment;
			}
		} while(next($segmentGroup));
	}

	protected function removeData(&$data, $keys) {
		foreach($keys as $key) {
			if(array_key_exists($key, $data)) {
				unset($data[$key]);
			}
		}
	}

	protected function processInterchangeControl(InterchangeControl $interchangeControl) {
		echo " - Function:\t\t".__FUNCTION__.PHP_EOL;

		$data = [];

		$header = $interchangeControl->getHeader();

		$data['ISA'] = $this->findNextSegment(
			$header,
			[ ISA::class ]
		);

		$descendant = $interchangeControl->getDescendant();

		if(is_array($descendant)) {
			foreach($descendant as $section) {
				$this->processFunctionalGroup($section, $data);
			}
		}
	}

	protected function processFunctionalGroup(FunctionalGroup $functionalGroup, array &$data) {
		echo " - Function:\t\t".__FUNCTION__.PHP_EOL;

		$header = $functionalGroup->getHeader();

		$data['GS'] = $this->findNextSegment(
			$header,
			[ GS::class ]
		);

		$descendant = $functionalGroup->getDescendant();

		if(is_array($descendant)) {
			foreach($descendant as $section) {
				$this->processTransactionSet($section, $data);
			}
		}
	}

	protected function processTransactionSet(TransactionSet $transactionSet, array &$data) {
		echo " - Function:\t\t".__FUNCTION__.PHP_EOL;

		$header = $transactionSet->getHeader();

		$segment = $this->findNextSegment(
			$header,
			[ BHT::class ]
		);

		if($segment && !$segment->elementEquals('BHT06', 'RP')) {
			$descendant = $transactionSet->getDescendant();

			if(is_array($descendant)) {
				foreach($descendant as $section) {
					switch(get_class($section)) {
						case Loop1000::class:
							$this->processLoop1000($section, $data);
							break;

						case Loop2000::class:
							$this->processLoop2000($section, $data);
							break;
					}
				}
			}
		}
	}

	protected function processLoop1000(Loop1000 $loop1000, array &$data) {
		echo " - Function:\t\t".__FUNCTION__.PHP_EOL;

		$header = $loop1000->getHeader();

		do {
			$segment = $this->findNextSegment(
				$header,
				[
					NM1::class,
					N3::class,
					N4::class,
				]
			);

			if($segment) {
				switch(get_class($segment)) {
					case NM1::class:
						$data['LastNM1'] = $segment;

						switch($segment->element('NM101')) {
							case '40':
								// 1000B — RECEIVER NAME
								// storeX12Partners
								if($segment->elementEquals('NM102', '2')) {
									$data['CurrentX12Partner'] = $this->getDatabase()->storeX12Partner([
										'name' => $segment->element('NM103'),
										'id_number' => $segment->element('NM109'),
										'x12_sender_id' => $data['ISA']->element('ISA06'),
										'x12_receiver_id' => $data['ISA']->element('ISA08'),
										'x12_version' => $data['GS']->element('GS08'),	// '005010X098A1'
										'x12_isa01' => $data['ISA']->element('ISA01'),
										'x12_isa02' => $data['ISA']->element('ISA02'),
										'x12_isa03' => $data['ISA']->element('ISA03'),
										'x12_isa04' => $data['ISA']->element('ISA04'),
										'x12_isa05' => $data['ISA']->element('ISA05'),
										'x12_isa07' => $data['ISA']->element('ISA07'),
										'x12_isa14' => $data['ISA']->element('ISA14'),
										'x12_isa15' => $data['ISA']->element('ISA15'),
										'x12_gs02' => $data['GS']->element('GS02'),
										'x12_gs03' => $data['GS']->element('GS03'),
									]);

									$this->removeData($data, [ 'ISA', 'GS' ]);
								}
								break;
							case '41':
								// 1000A — SUBMITTER NAME
								$data['SubmitterName'] = $segment->element('NM103');	// ?
								$data['SubmitterId'] = $segment->element('NM109');		// ?
								break;
						}
						break;
					case N3::class:
						// if($data['LastNM1']->elementEquals('NM101', '41')) {
						// 	$data['SubmitterAddress1'] = $segment->element('N301');	// ?
						// 	$data['SubmitterAddress2'] = $segment->element('N302');	// ?
						// }
						break;
					case N4::class:
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

	protected function processLoop2000(Loop2000 $loop2000, array &$data) {
		echo " - Function:\t\t".__FUNCTION__.PHP_EOL;

		$header = $loop2000->getHeader();

		do {
			$segment = $this->findNextSegment(
				$header,
				[
					PRV::class,
					SBR::class,
					PAT::class,
				]
			);

			if($segment) {
				switch(get_class($segment)) {
					case PRV::class:
						// 2000A — BILLING PROVIDER HIERARCHICAL LEVEL
						// storeUser
						if($segment->elementEquals('PRV01', 'BI')) {
							$data['BillingProviderTaxonomy'] = $segment->element('PRV03');
						}
						break;
					case SBR::class:
						// 2000B — SUBSCRIBER HIERARCHICAL LEVEL
						switch($segment->element('SBR01')) {
							case 'P':
								// storeInsuranceData
								$data['CurrentInsuranceType'] = 1;

								$data['PrimarySubscriberRelation'] = ($segment->elementEquals('SBR02', '18') ? 'self' : '');
								$data['PrimaryPolicy'] = $segment->element('SBR03');
								$data['PrimaryPlanName'] = $segment->element('SBR04');
								break;
							case 'S':
								// storeInsuranceData
								$data['CurrentInsuranceType'] = 2;

								$data['SecondarySubscriberRelation'] = ($segment->elementEquals('SBR02', '18') ? 'self' : '');
								$data['SecondaryPolicy'] = $segment->element('SBR03');
								$data['SecondaryPlanName'] = $segment->element('SBR04');
								break;
							case 'T':
								// storeInsuranceData
								$data['CurrentInsuranceType'] = 3;

								$data['TertiarySubscriberRelation'] = ($segment->elementEquals('SBR02', '18') ? 'self' : '');
								$data['TertiaryPolicy'] = $segment->element('SBR03');
								$data['TertiaryPlanName'] = $segment->element('SBR04');
								break;
						}
						break;
					case PAT::class:
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
						}
						break;
				}
			}
		} while(!is_null($segment));

		$descendant = $loop2000->getDescendant();

		if(is_array($descendant)) {
			foreach($descendant as $section) {
				switch(get_class($section)) {
					case Loop2010::class:
						$this->processLoop2010($section, $data);
						break;

					case Loop2300::class:
						$this->processLoop2300($section, $data);
						break;
				}
			}
		}
	}

	protected function processLoop2010(Loop2010 $loop2010, array &$data) {
		echo " - Function:\t\t".__FUNCTION__.PHP_EOL;

		$header = $loop2010->getHeader();

		do {
			$segment = $this->findNextSegment(
				$header,
				[
					NM1::class,
					N3::class,
					N4::class,
					DMG::class,
					REF::class,
				]
			);

			if($segment) {
				switch(get_class($segment)) {
					case NM1::class:
						$data['LastNM1'] = $segment;

						switch($segment->element('NM101')) {
							case '85':
								// 2010AA — BILLING PROVIDER NAME
								// storeFacility
								// storeUser
								// storeGroup
								// storeFormEncounter
								$data['BillingType'] = $segment->element('NM102');	// ?
								$data['BillingProviderlastName'] = $segment->element('NM103');
								$data['BillingProviderFirstName'] = $segment->element('NM104');
								$data['BillingProviderMiddleName'] = $segment->element('NM105');
								$data['BillingProviderSuffix'] = $segment->element('NM107');	// ?
								$data['BillingProviderId'] = $segment->element('NM109');
								break;
							case '87':
								// 2010AB — PAY-TO ADDRESS NAME
								// storeUser
								// storeGroup
								$data['PayToType'] = $segment->element('NM101');	// ?
								$data['PayToProviderLastName'] = $segment->element('NM102');
								$data['PayToProviderFirstName'] = $segment->element('NM103');
								$data['PayToProviderMiddleName'] = $segment->element('NM104');
								$data['PayToProviderSuffix'] = $segment->element('NM106');	// ?
								$data['PayToProviderId'] = $segment->element('NM108');
								break;
							case 'IL':
								// 2010BA — SUBSCRIBER NAME
								switch($data['CurrentInsuranceType']) {
									case 1:
										// storeInsuranceData
										$data['PrimarySubscriberLastName'] = $segment->element('NM102');
										$data['PrimarySubscriberFirstName'] = $segment->element('NM103');
										$data['PrimarySubscriberMiddleName'] = $segment->element('NM104');
										$data['PrimarySubscriberSuffix'] = $segment->element('NM106');	// ?
										$data['PrimarySubscriberId'] = $segment->element('NM108');

										if($data['PrimarySubscriberRelation'] == 'self') {
											// storePatientData
											$data['PatientLastName'] = $segment->element('NM102');
											$data['PatientFirstName'] = $segment->element('NM103');
											$data['PatientMiddleName'] = $segment->element('NM104');
											$data['PatientSuffix'] = $segment->element('NM106');	// ?
											$data['PatientId'] = $segment->element('NM108');	// ?
										}
										break;
									case 2:
										// storeInsuranceData
										$data['SecondarySubscriberLastName'] = $segment->element('NM102');
										$data['SecondarySubscriberFirstName'] = $segment->element('NM103');
										$data['SecondarySubscriberMiddleName'] = $segment->element('NM104');
										$data['SecondarySubscriberSuffix'] = $segment->element('NM106');
										$data['SecondarySubscriberId'] = $segment->element('NM108');

										if($data['SecondarySubscriberRelation'] == 'self' && $data['PatientLastName'] == '') {
											// storePatientData
											$data['PatientLastName'] = $segment->element('NM102');
											$data['PatientFirstName'] = $segment->element('NM103');
											$data['PatientMiddleName'] = $segment->element('NM104');
											$data['PatientSuffix'] = $segment->element('NM106');	// ?
											$data['PatientId'] = $segment->element('NM108');	// ?
										}
										break;
									case 3:
										// storeInsuranceData
										$data['TertiarySubscriberLastName'] = $segment->element('NM102');
										$data['TertiarySubscriberFirstName'] = $segment->element('NM103');
										$data['TertiarySubscriberMiddleName'] = $segment->element('NM104');
										$data['TertiarySubscriberSuffix'] = $segment->element('NM106');
										$data['TertiarySubscriberId'] = $segment->element('NM108');

										if($data['TertiarySubscriberRelation'] == 'self' && $data['PatientLastName'] == '') {
											// storePatientData
											$data['PatientLastName'] = $segment->element('NM102');
											$data['PatientFirstName'] = $segment->element('NM103');
											$data['PatientMiddleName'] = $segment->element('NM104');
											$data['PatientSuffix'] = $segment->element('NM106');	// ?
											$data['PatientId'] = $segment->element('NM108');	// ?
										}
										break;
								}
								break;
							case 'PR':
								// 2010BB — PAYER NAME
								switch($data['CurrentInsuranceType']) {
									case 1:
										// storeInsuranceCompany
										$data['PrimaryPayerName'] = $segment->element('NM102');
										$data['PrimaryPayerId'] = $segment->element('NM108');
										break;
									case 2:
										// storeInsuranceCompany
										$data['SecondaryPayerName'] = $segment->element('NM102');
										$data['SecondaryPayerId'] = $segment->element('NM108');
										break;
									case 3:
										// storeInsuranceCompany
										$data['TertiaryPayerName'] = $segment->element('NM102');
										$data['TertiaryPayerId'] = $segment->element('NM108');
										break;
								}
								break;
							case 'QC':
								// 2010CA — PATIENT NAME
								// storePatientData
								$data['PatientLastName'] = $segment->element('NM102');
								$data['PatientFirstName'] = $segment->element('NM103');
								$data['PatientMiddleName'] = $segment->element('NM104');
								$data['PatientSuffix'] = $segment->element('NM106');	// ?
								$data['PatientId'] = $segment->element('NM108');	// ?
								break;
						}
						break;
					case N3::class:
						switch($data['LastNM1']->element('NM101')) {
							case '85':
								// 2010AA — BILLING PROVIDER NAME
								// storeFacility
								$data['BillingProviderAddress1'] = $segment->element('N301');
								$data['BillingProviderAddress2'] = $segment->element('N302');
								break;
							case '87':
								// 2010AB — PAY-TO ADDRESS NAME
								$data['PayToProviderAddress1'] = $segment->element('N301');	// ?
								$data['PayToProviderAddress2'] = $segment->element('N302');	// ?
								break;
							case 'IL':
								// 2010BA — SUBSCRIBER NAME
								switch($data['CurrentInsuranceType']) {
									case 1:
										// storeInsuranceData
										$data['PrimarySubscriberAddress1'] = $segment->element('N301');
										$data['PrimarySubscriberAddress2'] = $segment->element('N302');

										if($data['PrimarySubscriberRelation'] == 'self') {
											// storePatientData
											$data['PatientAddress1'] = $segment->element('N301');
											$data['PatientAddress2'] = $segment->element('N302');
										}
										break;
									case 2:
										// storeInsuranceData
										$data['SecondarySubscriberAddress1'] = $segment->element('N301');
										$data['SecondarySubscriberAddress2'] = $segment->element('N302');

										if($data['SecondarySubscriberRelation'] == 'self' && $data['PatientAddress1'] == '') {
											// storePatientData
											$data['PatientAddress1'] = $segment->element('N301');
											$data['PatientAddress2'] = $segment->element('N302');
										}
										break;
									case 3:
										// storeInsuranceData
										$data['TertiarySubscriberAddress1'] = $segment->element('N301');
										$data['TertiarySubscriberAddress2'] = $segment->element('N302');

										if($data['TertiarySubscriberRelation'] == 'self' && $data['PatientAddress1'] == '') {
											// storePatientData
											$data['PatientAddress1'] = $segment->element('N301');
											$data['PatientAddress2'] = $segment->element('N302');
										}
										break;
								}
								break;
							case 'PR':
								// 2010BB — PAYER NAME
								switch($data['CurrentInsuranceType']) {
									case 1:
										// storeAddresses
										$data['PrimaryPayerAddress1'] = $segment->element('N301');
										$data['PrimaryPayerAddress2'] = $segment->element('N302');
										break;
									case 2:
										// storeAddresses
										$data['SecondaryPayerAddress1'] = $segment->element('N301');
										$data['SecondaryPayerAddress2'] = $segment->element('N302');
										break;
									case 3:
										// storeAddresses
										$data['TertiaryPayerAddress1'] = $segment->element('N301');
										$data['TertiaryPayerAddress2'] = $segment->element('N303');
										break;
								}
							case 'QC':
								// 2010CA — PATIENT NAME
								// storePatientData
								$data['PatientAddress1'] = $segment->element('N301');
								$data['PatientAddress2'] = $segment->element('N302');
								break;
						}
						break;
					case N4::class:
						switch($data['LastNM1']->element('NM101')) {
							case '85':
								// 2010AA — BILLING PROVIDER NAME
								// storeFacility
								$data['BillingProviderCity'] = $segment->element('N401');
								$data['BillingProviderState'] = $segment->element('N402');
								$data['BillingProviderZip'] = $segment->element('N403');
								break;
							case '87':
								// 2010AB — PAY-TO ADDRESS NAME
								$data['PayToProviderCity'] = $segment->element('N401');	// ?
								$data['PayToProviderState'] = $segment->element('N402');	// ?
								$data['PayToProviderZip'] = $segment->element('N403');	// ?
								break;
							case 'IL':
								// 2010BA — SUBSCRIBER NAME
								switch($data['CurrentInsuranceType']) {
									case 1:
										// storeInsuranceData
										$data['PrimarySubscriberCity'] = $segment->element('N401');
										$data['PrimarySubscriberState'] = $segment->element('N402');
										$data['PrimarySubscriberZip'] = $segment->element('N403');

										if($data['PrimarySubscriberRelation'] == 'self') {
											// storePatientData
											$data['PatientCity'] = $segment->element('N401');
											$data['PatientState'] = $segment->element('N402');
											$data['PatientZip'] = $segment->element('N403');
										}
										break;
									case 2:
										// storeInsuranceData
										$data['SecondarySubscriberCity'] = $segment->element('N401');
										$data['SecondarySubscriberState'] = $segment->element('N402');
										$data['SecondarySubscriberZip'] = $segment->element('N403');

										if($data['SecondarySubscriberRelation'] == 'self' && $data['PatientCity'] == '') {
											// storePatientData
											$data['PatientCity'] = $segment->element('N401');
											$data['PatientState'] = $segment->element('N402');
											$data['PatientZip'] = $segment->element('N403');
										}
										break;
									case 3:
										// storeInsuranceData
										$data['TertiarySubscriberCity'] = $segment->element('N401');
										$data['TertiarySubscriberState'] = $segment->element('N402');
										$data['TertiarySubscriberZip'] = $segment->element('N403');

										if($data['TertiarySubscriberRelation'] == 'self' && $data['PatientCity'] == '') {
											// storePatientData
											$data['PatientCity'] = $segment->element('N401');
											$data['PatientState'] = $segment->element('N402');
											$data['PatientZip'] = $segment->element('N403');
										}
										break;
								}
								break;
							case 'PR':
								// 2010BB — PAYER NAME
								switch($data['CurrentInsuranceType']) {
									case 1:
										// storeAddress
										$data['PrimaryPayerCity'] = $segment->element('N401');
										$data['PrimaryPayerState'] = $segment->element('N402');
										$data['PrimaryPayerZip'] = $segment->element('N403');
										break;
									case 2:
										// storeAddress
										$data['SecondaryPayerCity'] = $segment->element('N401');
										$data['SecondaryPayerState'] = $segment->element('N402');
										$data['SecondaryPayerZip'] = $segment->element('N403');
										break;
									case 3:
										// storeAddress
										$data['TertiaryPayerCity'] = $segment->element('N401');
										$data['TertiaryPayerState'] = $segment->element('N402');
										$data['TertiaryPayerZip'] = $segment->element('N403');
										break;
								}
								break;
							case 'QC':
								// 2010CA — PATIENT NAME
								// storePatientData
								$data['PatientCity'] = $segment->element('N401');
								$data['PatientState'] = $segment->element('N402');
								$data['PatientZip'] = $segment->element('N403');
								break;
						}
						break;
					case DMG::class:
						// 2010BA — SUBSCRIBER NAME & 2010CA — PATIENT NAME
						$data['SubDOB'] = $segment->element('DMG02');	// ?
						$data['SubSex'] = $segment->element('DMG03');	// ?

						switch($data['CurrentInsuranceType']) {
							case 1:
								// storeInsuranceData
								$data['PrimarySubscriberDOB'] = $segment->element('DMG02');
								$data['PrimarySubscriberSex'] = $segment->element('DMG03');

								if($data['PrimarySubscriberRelation'] == 'self') {
									// storePatientData
									$data['PatientDOB'] = $segment->element('DMG02');
									$data['PatientSex'] = $segment->element('DMG03');
								}
								break;
							case 2:
								// storeInsuranceData
								$data['SecondarySubscriberDOB'] = $segment->element('DMG02');
								$data['SecondarySubscriberSex'] = $segment->element('DMG03');

								if($data['SecondarySubscriberRelation'] == 'self') {
									// storePatientData
									$data['PatientDOB'] = $segment->element('DMG02');
									$data['PatientSex'] = $segment->element('DMG03');
								}
								break;
							case 3:
								// storeInsuranceData
								$data['TertiarySubscriberDOB'] = $segment->element('DMG02');
								$data['TertiarySubscriberSex'] = $segment->element('DMG03');

								if($data['TertiarySubscriberRelation'] == 'self') {
									// storePatientData
									$data['PatientDOB'] = $segment->element('DMG02');
									$data['PatientSex'] = $segment->element('DMG03');
								}
								break;
						}
						break;
					case REF::class:
						// 2010AA — BILLING PROVIDER NAME & 2010AC — PAY-TO PLAN NAME
						// storeFacility
						$data['BillingProviderEIN'] = $segment->element('REF02');
						break;
				}
			}
		} while(!is_null($segment));
	}

	protected function processLoop2300(Loop2300 $loop2300, array &$data) {
		echo " - Function:\t\t".__FUNCTION__.PHP_EOL;

		$header = $loop2300->getHeader();

		do {
			$segment = $this->findNextSegment(
				$header,
				[
					CLM::class,
					DTP::class,
					REF::class,
					NTE::class,
					HI::class,
				]
			);

			if($segment) {
				switch(get_class($segment)) {
					case CLM::class:
						// 2300 — CLAIM INFORMATION
						// storeFormEncounter
						// storeForms
						// storeBilling
						$data['ClaimId'] = $segment->element('CLM01');
						$data['ClaimAmount'] = $segment->element('CLM02');	// ?

						// storeFacility
						$data['FacilityCodeValue'] = $segment->subElement('CLM05', 0);
						$data['FacilityCodeQualifier'] = $segment->subElement('CLM05', 1);	// ?
						$data['FrequencyTypeCode'] = $segment->subElement('CLM05', 2);	// ?

						// storeInsuranceData
						$data['ProviderSignatureOnFile'] = $segment->element('CLM07');	// ?
						$data['ProviderAcceptAssignmentCode'] = ($segment->elementEquals('CLM08', 'A') ? 'true' : '');
						$data['BenefitIndicator'] = $segment->element('CLM09');	// ?
						$data['ReleaseOfInformation'] = $segment->element('CLM10');	// ?
						break;
					case DTP::class:
						// 2300 — CLAIM INFORMATION
						if($segment->elementEquals('DTP01', '431')) {
							$data['Dos2'] = $segment->element('DTP03');	// ?
						}
						break;
					case REF::class:
						// 2300 — CLAIM INFORMATION
						if($segment->elementEquals('REF01', 'EA')) {
							$data['MedicalRecordNumber'] = $segment->element('REF02');	// ?
						}
						break;
					case NTE::class:
						// 2300 — CLAIM INFORMATION
						if($segment->elementEquals('NTE01', 'ADD')) {
							$data['NoteDesc'] = $segment->element('NTE02');	// ?
						}
						break;
					case HI::class:
						// 2300 — CLAIM INFORMATION
						// storeBilling
						if($segment->subElementEquals('HI01', 0, [ 'ABK', 'BK' ])) {
							$elements = [
								'HI01', 'HI02', 'HI03',
								'HI04', 'HI05', 'HI06',
								'HI07', 'HI08', 'HI09',
								'HI10', 'HI11', 'HI12'
							];

							foreach($elements as $element) {
								if($segment->subElementCount($element) > 1) {
									array_key_exists('DxType', $data) || $data['DxType'] = [];
									$data['DxType'][] = $segment->subElement($element, 0);

									array_key_exists('Dx', $data) || $data['Dx'] = [];
									$data['Dx'][] = $segment->subElement($element, 1);

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
				switch(get_class($section)) {
					// case Loop2305::class:
					// 	$this->processLoop2305($section, $data);
					// 	break;
					case Loop2310::class:
						$this->processLoop2310($section, $data);
						break;

					case Loop2320::class:
						$this->processLoop2320($section, $data);
						break;

					case Loop2400::class:
						$this->processLoop2400($section, $data);
						break;
				}
			}
		}
	}

	// protected function processLoop2305(Loop2305 $loop2305, array &$data) {
	// 	echo " - Function:\t\t".__FUNCTION__.PHP_EOL;

	// 	$header = $loop2305->getHeader();
	// }

	protected function processLoop2310(Loop2310 $loop2310, array &$data) {
		echo " - Function:\t\t".__FUNCTION__.PHP_EOL;

		$header = $loop2310->getHeader();

		do {
			$segment = $this->findNextSegment(
				$header,
				[
					NM1::class,
					N3::class,
					N4::class,
					PRV::class,
				]
			);

			if($segment) {
				switch(get_class($segment)) {
					case NM1::class:
						$data['LastNM1'] = $segment;

						switch($segment->element('NM101')) {
							case 'DN':
								// 2310A — REFERRING PROVIDER NAME
								// storeUser
								// storeGroup
								$data['ReferringType'] = $segment->element('NM102');	// ?
								$data['ReferringLastName'] = $segment->element('NM103');
								$data['ReferringFirstName'] = $segment->element('NM104');
								$data['ReferringMiddleName'] = $segment->element('NM105');
								$data['ReferringSuffix'] = $segment->element('NM107');	// ?
								$data['ReferringId'] = $segment->element('NM109');
								break;
							case '82':
								// 2310B — RENDERING PROVIDER NAME
								// storeUser
								// storeGroup
								$data['RenderingType'] = $segment->element('NM102');	// ?
								$data['RenderingLastName'] = $segment->element('NM103');
								$data['RenderingFirstName'] = $segment->element('NM104');
								$data['RenderingMiddleName'] = $segment->element('NM105');
								$data['RenderingSuffix'] = $segment->element('NM107');	// ?
								$data['RenderingId'] = $segment->element('NM109');
								break;
							case '77':
								// 2310C — SERVICE FACILITY LOCATION NAME
								if($segment->elementEquals('NM102', '2')) {
									// storeUser
									// storeFacility
									$data['ServiceFacilityName'] = $segment->element('NM103');
									$data['ServiceFacilityId'] = $segment->element('NM109');	// NPI
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
					case N3::class:
						// 2310C — SERVICE FACILITY LOCATION NAME
						if($data['LastNM1']->elementEquals('NM101', '77')) {
							// storeFacility
							$data['ServiceFacilityAddress1'] = $segment->element('N301');
							$data['ServiceFacilityAddress2'] = $segment->element('N302');
						}
						break;
					case N4::class:
						// 2310C — SERVICE FACILITY LOCATION NAME
						if($data['LastNM1']->elementEquals('NM101', '77')) {
							// storeFacility
							$data['ServiceFacilityCity'] = $segment->element('N401');
							$data['ServiceFacilityState'] = $segment->element('N402');
							$data['ServiceFacilityZip'] = $segment->element('N403');
						}
						break;
					case PRV::class:
						// 2310B — RENDERING PROVIDER NAME
						if($segment->elementEquals('PRV01', 'PE')) {
							// storeUser
							$data['RenderingTaxonomy'] = $segment->element('PRV03');
						}
						break;
				}
			}
		} while(!is_null($segment));
	}

	protected function processLoop2320(Loop2320 $loop2320, array &$data) {
		echo " - Function:\t\t".__FUNCTION__.PHP_EOL;

		$header = $loop2320->getHeader();

		do {
			$segment = $this->findNextSegment(
				$header,
				[
					SBR::class,
				]
			);

			if($segment) {
				switch(get_class($segment)) {
					case SBR::class:
						// 2320 — OTHER SUBSCRIBER INFORMATION
						switch($segment->element('SBR01')) {
							case 'P':
								// storeInsuranceData
								$data['CurrentInsuranceType'] = 1;
								$data['PrimarySubscriberRelation'] = ($segment->elementEquals('SBR02', '18') ? 'self' : '');
								$data['PrimaryPolicy'] = $segment->element('SBR03');
								$data['PrimaryPlanName'] = $segment->element('SBR04');
								break;
							case 'S':
								// storeInsuranceData
								$data['CurrentInsuranceType'] = 2;
								$data['SecondarySubscriberRelation'] = ($segment->elementEquals('SBR02', '18') ? 'self' : '');
								$data['SecondaryPolicy'] = $segment->element('SBR03');
								$data['SecondaryPlanName'] = $segment->element('SBR04');
								break;
							case 'T':
								// storeInsuranceData
								$data['CurrentInsuranceType'] = 3;
								$data['TertiarySubscriberRelation'] = ($segment->elementEquals('SBR02', '18') ? 'self' : '');
								$data['TertiaryPolicy'] = $segment->element('SBR03');
								$data['TertiaryPlanName'] = $segment->element('SBR04');
								break;
						}
						break;
				}
			}
		} while(!is_null($segment));

		$descendant = $loop2320->getDescendant();

		if(is_array($descendant)) {
			foreach($descendant as $section) {
				$this->processLoop2330($section, $data);
			}
		}
	}

	protected function processLoop2330(Loop2330 $loop2330, array &$data) {
		echo " - Function:\t\t".__FUNCTION__.PHP_EOL;

		$header = $loop2330->getHeader();

		do {
			$segment = $this->findNextSegment(
				$header,
				[
					NM1::class,
					N3::class,
					N4::class,
					REF::class,
				]
			);

			if($segment) {
				switch (get_class($segment)) {
					case NM1::class:
						$data['LastNM1'] = $segment;

						switch($segment->element('NM101')) {
							case 'IL':
								// 2330A — OTHER SUBSCRIBER NAME
								switch($data['CurrentInsuranceType']) {
									case 1:
										// storeInsuranceData
										$data['PrimarySubscriberLastName'] = $segment->element('NM102');
										$data['PrimarySubscriberFirstName'] = $segment->element('NM103');
										$data['PrimarySubscriberMiddleName'] = $segment->element('NM104');
										$data['PrimarySubscriberSuffix'] = $segment->element('NM106');	// ?
										$data['PrimarySubscriberId'] = $segment->element('NM108');

										if($data['PrimarySubscriberRelation'] == 'self') {
											// storePatientData
											$data['PatientLastName'] = $segment->element('NM102');
											$data['PatientFirstName'] = $segment->element('NM103');
											$data['PatientMiddleName'] = $segment->element('NM104');
											$data['PatientSuffix'] = $segment->element('NM106');	// ?
											$data['PatientId'] = $segment->element('NM108');	// ?
										}
										break;
									case 2:
										// storeInsuranceData
										$data['SecondarySubscriberLastName'] = $segment->element('NM102');
										$data['SecondarySubscriberFirstName'] = $segment->element('NM103');
										$data['SecondarySubscriberMiddleName'] = $segment->element('NM104');
										$data['SecondarySubscriberSuffix'] = $segment->element('NM106');	// ?
										$data['SecondarySubscriberId'] = $segment->element('NM108');

										if($data['SecondarySubscriberRelation'] == 'self' && $data['PatientLastName'] == '') {
											// storePatientData
											$data['PatientLastName'] = $segment->element('NM102');
											$data['PatientFirstName'] = $segment->element('NM103');
											$data['PatientMiddleName'] = $segment->element('NM104');
											$data['PatientSuffix'] = $segment->element('NM106');	// ?
											$data['PatientId'] = $segment->element('NM108');	// ?
										}
										break;
									case 3:
										// storeInsuranceData
										$data['TertiarySubscriberLastName'] = $segment->element('NM102');
										$data['TertiarySubscriberFirstName'] = $segment->element('NM103');
										$data['TertiarySubscriberMiddleName'] = $segment->element('NM104');
										$data['TertiarySubscriberSuffix'] = $segment->element('NM106');	// ?
										$data['TertiarySubscriberId'] = $segment->element('NM108');

										if($data['TertiarySubscriberRelation'] == 'self' && $data['PatientLastName'] == '') {
											// storePatientData
											$data['PatientLastName'] = $segment->element('NM102');
											$data['PatientFirstName'] = $segment->element('NM103');
											$data['PatientMiddleName'] = $segment->element('NM104');
											$data['PatientSuffix'] = $segment->element('NM106');	// ?
											$data['PatientId'] = $segment->element('NM108');	// ?
										}
										break;
								}
								break;
							case 'PR':
								// 2330B — OTHER PAYER NAME
								switch($data['CurrentInsuranceType']) {
									case 1:
										// storeInsuranceCompany
										$data['PrimaryPayerName'] = $segment->element('NM102');
										$data['PrimaryPayerId'] = $segment->element('NM108');
										break;
									case 2:
										// storeInsuranceCompany
										$data['SecondaryPayerName'] = $segment->element('NM102');
										$data['SecondaryPayerId'] = $segment->element('NM108');
										break;
									case 3:
										// storeInsuranceCompany
										$data['TertiaryPayerName'] = $segment->element('NM102');
										$data['TertiaryPayerId'] = $segment->element('NM108');
										break;
								}
								break;
							case 'DN':
								// 2330C — OTHER PAYER REFERRING PROVIDER
								// storeUser
								// storeGroup
								$data['ReferringType'] = $segment->element('NM102');	// ?
								$data['ReferringLastName'] = $segment->element('NM103');
								$data['ReferringFirstName'] = $segment->element('NM104');
								$data['ReferringMiddleName'] = $segment->element('NM105');
								$data['ReferringSuffix'] = $segment->element('NM107');	// ?
								$data['ReferringId'] = $segment->element('NM109');
								break;
							case '82':
								// 2330D — OTHER PAYER RENDERING PROVIDER
								// storeUser
								// storeGroup
								$data['RenderingType'] = $segment->element('NM102');	// ?
								$data['RenderingLastName'] = $segment->element('NM103');
								$data['RenderingFirstName'] = $segment->element('NM104');
								$data['RenderingMiddleName'] = $segment->element('NM105');
								$data['RenderingSuffix'] = $segment->element('NM107');	// ?
								$data['RenderingId'] = $segment->element('NM109');
								break;
							case '77':
								// 2330E — OTHER PAYER SERVICE FACILITY LOCATION
								if($segment->elementEquals('NM102', '2')) {
									// storeFacility
									// storeUser
									$data['ServiceFacilityName'] = $segment->element('NM103');
									$data['ServiceFacilityId'] = $segment->element('NM109');	// NPI
								}
								break;
							case 'DQ':
								// 2330F — OTHER PAYER SUPERVISING PROVIDER
								// storeUser
								// storeGroup
								$data['SupervisingType'] = $segment->element('NM102');	// ?
								$data['SupervisingLastName'] = $segment->element('NM103');	// ?
								$data['SupervisingFirstName'] = $segment->element('NM104');	// ?
								$data['SupervisingMiddleName'] = $segment->element('NM105');	// ?
								$data['SupervisingSuffix'] = $segment->element('NM107');	// ?
								$data['SupervisingId'] = $segment->element('NM109');	// ?
								break;
							case '85':
								// 2330G — OTHER PAYER BILLING PROVIDER
								// storeFacility
								// storeUser
								// storeGroup
								$data['BillingType'] = $segment->element('NM102');	// ?
								$data['BillingProviderLastName'] = $segment->element('NM103');
								$data['BillingProviderFirstName'] = $segment->element('NM104');
								$data['BillingProviderMiddleName'] = $segment->element('NM105');
								$data['BillingProviderSuffix'] = $segment->element('NM107');	// ?
								$data['BillingProviderId'] = $segment->element('NM109');
								break;
						}
						break;
					case N3::class:
						switch($data['LastNM1']->element('NM101')) {
							case 'IL':
								// 2330A — OTHER SUBSCRIBER NAME
								switch($data['CurrentInsuranceType']) {
									case 2:
										// storeInsuranceData
										$data['SecondarySubscriberAddress1'] = $segment->element('N301');
										$data['SecondarySubscriberAddress2'] = $segment->element('N302');

										if($data['SecondarySubscriberRelation'] == 'self' && $data['PatientAddress1'] == '') {
											// storePatientData
											$data['PatientAddress1'] = $segment->element('N301');
											$data['PatientAddress2'] = $segment->element('N302');
										}
										break;
									case 3:
										// storeInsuranceData
										$data['TertiarySubscriberAddress1'] = $segment->element('N301');
										$data['TertiarySubscriberAddress2'] = $segment->element('N302');

										if($data['TertiarySubscriberRelation'] == 'self' && $data['PatientAddress1'] == '') {
											// storePatientData
											$data['PatientAddress1'] = $segment->element('N301');
											$data['PatientAddress2'] = $segment->element('N302');
										}
										break;
								}
								break;
							case 'PR':
								// 2330B — OTHER PAYER NAME
								switch($data['CurrentInsuranceType']) {
									case 1:
										// storeAddresses
										$data['PrimaryPayerAddress1'] = $segment->element('N301');
										$data['PrimaryPayerAddress2'] = $segment->element('N302');
										break;
									case 2:
										// storeAddresses
										$data['SecondaryPayerAddress1'] = $segment->element('N301');
										$data['SecondaryPayerAddress2'] = $segment->element('N302');
										break;
									case 3:
										// storeAddresses
										$data['TertiaryPayerAddress1'] = $segment->element('N301');
										$data['TertiaryPayerAddress2'] = $segment->element('N303');
										break;
								}
								break;
							case '77':
								// 2330E — OTHER PAYER SERVICE FACILITY LOCATION
								// storeFacility
								$data['ServiceFacilityAddress1'] = $segment->element('N301');
								$data['ServiceFacilityAddress2'] = $segment->element('N302');
								break;
							case '85':
								// 2330G — OTHER PAYER BILLING PROVIDER
								// storeFacility
								$data['BillingProviderAddress1'] = $segment->element('N301');
								$data['BillingProviderAddress2'] = $segment->element('N302');
								break;
						}
						break;
					case N4::class:
						switch($data['LastNM1']->element('NM101')) {
							case 'IL':
								// 2330A — OTHER SUBSCRIBER NAME
								switch($data['CurrentInsuranceType']) {
									case 1:
										// storeInsuranceData
										$data['PrimarySubscriberCity'] = $segment->element('N401');
										$data['PrimarySubscriberState'] = $segment->element('N402');
										$data['PrimarySubscriberZip'] = $segment->element('N403');

										if($data['PrimarySubscriberRelation'] == 'self') {
											// storePatientData
											$data['PatientCity'] = $segment->element('N401');
											$data['PatientState'] = $segment->element('N402');
											$data['PatientZip'] = $segment->element('N403');
										}
										break;
									case 2:
										// storeInsuranceData
										$data['SecondarySubscriberCity'] = $segment->element('N401');
										$data['SecondarySubscriberState'] = $segment->element('N402');
										$data['SecondarySubscriberZip'] = $segment->element('N403');

										if($data['SecondarySubscriberRelation'] == 'self' && $data['PatientCity'] == '') {
											// storePatientData
											$data['PatientCity'] = $segment->element('N401');
											$data['PatientState'] = $segment->element('N402');
											$data['PatientZip'] = $segment->element('N403');
										}
										break;
									case 3:
										// storeInsuranceData
										$data['TertiarySubscriberCity'] = $segment->element('N401');
										$data['TertiarySubscriberState'] = $segment->element('N402');
										$data['TertiarySubscriberZip'] = $segment->element('N403');

										if($data['TertiarySubscriberRelation'] == 'self' && $data['PatientCity'] == '') {
											// storePatientData
											$data['PatientCity'] = $segment->element('N401');
											$data['PatientState'] = $segment->element('N402');
											$data['PatientZip'] = $segment->element('N403');
										}
										break;
								}
								break;
							case 'PR':
								// 2330B — OTHER PAYER NAME
								switch($data['CurrentInsuranceType']) {
									case 1:
										// storeAddress
										$data['PrimaryPayerCity'] = $segment->element('N401');
										$data['PrimaryPayerState'] = $segment->element('N402');
										$data['PrimaryPayerZip'] = $segment->element('N403');
										break;
									case 2:
										// storeAddress
										$data['SecondaryPayerCity'] = $segment->element('N401');
										$data['SecondaryPayerState'] = $segment->element('N402');
										$data['SecondaryPayerZip'] = $segment->element('N403');
										break;
									case 3:
										// storeAddress
										$data['TertiaryPayerCity'] = $segment->element('N401');
										$data['TertiaryPayerState'] = $segment->element('N402');
										$data['TertiaryPayerZip'] = $segment->element('N403');
										break;
								}
								break;
							case '77':
								// 2330E — OTHER PAYER SERVICE FACILITY LOCATION
								// storeFacility
								$data['ServiceFacilityCity'] = $segment->element('N401');
								$data['ServiceFacilityState'] = $segment->element('N402');
								$data['ServiceFacilityZip'] = $segment->element('N403');
								break;
							case '85':
								// 2330G — OTHER PAYER BILLING PROVIDER
								// storeFacility
								$data['BillingProviderCity'] = $segment->element('N401');
								$data['BillingProviderState'] = $segment->element('N402');
								$data['BillingProviderZip'] = $segment->element('N403');
								break;
						}
						break;
					case REF::class:
						// 2330B — OTHER PAYER NAME
						if($segment->elementEquals('REF01', 'EI')) {
							// storeFacility
							$data['BillingProviderEIN'] = $segment->element('REF02');
						}
						break;
				}
			}
		} while(!is_null($segment));
	}

	protected function processLoop2400(Loop2400 $loop2400, array &$data) {
		echo " - Function:\t\t".__FUNCTION__.PHP_EOL;

		$header = $loop2400->getHeader();

		do {
			$segment = $this->findNextSegment(
				$header,
				[
					SV1::class,
					DTP::class,
					NTE::class,
				]
			);

			if($segment) {
				switch(get_class($segment)) {
					case SV1::class:
						// 2400 — SERVICE LINE NUMBER
						if($segment->subElementEquals('SV101', 0, [ 'HC', 'WK' ])) {
							// storeBilling
							$data['Tx'][] = $segment->subElement('SV101', 1);

							if($segment->subElementExists('SV101', 2)) {
								$data['TxMod'][] = $segment->subElement('SV101', 2);
							}

							$data['TxAmount'][] = $segment->element('SV102');

							$data['TxUnits'][] = $segment->element('SV104');

							$data['TxCount'] = count($data['Tx']);
						}
						break;
					case DTP::class:
						// 2400 — SERVICE LINE NUMBER
						if($segment->elementEquals('DTP01', '472')) {
							// storePatientData
							$data['Dos1'] = $segment->element('DTP02').rand(10, 20).rand(10, 59).rand(10, 59);
						}
						break;
					case NTE::class:
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
				switch(get_class($section)) {
					// case Loop2410::class:
					// 	$this->processLoop2410($section, $data);
					// 	break;

					case Loop2420::class:
						$this->processLoop2420($section, $data);
						break;

					// case Loop2430::class:
					// 	$this->processLoop2430($section, $data);
					// 	break;

					// case Loop2440::class:
					// 	$this->processLoop2440($section, $data);
					// 	break;
				}
			}
		}
	}

	// protected function processLoop2410(Loop2410 $loop2410, array &$data) {
	// 	echo " - Function:\t\t".__FUNCTION__.PHP_EOL;

	// 	$header = $loop2330->getHeader();
	// }

	protected function processLoop2420(Loop2420 $loop2420, array &$data) {
		echo " - Function:\t\t".__FUNCTION__.PHP_EOL;

		$header = $loop2330->getHeader();

		do {
			$segment = $this->findNextSegment(
				$header,
				[
					NM1::class,
					N3::class,
					N4::class,
					PRV::class,
				]
			);

			if($segment) {
				switch(get_class($segment)) {
					case NM1::class:
						$data['LastNM1'] = $segment;

						switch($segment->element('NM101')) {
							case '82':
								// 2420A — RENDERING PROVIDER NAME
								// storeUser
								// storeGroup
								$data['RenderingType'] = $segment->element('NM101');	// ?
								$data['RenderingLastName'] = $segment->element('NM103');
								$data['RenderingFirstName'] = $segment->element('NM104');
								$data['RenderingMiddleName'] = $segment->element('NM105');
								$data['RenderingSuffix'] = $segment->element('NM106');	// ?
								$data['RenderingId'] = $segment->element('NM109');
								break;
							case '77':
								// 2420C — SERVICE FACILITY LOCATION NAME
								if($segment->elementEquals('NM102', '2')) {
									// storeFacility
									// storeUser
									$data['ServiceFacilityName'] = $segment->element('NM103');
									$data['ServiceFacilityId'] = $segment->element('NM109');	// NPI
								}
								break;
							case 'DQ':
								// 2420D — SUPERVISING PROVIDER NAME
								$data['SupervisingType'] = $segment->element('NM101');	// ?
								$data['SupervisingLastName'] = $segment->element('NM103');	// ?
								$data['SupervisingFirstName'] = $segment->element('NM104');	// ?
								$data['SupervisingMiddleName'] = $segment->element('NM105');	// ?
								$data['SupervisingSuffix'] = $segment->element('NM106');	// ?
								$data['SupervisingId'] = $segment->element('NM109');	// ?
								break;
							case 'DK':
								// 2420E — ORDERING PROVIDER NAME
								// storeUser
								// storeGroup
								$data['OrderingType'] = $segment->element('NM101');	// ?
								$data['OrderingLastName'] = $segment->element('NM103');
								$data['OrderingFirstName'] = $segment->element('NM104');
								$data['OrderingMiddlesName'] = $segment->element('NM105');
								$data['OrderingSuffix'] = $segment->element('NM106');	// ?
								$data['OrderingId'] = $segment->element('NM109');
								break;
							case 'DN':
								// 2420F — REFERRING PROVIDER NAME
								// storeUser
								// storeGroup
								$data['ReferringType'] = $segment->element('NM101');	// ?
								$data['ReferringLastName'] = $segment->element('NM103');
								$data['ReferringFirstName'] = $segment->element('NM104');
								$data['ReferringMiddleName'] = $segment->element('NM105');
								$data['ReferringSuffix'] = $segment->element('NM106');	// ?
								$data['ReferringId'] = $segment->element('NM109');
								break;
						}
						break;
					case N3::class:
						switch($data['LastNM1']->element('NM101')) {
							case '77':
								// 2420C — SERVICE FACILITY LOCATION NAME
								// storeFacility
								$data['ServiceFacilityAddress1'] = $segment->element('N301');
								$data['ServiceFacilityAddress2'] = $segment->element('N302');
								break;
							case 'DK':
								// 2420E — ORDERING PROVIDER NAME
								$data['OrderingAddress1'] = $segment->element('N301');	// ?
								$data['OrderingAddress2'] = $segment->element('N302');	// ?
								break;
						}
						break;
					case N4::class:
						switch($data['LastNM1']->element('NM101')) {
							case '77':
								// 2420C — SERVICE FACILITY LOCATION NAME
								// storeFacility
								$data['ServiceFacilityCity'] = $segment->element('N401');
								$data['ServiceFacilityState'] = $segment->element('N402');
								$data['ServiceFacilityZip'] = $segment->element('N403');
								break;
							case 'DK':
								// 2420E — ORDERING PROVIDER NAME
								$data['OrderingCity'] = $segment->element('N401');	// ?
								$data['OrderingState'] = $segment->element('N402');	// ?
								$data['OrderingZip'] = $segment->element('N403');	// ?
								break;
						}
						break;
					case PRV::class:
						// 2420A — RENDERING PROVIDER NAME
						if($segment->elementEquals('PRV01', 'PE') &&
							$segment->elementEquals('PRV02', 'PXC')
						) {
							// storeUsers
							$this->data['RenderingTaxonomy'] = $segment->element('PRV03');
						}
						break;
				}
			}
		} while(!is_null($segment));
	}

	// protected function processLoop2430(Loop2430 $loop2430, array &$data) {
	// 	echo " - Function:\t\t".__FUNCTION__.PHP_EOL;

	// 	$header = $loop2330->getHeader();
	// }

	// protected function processLoop2440(Loop2440 $loop2440, array &$data) {
	// 	echo " - Function:\t\t".__FUNCTION__.PHP_EOL;

	// 	$header = $loop2330->getHeader();
	// }

}