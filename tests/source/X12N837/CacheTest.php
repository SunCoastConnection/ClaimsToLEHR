<?php

namespace SunCoastConnection\ClaimsToOEMR\Tests\X12N837;

use \SunCoastConnection\ClaimsToOEMR\Tests\BaseTestCase;
use \SunCoastConnection\ClaimsToOEMR\Document\Options;
use \SunCoastConnection\ClaimsToOEMR\Document\Raw\Element;
use \SunCoastConnection\ClaimsToOEMR\Store;
use \SunCoastConnection\ClaimsToOEMR\X12N837\Document;
use \SunCoastConnection\ClaimsToOEMR\X12N837\Cache;
use \SunCoastConnection\ClaimsToOEMR\X12N837\Envelope;
use \SunCoastConnection\ClaimsToOEMR\X12N837\Loop;
use \SunCoastConnection\ClaimsToOEMR\X12N837\Segment;

class CacheTest extends BaseTestCase {

	protected $cache;

	public function setUp() {
		parent::setUp();

		$this->cache = $this->getMockery(
			Cache::class
		)->makePartial();
	}

	protected function setupTestProcessLoop($loopClass, $segmentMatches, $segmentClasses = null) {
		$loop = $this->getMockery($loopClass);

		if(!is_array($segmentClasses)) {
			$segmentClasses = [ $segmentClasses ];
		}

		$header = [];

		foreach($segmentClasses as $segmentClass) {
			if(!is_null($segmentClass)) {
				$segment = $this->getMockery($segmentClass);

				$segment->shouldReceive('getName')
					->once()
					->andReturn(
						substr(
							$segmentClass,
							strrpos($segmentClass, '\\') + 1
						)
					);

				$header[] = $segment;
			}
		}

		$loop->shouldReceive('getHeader')
			->once()
			->andReturn($header);

		$this->cache->shouldAllowMockingProtectedMethods();

		$findNextSegment = $this->cache->shouldReceive('findNextSegment')
			->times(count($header) + 1)
			->with($header, $segmentMatches)
			->andReturnValues(array_merge($header, [ null ]));

		return [ 'segments' => $header, 'loop' => $loop ];
	}

	/**
	 * @covers SunCoastConnection\ClaimsToOEMR\X12N837\Cache::getInstance()
	 */
	public function testGetInstance() {
		$options = $this->getMockery(
			Store::class
		);

		$cache = Cache::getInstance($options);

		$this->assertInstanceOf(
			Cache::class,
			$cache,
			'Expected new instance of '.Cache::class.'.'
		);
	}

	/**
	 * @covers SunCoastConnection\ClaimsToOEMR\X12N837\Cache::__construct()
	 */
	public function testConstruct() {
		$store = $this->getMockery(
			Store::class
		);

		$this->cache->shouldAllowMockingProtectedMethods()
			->shouldReceive('store')
			->once()
			->with($store);

		$this->cache->__construct($store);
	}

	/**
	 * @covers SunCoastConnection\ClaimsToOEMR\X12N837\Cache::store()
	 */
	public function testStore() {
		$this->assertNull(
			$this->cache->store(),
			'Store should return null when empty.'
		);

		$store = $this->getMockery(
			Store::class
		);

		$this->assertSame(
			$store,
			$this->cache->store($store),
			'Store should return set store object when setting value.'
		);

		$this->assertSame(
			$store,
			$this->cache->store(),
			'Store should return set store object after setting value.'
		);
	}

	/**
	 * @covers SunCoastConnection\ClaimsToOEMR\X12N837\Cache::storeAddress()
	 */
	public function testStoreAddressWithNoData() {
		$data = [];

		$this->cache->shouldAllowMockingProtectedMethods()
			->shouldNotReceive('store->storeAddress');

		$this->assertNull(
			$this->cache->storeAddress($data),
			'Store Address should not have returned Id'
		);
	}

	/**
	 * @covers SunCoastConnection\ClaimsToOEMR\X12N837\Cache::storeAddress()
	 */
	public function testStoreAddress() {
		$storeAddress = [
			'foreign_id' => 'InsuranceCompany',
			'line1' => 'line1',
			'line2' => 'line2',
			'city' => 'city',
			'state' => 'state',
			'zip' => '12345',
			'plus_four' => '6789',
		];

		$data = [
			'InsuranceCompany' => 'InsuranceCompany',
			'N3' => $this->getMockery(
				Segment\N3::class
			),
			'N4' => $this->getMockery(
				Segment\N4::class
			)
		];

		$data['N3']->shouldReceive('element')
			->once()
			->with('N301')
			->andReturn($storeAddress['line1']);

		$data['N3']->shouldReceive('element')
			->once()
			->with('N302')
			->andReturn($storeAddress['line2']);

		$data['N4']->shouldReceive('element')
			->once()
			->with('N401')
			->andReturn($storeAddress['city']);

		$data['N4']->shouldReceive('element')
			->once()
			->with('N402')
			->andReturn($storeAddress['state']);

		$data['N4']->shouldReceive('element')
			->times(3)
			->with('N403')
			->andReturn('123456789');

		$this->cache->shouldAllowMockingProtectedMethods()
			->shouldReceive('store->storeAddress')
			->once()
			->with($storeAddress)
			->andReturn('storeAddress');

		$this->assertEquals(
			'storeAddress',
			$this->cache->storeAddress($data),
			'Store Address should have returned Id'
		);
	}

	/**
	 * @covers SunCoastConnection\ClaimsToOEMR\X12N837\Cache::storeBilling()
	 */
	public function testStoreBillingWithNoData() {
		$data = [];

		$this->cache->shouldAllowMockingProtectedMethods()
			->shouldNotReceive('store->storeBilling');

		$this->assertNull(
			$this->cache->storeBilling($data),
			'Store Billing should not have returned Id'
		);
	}

	/**
	 * @covers SunCoastConnection\ClaimsToOEMR\X12N837\Cache::storeBilling()
	 */
	public function testStoreBillingWithSegmentSV1() {
		$storeBilling = [
			'code_type' => 'CPT4',
			'groupname' => 'Default',
			'authorized' => 1,
			'billed' => 0,
			'activity' => 1,
			'bill_process' => 0,
			'units' => 'units',
			'fee' => 'fee',
			'provider_id' => 'User',
			'user' => 'User',
			'payer_id' => 'InsuranceCompany',
			'pid' => 'PatientData',
			'encounter' => 'encounter',
			'justify' => 'Z12.34',
			'code' => 'code',
			'modifier' => 'modifier',
		];

		$data = [
			'User' => 'User',
			'InsuranceCompany' => 'InsuranceCompany',
			'PatientData' => 'PatientData',
			'CLM' => $this->getMockery(
				Segment\CLM::class
			),
			'SV1' => $this->getMockery(
				Segment\SV1::class
			),
			'HI' => [
				$this->getMockery(
					Segment\HI::class
				)
			]
		];

		$data['CLM']->shouldReceive('element')
			->once()
			->with('CLM01')
			->andReturn($storeBilling['encounter']);

		$elementHI01 = $this->getMockery(
			Element::class
		);

		$data['HI'][0]->shouldReceive('element')
			->once()
			->with('HI01')
			->andReturn($elementHI01);

		$elementHI01->shouldReceive('subElement')
			->once()
			->with(1)
			->andReturn('Z1234');

		$elementSV101 = $this->getMockery(
			Element::class
		);

		$data['SV1']->shouldReceive('element')
			->twice()
			->with('SV101')
			->andReturn($elementSV101);

		$elementSV101->shouldReceive('subElement')
			->once()
			->with(1)
			->andReturn($storeBilling['code']);

		$elementSV101->shouldReceive('subElement')
			->once()
			->with(2)
			->andReturn($storeBilling['modifier']);

		$data['SV1']->shouldReceive('element')
			->once()
			->with('SV102')
			->andReturn($storeBilling['fee']);

		$data['SV1']->shouldReceive('element')
			->once()
			->with('SV104')
			->andReturn($storeBilling['units']);

		$this->cache->shouldAllowMockingProtectedMethods()
			->shouldReceive('store->storeBilling')
			->once()
			->with($storeBilling)
			->andReturn('storeBilling');

		$this->cache->storeBilling($data);
	}

	/**
	 * @covers SunCoastConnection\ClaimsToOEMR\X12N837\Cache::storeBilling()
	 */
	public function testStoreBillingWithSegmentSV1AndDTP02EqualsRD8() {
		$storeBilling = [
			'code' => 'code',
			'modifier' => 'modifier',
			'fee' => 'fee',
			'units' => 'units',
		];

		$data = [
			'DTP' => $this->getMockery(
				Segment\DTP::class
			),
			'SV1' => $this->getMockery(
				Segment\SV1::class
			)
		];

		$data['DTP']->shouldReceive('elementEquals')
			->once()
			->with('DTP02', 'RD8')
			->andReturn(true);

		$data['DTP']->shouldReceive('element')
			->once()
			->with('DTP03')
			->andReturn('12345678-87654321');

		$elementSV101 = $this->getMockery(
			Element::class
		);

		$data['SV1']->shouldReceive('element')
			->twice()
			->with('SV101')
			->andReturn($elementSV101);

		$elementSV101->shouldReceive('subElement')
			->once()
			->with(1)
			->andReturn($storeBilling['code']);

		$elementSV101->shouldReceive('subElement')
			->once()
			->with(2)
			->andReturn($storeBilling['modifier']);

		$data['SV1']->shouldReceive('element')
			->once()
			->with('SV102')
			->andReturn($storeBilling['fee']);

		$data['SV1']->shouldReceive('element')
			->once()
			->with('SV104')
			->andReturn($storeBilling['units']);

		$this->cache->shouldAllowMockingProtectedMethods()
			->shouldReceive('store->storeBilling')
			->once()
			->with(\Mockery::on(function($billing) {
				return array_key_exists('date', $billing) &&
					strlen($billing['date']) == 14 &&
					substr($billing['date'], 0, 8) == '12345678';
			}))
			->andReturn('storeBilling');

		$this->cache->storeBilling($data);
	}

	/**
	 * @covers SunCoastConnection\ClaimsToOEMR\X12N837\Cache::storeBilling()
	 */
	public function testStoreBillingWithSegmentSV1AndDTP02NotEqualsRD8() {
		$storeBilling = [
			'code' => 'code',
			'modifier' => 'modifier',
			'fee' => 'fee',
			'units' => 'units',
		];

		$data = [
			'DTP' => $this->getMockery(
				Segment\DTP::class
			),
			'SV1' => $this->getMockery(
				Segment\SV1::class
			)
		];

		$data['DTP']->shouldReceive('elementEquals')
			->once()
			->with('DTP02', 'RD8')
			->andReturn(false);

		$data['DTP']->shouldReceive('element')
			->once()
			->with('DTP03')
			->andReturn('12345678');

		$elementSV101 = $this->getMockery(
			Element::class
		);

		$data['SV1']->shouldReceive('element')
			->twice()
			->with('SV101')
			->andReturn($elementSV101);

		$elementSV101->shouldReceive('subElement')
			->once()
			->with(1)
			->andReturn($storeBilling['code']);

		$elementSV101->shouldReceive('subElement')
			->once()
			->with(2)
			->andReturn($storeBilling['modifier']);

		$data['SV1']->shouldReceive('element')
			->once()
			->with('SV102')
			->andReturn($storeBilling['fee']);

		$data['SV1']->shouldReceive('element')
			->once()
			->with('SV104')
			->andReturn($storeBilling['units']);

		$this->cache->shouldAllowMockingProtectedMethods()
			->shouldReceive('store->storeBilling')
			->once()
			->with(\Mockery::on(function($billing) {
				return array_key_exists('date', $billing) &&
					strlen($billing['date']) == 14 &&
					substr($billing['date'], 0, 8) == '12345678';
			}))
			->andReturn('storeBilling');

		$this->cache->storeBilling($data);
	}

	/**
	 * @covers SunCoastConnection\ClaimsToOEMR\X12N837\Cache::storeBilling()
	 */
	public function testStoreBillingWithNoSegmentSV1AndHI011IsCPT4() {
		$storeBilling = [
			'code_type' => 'CPT4',
			'groupname' => 'Default',
			'authorized' => 1,
			'billed' => 0,
			'activity' => 1,
			'bill_process' => 0,
			'units' => 1,
			'fee' => 10,
			'provider_id' => 'User',
			'user' => 'User',
			'payer_id' => 'InsuranceCompany',
			'pid' => 'PatientData',
			'encounter' => 'encounter',
			'code' => 'code',
		];

		$data = [
			'User' => 'User',
			'InsuranceCompany' => 'InsuranceCompany',
			'PatientData' => 'PatientData',
			'CLM' => $this->getMockery(
				Segment\CLM::class
			),
			'HI' => $this->getMockery(
				Segment\HI::class
			)
		];

		$data['CLM']->shouldReceive('element')
			->once()
			->with('CLM01')
			->andReturn($storeBilling['encounter']);

		$data['HI']->shouldReceive('elementExists')
			->once()
			->with('HI01')
			->andReturn(true);

		$elementHI01 = $this->getMockery(
			Element::class
		);

		$data['HI']->shouldReceive('element')
			->times(3)
			->with('HI01')
			->andReturn($elementHI01);

		$elementHI01->shouldReceive('subElementCount')
			->once()
			->andReturn(2);

		$elementHI01->shouldReceive('subElement')
			->once()
			->with(0)
			->andReturn('BP');

		$elementHI01->shouldReceive('subElement')
			->once()
			->with(1)
			->andReturn('code');

		$data['HI']->shouldReceive('elementExists')
			->times(11)
			->with(\Mockery::not('HI01'))
			->andReturn(false);

		$this->cache->shouldAllowMockingProtectedMethods()
			->shouldReceive('store->storeBilling')
			->once()
			->with($storeBilling)
			->andReturn('storeBilling');

		$this->cache->storeBilling($data);
	}

	/**
	 * @covers SunCoastConnection\ClaimsToOEMR\X12N837\Cache::storeBilling()
	 */
	public function testStoreBillingWithNoSegmentSV1AndHI011IsICD9() {
		$storeBilling = [
			'code_type' => 'ICD9',
			'groupname' => 'Default',
			'authorized' => 1,
			'billed' => 0,
			'activity' => 1,
			'bill_process' => 0,
			'units' => 1,
			'fee' => 10,
			'provider_id' => 'User',
			'user' => 'User',
			'payer_id' => 'InsuranceCompany',
			'pid' => 'PatientData',
			'encounter' => 'encounter',
			'code' => 'Z12.34',
		];

		$data = [
			'User' => 'User',
			'InsuranceCompany' => 'InsuranceCompany',
			'PatientData' => 'PatientData',
			'CLM' => $this->getMockery(
				Segment\CLM::class
			),
			'HI' => $this->getMockery(
				Segment\HI::class
			)
		];

		$data['CLM']->shouldReceive('element')
			->once()
			->with('CLM01')
			->andReturn($storeBilling['encounter']);

		$data['HI']->shouldReceive('elementExists')
			->once()
			->with('HI01')
			->andReturn(true);

		$elementHI01 = $this->getMockery(
			Element::class
		);

		$data['HI']->shouldReceive('element')
			->times(3)
			->with('HI01')
			->andReturn($elementHI01);

		$elementHI01->shouldReceive('subElementCount')
			->once()
			->andReturn(2);

		$elementHI01->shouldReceive('subElement')
			->once()
			->with(0)
			->andReturn('BK');

		$elementHI01->shouldReceive('subElement')
			->once()
			->with(1)
			->andReturn('Z1234');

		$data['HI']->shouldReceive('elementExists')
			->times(11)
			->with(\Mockery::not('HI01'))
			->andReturn(false);

		$this->cache->shouldAllowMockingProtectedMethods()
			->shouldReceive('store->storeBilling')
			->once()
			->with($storeBilling)
			->andReturn('storeBilling');

		$this->cache->storeBilling($data);
	}

	/**
	 * @covers SunCoastConnection\ClaimsToOEMR\X12N837\Cache::storeBilling()
	 */
	public function testStoreBillingWithNoSegmentSV1AndHI011IsICD10() {
		$storeBilling = [
			'code_type' => 'ICD10',
			'groupname' => 'Default',
			'authorized' => 1,
			'billed' => 0,
			'activity' => 1,
			'bill_process' => 0,
			'units' => 1,
			'fee' => 10,
			'provider_id' => 'User',
			'user' => 'User',
			'payer_id' => 'InsuranceCompany',
			'pid' => 'PatientData',
			'encounter' => 'encounter',
			'code' => 'Z12.34',
		];

		$data = [
			'User' => 'User',
			'InsuranceCompany' => 'InsuranceCompany',
			'PatientData' => 'PatientData',
			'CLM' => $this->getMockery(
				Segment\CLM::class
			),
			'HI' => $this->getMockery(
				Segment\HI::class
			)
		];

		$data['CLM']->shouldReceive('element')
			->once()
			->with('CLM01')
			->andReturn($storeBilling['encounter']);

		$data['HI']->shouldReceive('elementExists')
			->once()
			->with('HI01')
			->andReturn(true);

		$elementHI01 = $this->getMockery(
			Element::class
		);

		$data['HI']->shouldReceive('element')
			->times(3)
			->with('HI01')
			->andReturn($elementHI01);

		$elementHI01->shouldReceive('subElementCount')
			->once()
			->andReturn(2);

		$elementHI01->shouldReceive('subElement')
			->once()
			->with(0)
			->andReturn('ABK');

		$elementHI01->shouldReceive('subElement')
			->once()
			->with(1)
			->andReturn('Z1234');

		$data['HI']->shouldReceive('elementExists')
			->times(11)
			->with(\Mockery::not('HI01'))
			->andReturn(false);

		$this->cache->shouldAllowMockingProtectedMethods()
			->shouldReceive('store->storeBilling')
			->once()
			->with($storeBilling)
			->andReturn('storeBilling');

		$this->cache->storeBilling($data);
	}

	/**
	 * @covers SunCoastConnection\ClaimsToOEMR\X12N837\Cache::storeFacility()
	 */
	public function testStoreFacilityWithNoData() {
		$data = [];

		$this->cache->shouldAllowMockingProtectedMethods()
			->shouldNotReceive('store->storeFacility');

		$this->assertNull(
			$this->cache->storeFacility($data),
			'Store Facility should not have returned Id'
		);
	}

	/**
	 * @covers SunCoastConnection\ClaimsToOEMR\X12N837\Cache::storeFacility()
	 */
	public function testStoreFacility() {
		$storeFacility = [
			'country_code' => 'USA',
			'service_location' => 1,
			'billing_location' => 0,
			'accepts_assignment' => 1,
			'attn' => 'Billing',
			'tax_id_type' => 'EI',
			'color' => '#FFCC99',
			'primary_business_entity' => 0,
			'pos_code' => 'pos_code',
			'name' => 'name',
			'facility_npi' => 'facility_npi',
			'domain_identifier' => 'domain_identifier',
			'street' => 'street1 street2',
			'city' => 'city',
			'state' => 'state',
			'postal_code' => 'postal_code',
			'federal_ein' => 'federal_ein'
		];

		$data = [
			'CLM' => $this->getMockery(
				Segment\CLM::class
			),
			'NM1' => $this->getMockery(
				Segment\NM1::class
			),
			'N3' => $this->getMockery(
				Segment\N3::class
			),
			'N4' => $this->getMockery(
				Segment\N4::class
			),
			'REF' => $this->getMockery(
				Segment\REF::class
			)
		];

		$elementCLM05 = $this->getMockery(
			Element::class
		);

		$data['CLM']->shouldReceive('element')
			->once()
			->with('CLM05')
			->andReturn($elementCLM05);

		$elementCLM05->shouldReceive('subElement')
			->once()
			->with(0)
			->andReturn($storeFacility['pos_code']);

		$data['NM1']->shouldReceive('element')
			->once()
			->with('NM103')
			->andReturn($storeFacility['name']);

		$data['NM1']->shouldReceive('element')
			->once()
			->with('NM108')
			->andReturn($storeFacility['facility_npi']);

		$data['NM1']->shouldReceive('element')
			->once()
			->with('NM109')
			->andReturn($storeFacility['domain_identifier']);

		$data['N3']->shouldReceive('element')
			->once()
			->with('N301')
			->andReturn('street1');

		$data['N3']->shouldReceive('element')
			->once()
			->with('N302')
			->andReturn('street2');

		$data['N4']->shouldReceive('element')
			->once()
			->with('N401')
			->andReturn($storeFacility['city']);

		$data['N4']->shouldReceive('element')
			->once()
			->with('N402')
			->andReturn($storeFacility['state']);

		$data['N4']->shouldReceive('element')
			->once()
			->with('N403')
			->andReturn($storeFacility['postal_code']);

		$data['REF']->shouldReceive('element')
			->once()
			->with('REF02')
			->andReturn($storeFacility['federal_ein']);

		$this->cache->shouldAllowMockingProtectedMethods()
			->shouldReceive('store->storeFacility')
			->once()
			->with($storeFacility)
			->andReturn('storeFacility');

		$this->assertEquals(
			'storeFacility',
			$this->cache->storeFacility($data),
			'Store Facility should have returned Id'
		);
	}

	/**
	 * @covers SunCoastConnection\ClaimsToOEMR\X12N837\Cache::storeFormEncounter()
	 */
	public function testStoreFormEncounterWithNoData() {
		$data = [];

		$this->cache->shouldAllowMockingProtectedMethods()
			->shouldNotReceive('store->storeFormEncounter');

		$this->assertNull(
			$this->cache->storeFormEncounter($data),
			'Store Form Encounter should not have returned Id'
		);
	}

	/**
	 * @covers SunCoastConnection\ClaimsToOEMR\X12N837\Cache::storeFormEncounter()
	 */
	public function testStoreFormEncounter() {
		$storeFormEncounter = [
			'reason' => 'Imported Encounter',
			'onset_date' => '0000-00-00 00:00:00',
			'sensitivity' => 'normal',
			'pc_catid' => 1,
			'last_level_billed' => 0,
			'last_level_closed' => 0,
			'stmt_count' => 0,
			'supervisor_id' => 0,
			'facility_id' => 'Facility',
			'billing_facility' => 'Facility',
			'provider_id' => 'User',
			'pid' => 'PatientData',
			'encounter' => 'encounter',
			'facility' => 'facility'
		];

		$data = [
			'Facility' => 'Facility',
			'User' => 'User',
			'PatientData' => 'PatientData',
			'CLM' => $this->getMockery(
				Segment\CLM::class
			),
			'NM1' => $this->getMockery(
				Segment\NM1::class
			)
		];

		$data['CLM']->shouldReceive('element')
			->once()
			->with('CLM01')
			->andReturn('%#!@^'.$storeFormEncounter['encounter'].'-_=`\':');

		$data['NM1']->shouldReceive('elementEquals')
			->once()
			->with('NM101', '85')
			->andReturn(true);

		$data['NM1']->shouldReceive('element')
			->once()
			->with('NM103')
			->andReturn($storeFormEncounter['facility']);

		$this->cache->shouldAllowMockingProtectedMethods()
			->shouldReceive('store->storeFormEncounter')
			->once()
			->with($storeFormEncounter)
			->andReturn('storeFormEncounter');

		$this->assertEquals(
			'storeFormEncounter',
			$this->cache->storeFormEncounter($data),
			'Store Form Encounter should have returned Id'
		);
	}

	/**
	 * @covers SunCoastConnection\ClaimsToOEMR\X12N837\Cache::storeFormEncounter()
	 */
	public function testStoreFormEncounterWithSegmentDTP_431() {
		$data = [
			'DTP-431' => $this->getMockery(
				Segment\DTP::class
			)
		];

		$data['DTP-431']->shouldReceive('element')
			->once()
			->with('DTP03')
			->andReturn('12345678');

		$this->cache->shouldAllowMockingProtectedMethods()
			->shouldReceive('store->storeFormEncounter')
			->once()
			->with(\Mockery::on(function($formEncounter) {
				return array_key_exists('onset_date', $formEncounter) &&
					$formEncounter['onset_date'] == '12345678';
			}))
			->andReturn('storeFormEncounter');

		$this->assertEquals(
			'storeFormEncounter',
			$this->cache->storeFormEncounter($data),
			'Store Form Encounter should have returned Id'
		);
	}

	/**
	 * @covers SunCoastConnection\ClaimsToOEMR\X12N837\Cache::storeFormEncounter()
	 */
	public function testStoreFormEncounterWithSegmentDTP02EqualsRD8() {
		$data = [
			'DTP' => $this->getMockery(
				Segment\DTP::class
			)
		];

		$data['DTP']->shouldReceive('elementEquals')
			->once()
			->with('DTP02', 'RD8')
			->andReturn(true);

		$data['DTP']->shouldReceive('element')
			->once()
			->with('DTP03')
			->andReturn('12345678-87654321');

		$this->cache->shouldAllowMockingProtectedMethods()
			->shouldReceive('store->storeFormEncounter')
			->once()
			->with(\Mockery::on(function($formEncounter) {
				return array_key_exists('date', $formEncounter) &&
					strlen($formEncounter['date']) == 14 &&
					substr($formEncounter['date'], 0, 8) == '12345678';
			}))
			->andReturn('storeFormEncounter');

		$this->assertEquals(
			'storeFormEncounter',
			$this->cache->storeFormEncounter($data),
			'Store Form Encounter should have returned Id'
		);
	}

	/**
	 * @covers SunCoastConnection\ClaimsToOEMR\X12N837\Cache::storeFormEncounter()
	 */
	public function testStoreFormEncounterWithSegmentDTP02NotEqualsRD8() {
		$data = [
			'DTP' => $this->getMockery(
				Segment\DTP::class
			)
		];

		$data['DTP']->shouldReceive('elementEquals')
			->once()
			->with('DTP02', 'RD8')
			->andReturn(false);

		$data['DTP']->shouldReceive('element')
			->once()
			->with('DTP03')
			->andReturn('12345678');

		$this->cache->shouldAllowMockingProtectedMethods()
			->shouldReceive('store->storeFormEncounter')
			->once()
			->with(\Mockery::on(function($formEncounter) {
				return array_key_exists('date', $formEncounter) &&
					strlen($formEncounter['date']) == 14 &&
					substr($formEncounter['date'], 0, 8) == '12345678';
			}))
			->andReturn('storeFormEncounter');

		$this->assertEquals(
			'storeFormEncounter',
			$this->cache->storeFormEncounter($data),
			'Store Form Encounter should have returned Id'
		);
	}

	/**
	 * @covers SunCoastConnection\ClaimsToOEMR\X12N837\Cache::storeForm()
	 */
	public function testStoreFormWithNoData() {
		$data = [];

		$this->cache->shouldAllowMockingProtectedMethods()
			->shouldNotReceive('store->storeForm');

		$this->assertNull(
			$this->cache->storeForm($data),
			'Store Form should not have returned Id'
		);
	}

	/**
	 * @covers SunCoastConnection\ClaimsToOEMR\X12N837\Cache::storeForm()
	 */
	public function testStoreForm() {
		$storeForm = [
			'form_name' => 'New Patient Encounter',
			'groupname' => 'Default',
			'authorized' => 1,
			'deleted' => 0,
			'formdir' => 'newpatient',
			'form_id' => 'FormEncounter',
			'user' => 'User',
			'pid' => 'PatientData',
			'encounter' => 'encounter'
		];

		$data = [
			'FormEncounter' => 'FormEncounter',
			'User' => 'User',
			'PatientData' => 'PatientData',
			'CLM' => $this->getMockery(
				Segment\CLM::class
			)
		];

		$data['CLM']->shouldReceive('element')
			->once()
			->with('CLM01')
			->andReturn($storeForm['encounter']);

		$this->cache->shouldAllowMockingProtectedMethods()
			->shouldReceive('store->storeForm')
			->once()
			->with($storeForm)
			->andReturn('storeForm');

		$this->assertEquals(
			'storeForm',
			$this->cache->storeForm($data),
			'Store Form should have returned Id'
		);
	}

	/**
	 * @covers SunCoastConnection\ClaimsToOEMR\X12N837\Cache::storeForm()
	 */
	public function testStoreFormWithSegmentDTP02EqualsRD8() {
		$data = [
			'DTP' => $this->getMockery(
				Segment\DTP::class
			)
		];

		$data['DTP']->shouldReceive('elementEquals')
			->once()
			->with('DTP02', 'RD8')
			->andReturn(true);

		$data['DTP']->shouldReceive('element')
			->once()
			->with('DTP03')
			->andReturn('12345678-87654321');

		$this->cache->shouldAllowMockingProtectedMethods()
			->shouldReceive('store->storeForm')
			->once()
			->with(\Mockery::on(function($form) {
				return array_key_exists('date', $form) &&
					strlen($form['date']) == 14 &&
					substr($form['date'], 0, 8) == '12345678';
			}))
			->andReturn('storeForm');

		$this->assertEquals(
			'storeForm',
			$this->cache->storeForm($data),
			'Store Form should have returned Id'
		);
	}

	/**
	 * @covers SunCoastConnection\ClaimsToOEMR\X12N837\Cache::storeForm()
	 */
	public function testStoreFormWithSegmentDTP02NotEqualsRD8() {
		$data = [
			'DTP' => $this->getMockery(
				Segment\DTP::class
			)
		];

		$data['DTP']->shouldReceive('elementEquals')
			->once()
			->with('DTP02', 'RD8')
			->andReturn(false);

		$data['DTP']->shouldReceive('element')
			->once()
			->with('DTP03')
			->andReturn('12345678');

		$this->cache->shouldAllowMockingProtectedMethods()
			->shouldReceive('store->storeForm')
			->once()
			->with(\Mockery::on(function($form) {
				return array_key_exists('date', $form) &&
					strlen($form['date']) == 14 &&
					substr($form['date'], 0, 8) == '12345678';
			}))
			->andReturn('storeForm');

		$this->assertEquals(
			'storeForm',
			$this->cache->storeForm($data),
			'Store Form should have returned Id'
		);
	}

	/**
	 * @covers SunCoastConnection\ClaimsToOEMR\X12N837\Cache::storeGroup()
	 */
	public function testStoreGroupWithNoData() {
		$data = [];

		$this->cache->shouldAllowMockingProtectedMethods()
			->shouldNotReceive('store->storeGroup');

		$this->assertNull(
			$this->cache->storeGroup($data),
			'Store Group should not have returned Id'
		);
	}

	/**
	 * @covers SunCoastConnection\ClaimsToOEMR\X12N837\Cache::storeGroup()
	 */
	public function testStoreGroup() {
		$storeGroup = [
			'name' => 'Default',
			'user' => 'lnamefname'
		];

		$data = [
			'NM1' => $this->getMockery(
				Segment\NM1::class
			)
		];

		$data['NM1']->shouldReceive('element')
			->once()
			->with('NM103')
			->andReturn('lname');

		$data['NM1']->shouldReceive('element')
			->once()
			->with('NM104')
			->andReturn('fname');

		$this->cache->shouldAllowMockingProtectedMethods()
			->shouldReceive('store->storeGroup')
			->once()
			->with($storeGroup)
			->andReturn('storeGroup');

		$this->assertEquals(
			'storeGroup',
			$this->cache->storeGroup($data),
			'Store Group should have returned Id'
		);
	}

	/**
	 * @covers SunCoastConnection\ClaimsToOEMR\X12N837\Cache::storeInsuranceCompany()
	 */
	public function testStoreInsuranceCompanyWithNoData() {
		$data = [];

		$this->cache->shouldAllowMockingProtectedMethods()
			->shouldNotReceive('store->storeInsuranceCompany');

		$this->assertNull(
			$this->cache->storeInsuranceCompany($data),
			'Store Insurance Company should not have returned Id'
		);
	}

	/**
	 * @covers SunCoastConnection\ClaimsToOEMR\X12N837\Cache::storeInsuranceCompany()
	 */
	public function testStoreInsuranceCompany() {
		$storeInsuranceCompany = [
			'attn' => 'Claims',
			'x12_receiver_id' => 'X12Partner',
			'x12_default_partner_id' => 'X12Partner',
			'name' => 'name',
			'cms_id' => 'cms_id'
		];

		$data = [
			'X12Partner' => 'X12Partner',
			'NM1' => $this->getMockery(
				Segment\NM1::class
			)
		];

		$data['NM1']->shouldReceive('element')
			->once()
			->with('NM103')
			->andReturn($storeInsuranceCompany['name']);

		$data['NM1']->shouldReceive('element')
			->once()
			->with('NM109')
			->andReturn($storeInsuranceCompany['cms_id']);

		$this->cache->shouldAllowMockingProtectedMethods()
			->shouldReceive('store->storeInsuranceCompany')
			->once()
			->with($storeInsuranceCompany)
			->andReturn('storeInsuranceCompany');

		$this->assertEquals(
			'storeInsuranceCompany',
			$this->cache->storeInsuranceCompany($data),
			'Store Insurance Company should have returned Id'
		);
	}

	/**
	 * @covers SunCoastConnection\ClaimsToOEMR\X12N837\Cache::storeInsuranceData()
	 */
	public function testStoreInsuranceDataWithNoData() {
		$data = [];

		$this->cache->shouldAllowMockingProtectedMethods()
			->shouldNotReceive('store->storeInsuranceData');

		$this->assertNull(
			$this->cache->storeInsuranceData($data),
			'Store Insurance Data should not have returned Id'
		);
	}

	/**
	 * @covers SunCoastConnection\ClaimsToOEMR\X12N837\Cache::storeInsuranceData()
	 */
	public function testStoreInsuranceData() {
		$storeInsuranceData = [
			'subscriber_country' => 'USA',
			'pid' => 'PatientData',
			'date' => '12340101010101',
			'type' => 'type',
			'group_number' => 'group_number',
			'plan_name' => 'plan_name',
			'subscriber_relationship' => 'self',
			'subscriber_lname' => 'subscriber_lname',
			'subscriber_fname' => 'subscriber_fname',
			'subscriber_mname' => 'subscriber_mname',
			'policy_number' => 'policy_number',
			'subscriber_street' => 'street1 street2',
			'subscriber_city' => 'subscriber_city',
			'subscriber_state' => 'subscriber_state',
			'subscriber_postal_code' => 'subscriber_postal_code',
			'accept_assignment' => 'accept_assignment',
			'subscriber_DOB' => 'subscriber_DOB',
			'subscriber_sex' => 'subscriber_sex'
		];

		$data = [
			'PatientData' => 'PatientData',
			'GS' => $this->getMockery(
				Segment\GS::class
			),
			'SBR' => $this->getMockery(
				Segment\SBR::class
			),
			'NM1' => $this->getMockery(
				Segment\NM1::class
			),
			'N3' => $this->getMockery(
				Segment\N3::class
			),
			'N4' => $this->getMockery(
				Segment\N4::class
			),
			'CLM' => $this->getMockery(
				Segment\CLM::class
			),
			'DMG' => $this->getMockery(
				Segment\DMG::class
			),
		];

		$data['GS']->shouldReceive('element')
			->once()
			->with('GS04')
			->andReturn('12345678');

		$data['SBR']->shouldReceive('element')
			->once()
			->with('SBR01')
			->andReturn($storeInsuranceData['type']);

		$data['SBR']->shouldReceive('element')
			->once()
			->with('SBR03')
			->andReturn($storeInsuranceData['group_number']);

		$data['SBR']->shouldReceive('element')
			->once()
			->with('SBR04')
			->andReturn($storeInsuranceData['plan_name']);

		$data['SBR']->shouldReceive('element')
			->once()
			->with('SBR02')
			->andReturn(18);

		$data['NM1']->shouldReceive('element')
			->once()
			->with('NM103')
			->andReturn($storeInsuranceData['subscriber_lname']);

		$data['NM1']->shouldReceive('element')
			->once()
			->with('NM104')
			->andReturn($storeInsuranceData['subscriber_fname']);

		$data['NM1']->shouldReceive('element')
			->once()
			->with('NM105')
			->andReturn($storeInsuranceData['subscriber_mname']);

		$data['NM1']->shouldReceive('element')
			->once()
			->with('NM109')
			->andReturn($storeInsuranceData['policy_number']);

		$data['N3']->shouldReceive('element')
			->once()
			->with('N301')
			->andReturn('street1');

		$data['N3']->shouldReceive('element')
			->once()
			->with('N302')
			->andReturn('street2');

		$data['N4']->shouldReceive('element')
			->once()
			->with('N401')
			->andReturn($storeInsuranceData['subscriber_city']);

		$data['N4']->shouldReceive('element')
			->once()
			->with('N402')
			->andReturn($storeInsuranceData['subscriber_state']);

		$data['N4']->shouldReceive('element')
			->once()
			->with('N403')
			->andReturn($storeInsuranceData['subscriber_postal_code']);

		$data['CLM']->shouldReceive('element')
			->once()
			->with('CLM08')
			->andReturn($storeInsuranceData['accept_assignment']);

		$data['DMG']->shouldReceive('element')
			->once()
			->with('DMG02')
			->andReturn($storeInsuranceData['subscriber_DOB']);

		$data['DMG']->shouldReceive('element')
			->once()
			->with('DMG03')
			->andReturn($storeInsuranceData['subscriber_sex']);

		$this->cache->shouldAllowMockingProtectedMethods()
			->shouldReceive('store->storeInsuranceData')
			->once()
			->with($storeInsuranceData)
			->andReturn('storeInsuranceData');

		$this->assertEquals(
			'storeInsuranceData',
			$this->cache->storeInsuranceData($data),
			'Store Insurance Data should have returned Id'
		);
	}

	/**
	 * @covers SunCoastConnection\ClaimsToOEMR\X12N837\Cache::storePatientData()
	 */
	public function testStorePatientDataWithNoData() {
		$data = [];

		$this->cache->shouldAllowMockingProtectedMethods()
			->shouldNotReceive('store->storePatientData');

		$this->assertNull(
			$this->cache->storePatientData($data),
			'Store Patient Data should not have returned Id'
		);
	}

	/**
	 * @covers SunCoastConnection\ClaimsToOEMR\X12N837\Cache::storePatientData()
	 */
	public function testStorePatientData() {
		$storePatientData = [
			'language' => 'English',
			'providerID' => 'User',
			'date' => '12340101010101',
			'lname' => 'lname',
			'fname' => 'fname',
			'mname' => 'mname',
			'street' => 'street1 street2',
			'city' => 'city',
			'state' => 'state',
			'postal_code' => 'postal_code',
			'DOB' => 'DOB',
			'sex' => 'Female',
		];

		$data = [
			'User' => 'User',
			'GS' => $this->getMockery(
				Segment\GS::class
			),
			'NM1' => $this->getMockery(
				Segment\NM1::class
			),
			'N3' => $this->getMockery(
				Segment\N3::class
			),
			'N4' => $this->getMockery(
				Segment\N4::class
			),
			'DMG' => $this->getMockery(
				Segment\DMG::class
			),
		];

		$data['GS']->shouldReceive('element')
			->once()
			->with('GS04')
			->andReturn('12345678');

		$data['NM1']->shouldReceive('element')
			->once()
			->with('NM103')
			->andReturn($storePatientData['lname']);

		$data['NM1']->shouldReceive('element')
			->once()
			->with('NM104')
			->andReturn($storePatientData['fname']);

		$data['NM1']->shouldReceive('element')
			->once()
			->with('NM105')
			->andReturn($storePatientData['mname']);

		$data['N3']->shouldReceive('element')
			->once()
			->with('N301')
			->andReturn('street1');

		$data['N3']->shouldReceive('element')
			->once()
			->with('N302')
			->andReturn('street2');

		$data['N4']->shouldReceive('element')
			->once()
			->with('N401')
			->andReturn($storePatientData['city']);

		$data['N4']->shouldReceive('element')
			->once()
			->with('N402')
			->andReturn($storePatientData['state']);

		$data['N4']->shouldReceive('element')
			->once()
			->with('N403')
			->andReturn($storePatientData['postal_code']);

		$data['DMG']->shouldReceive('element')
			->once()
			->with('DMG02')
			->andReturn($storePatientData['DOB']);

		$data['DMG']->shouldReceive('element')
			->once()
			->with('DMG03')
			->andReturn('f');

		$this->cache->shouldAllowMockingProtectedMethods()
			->shouldReceive('store->storePatientData')
			->once()
			->with($storePatientData)
			->andReturn('storePatientData');

		$this->assertEquals(
			'storePatientData',
			$this->cache->storePatientData($data),
			'Store Patient Data should have returned Id'
		);
	}

	/**
	 * @covers SunCoastConnection\ClaimsToOEMR\X12N837\Cache::storePhoneNumber()
	 */
	public function testStorePhoneNumberWithNoData() {
		$data = [];

		$this->cache->shouldAllowMockingProtectedMethods()
			->shouldNotReceive('store->storePhoneNumber');

		$this->assertNull(
			$this->cache->storePhoneNumber($data),
			'Store Phone Number should not have returned Id'
		);
	}

	/**
	 * @covers SunCoastConnection\ClaimsToOEMR\X12N837\Cache::storePhoneNumber()
	 */
	public function testStorePhoneNumber() {
		$storePhoneNumber = [
			'country_code' => '+1',
			'type' => '2',
			'foreign_id' => 'InsuranceCompany',
		];

		$data = [
			'InsuranceCompany' => 'InsuranceCompany',
		];

		$this->cache->shouldAllowMockingProtectedMethods()
			->shouldReceive('store->storePhoneNumber')
			->once()
			->with($storePhoneNumber)
			->andReturn('storePhoneNumber');

		$this->assertEquals(
			'storePhoneNumber',
			$this->cache->storePhoneNumber($data),
			'Store Phone Number should have returned Id'
		);
	}

	/**
	 * @covers SunCoastConnection\ClaimsToOEMR\X12N837\Cache::storeUser()
	 */
	public function testStoreUserWithNoData() {
		$data = [];

		$this->cache->shouldAllowMockingProtectedMethods()
			->shouldNotReceive('store->storeUser');

		$this->assertNull(
			$this->cache->storeUser($data),
			'Store User should not have returned Id'
		);
	}

	/**
	 * @covers SunCoastConnection\ClaimsToOEMR\X12N837\Cache::storeUser()
	 */
	public function testStoreUser() {
		$storeUser = [
			'password' => '70702b9402107c11ef9d18d9daad4ff1',
			'authorized' => 1,
			'federaltaxid' => '',
			'federaldrugid' => '',
			'active' => 1,
			'cal_ui' => 3,
			'taxonomy' => 'taxonomy',
			'calendar' => 1,
			'abook_type' => 'miscellaneous',
			'state_license_number' => '',
			'facility_id' => 'facility_id',
			'username' => 'lnamefname',
			'lname' => 'lname',
			'fname' => 'fname',
			'mname' => 'mname',
			'npi' => 'npi',
		];

		$data = [
			'Facility' => $storeUser['facility_id'],
			'NM1' => $this->getMockery(
				Segment\NM1::class
			),
			'PRV' => $this->getMockery(
				Segment\PRV::class
			)
		];

		$data['NM1']->shouldReceive('element')
			->twice()
			->with('NM103')
			->andReturn($storeUser['lname']);

		$data['NM1']->shouldReceive('element')
			->twice()
			->with('NM104')
			->andReturn($storeUser['fname']);

		$data['NM1']->shouldReceive('element')
			->once()
			->with('NM105')
			->andReturn($storeUser['mname']);

		$data['NM1']->shouldReceive('element')
			->once()
			->with('NM109')
			->andReturn($storeUser['npi']);

		$data['PRV']->shouldReceive('elementEquals')
			->once()
			->with('PRV01', 'BI')
			->andReturn(true);

		$data['PRV']->shouldReceive('element')
			->once()
			->with('PRV03')
			->andReturn($storeUser['taxonomy']);

		$this->cache->shouldAllowMockingProtectedMethods()
			->shouldReceive('store->storeUser')
			->once()
			->with($storeUser)
			->andReturn('storeUser');

		$this->assertEquals(
			'storeUser',
			$this->cache->storeUser($data),
			'Store User should have returned Id'
		);
	}

	/**
	 * @covers SunCoastConnection\ClaimsToOEMR\X12N837\Cache::storeX12Partner()
	 */
	public function testStoreX12PartnerWithNoData() {
		$this->cache->shouldAllowMockingProtectedMethods()
			->shouldNotReceive('store->storeX12Partner');

		$data = [];

		$this->assertNull(
			$this->cache->storeX12Partner($data),
			'Store X12Partner should not have returned Id'
		);
	}

	/**
	 * @covers SunCoastConnection\ClaimsToOEMR\X12N837\Cache::storeX12Partner()
	 */
	public function testStoreX12Partner() {
		$storeX12Partner = [
			'name' => 'name',
			'id_number' => 'id_number',
			'x12_sender_id' => 'x12_sender_id',
			'x12_receiver_id' => 'x12_receiver_id',
			'x12_version' => 'x12_version',
			'x12_isa01' => 'x12_isa01',
			'x12_isa02' => 'x12_isa02',
			'x12_isa03' => 'x12_isa03',
			'x12_isa04' => 'x12_isa04',
			'x12_isa05' => 'x12_isa05',
			'x12_isa07' => 'x12_isa07',
			'x12_isa14' => 'x12_isa14',
			'x12_isa15' => 'x12_isa15',
			'x12_gs02' => 'x12_gs02',
			'x12_gs03' => 'x12_gs03',
		];

		$data = [
			'ISA' => $this->getMockery(
				Segment\ISA::class
			),
			'GS' => $this->getMockery(
				Segment\GS::class
			),
			'NM1' => $this->getMockery(
				Segment\NM1::class
			)
		];

		$data['ISA']->shouldReceive('element')
			->once()
			->with('ISA06')
			->andReturn($storeX12Partner['x12_sender_id']);

		$data['ISA']->shouldReceive('element')
			->once()
			->with('ISA08')
			->andReturn($storeX12Partner['x12_receiver_id']);

		$data['ISA']->shouldReceive('element')
			->once()
			->with('ISA01')
			->andReturn($storeX12Partner['x12_isa01']);

		$data['ISA']->shouldReceive('element')
			->once()
			->with('ISA02')
			->andReturn($storeX12Partner['x12_isa02']);

		$data['ISA']->shouldReceive('element')
			->once()
			->with('ISA03')
			->andReturn($storeX12Partner['x12_isa03']);

		$data['ISA']->shouldReceive('element')
			->once()
			->with('ISA04')
			->andReturn($storeX12Partner['x12_isa04']);

		$data['ISA']->shouldReceive('element')
			->once()
			->with('ISA05')
			->andReturn($storeX12Partner['x12_isa05']);

		$data['ISA']->shouldReceive('element')
			->once()
			->with('ISA07')
			->andReturn($storeX12Partner['x12_isa07']);

		$data['ISA']->shouldReceive('element')
			->once()
			->with('ISA14')
			->andReturn($storeX12Partner['x12_isa14']);

		$data['ISA']->shouldReceive('element')
			->once()
			->with('ISA15')
			->andReturn($storeX12Partner['x12_isa15']);

		$data['GS']->shouldReceive('element')
			->once()
			->with('GS08')
			->andReturn($storeX12Partner['x12_version']);

		$data['GS']->shouldReceive('element')
			->once()
			->with('GS02')
			->andReturn($storeX12Partner['x12_gs02']);

		$data['GS']->shouldReceive('element')
			->once()
			->with('GS03')
			->andReturn($storeX12Partner['x12_gs03']);

		$data['NM1']->shouldReceive('element')
			->once()
			->with('NM103')
			->andReturn($storeX12Partner['name']);

		$data['NM1']->shouldReceive('element')
			->once()
			->with('NM109')
			->andReturn($storeX12Partner['id_number']);

		$this->cache->shouldAllowMockingProtectedMethods()
			->shouldReceive('store->storeX12Partner')
			->once()
			->with($storeX12Partner)
			->andReturn('storeX12Partner');

		$this->assertEquals(
			'storeX12Partner',
			$this->cache->storeX12Partner($data),
			'Store X12Partner should have returned Id'
		);
	}

	/**
	 * @covers SunCoastConnection\ClaimsToOEMR\X12N837\Cache::existsAdd()
	 */
	public function testExistsAddWithMissingIndex() {
		$findArray = [
			'A' => '1',
			'B' => '2',
			'C' => '3',
		];

		$addArray = [];

		$this->callProtectedMethod(
			$this->cache,
			'existsAdd',
			[ 'D', &$findArray, 'Four', &$addArray ]
		);

		$this->assertArrayNotHasKey(
			'Four',
			$addArray,
			'Index should not have been found and added to array'
		);
	}

	/**
	 * @covers SunCoastConnection\ClaimsToOEMR\X12N837\Cache::existsAdd()
	 */
	public function testExistsAddWithExistingIndex() {
		$findArray = [
			'A' => '1',
			'B' => '2',
			'C' => '3',
			'D' => '4',
		];

		$addArray = [];

		$this->callProtectedMethod(
			$this->cache,
			'existsAdd',
			[ 'D', &$findArray, 'Four', &$addArray ]
		);

		$this->assertArrayHasKey(
			'Four',
			$addArray,
			'Index should have been found and added to array'
		);

		$this->assertEquals(
			$findArray['D'],
			$addArray['Four'],
			'Stored value for found index not returned correctly'
		);
	}

	/**
	 * @covers SunCoastConnection\ClaimsToOEMR\X12N837\Cache::findNextSegment()
	 */
	public function testFindNextSegment() {
		$segmentGroup = [
			'AMT' => $this->getMockery(
				Segment\AMT::class
			),
			'BHT' => $this->getMockery(
				Segment\BHT::class
			),
		];

		$segmentGroup['AMT']->shouldReceive('getName')
			->once()
			->andReturn('AMT');

		$segmentGroup['BHT']->shouldReceive('getName')
			->once()
			->andReturn('BHT');

		$segmentMatches = [
			'AMT',
			'BHT',
		];

		$this->assertSame(
			$segmentGroup['AMT'],
			$this->callProtectedMethod(
				$this->cache,
				'findNextSegment',
				[ &$segmentGroup, $segmentMatches ]
			)
		);

		$this->assertSame(
			$segmentGroup['BHT'],
			$this->callProtectedMethod(
				$this->cache,
				'findNextSegment',
				[ &$segmentGroup, $segmentMatches ]
			)
		);

		$this->assertNull(
			$this->callProtectedMethod(
				$this->cache,
				'findNextSegment',
				[ &$segmentGroup, $segmentMatches ]
			)
		);
	}

	/**
	 * @covers SunCoastConnection\ClaimsToOEMR\X12N837\Cache::processDocument()
	 */
	public function testProcessDocumentWithNoDescendantArray() {
		$document = $this->getMockery(
			Document::class
		);

		$document->shouldReceive('getDescendant')
			->once()
			->andReturnNull();

		$this->cache->shouldAllowMockingProtectedMethods()
			->shouldNotReceive('processInterchangeControl');

		$this->cache->processDocument($document);
	}

	/**
	 * @covers SunCoastConnection\ClaimsToOEMR\X12N837\Cache::processDocument()
	 */
	public function testProcessDocumentWithEmptyDescendantArray() {
		$descendant = [];

		$document = $this->getMockery(
			Document::class
		);

		$document->shouldReceive('getDescendant')
			->once()
			->andReturn($descendant);

		$this->cache->shouldAllowMockingProtectedMethods()
			->shouldNotReceive('processInterchangeControl');

		$this->cache->processDocument($document);
	}

	/**
	 * @covers SunCoastConnection\ClaimsToOEMR\X12N837\Cache::processDocument()
	 */
	public function testProcessDocumentWithDescendantArray() {
		$interchangeControl = $this->getMockery(
			Envelope\InterchangeControl::class
		);

		$descendant = [
			$interchangeControl,
			$interchangeControl
		];

		$document = $this->getMockery(
			Document::class
		);

		$document->shouldReceive('getDescendant')
			->once()
			->andReturn($descendant);

		$this->cache->shouldAllowMockingProtectedMethods()
			->shouldReceive('processInterchangeControl')
			->twice()
			->with($interchangeControl);

		$this->cache->processDocument($document);
	}

	/**
	 * @covers SunCoastConnection\ClaimsToOEMR\X12N837\Cache::processInterchangeControl()
	 */
	public function testProcessInterchangeControl() {
		$interchangeControl = $this->getMockery(
			Envelope\InterchangeControl::class
		);

		$isa = $this->getMockery(
			Segment\ISA::class
		);

		$header = [
			$isa
		];

		$interchangeControl->shouldReceive('getHeader')
			->once()
			->andReturn($header);

		$this->cache->shouldAllowMockingProtectedMethods();

		$this->cache->shouldReceive('findNextSegment')
			->once()
			->with($header, [ 'ISA' ])
			->andReturn($isa);

		$descendant = [
			$this->getMockery(
				Envelope\FunctionalGroup::class
			)
		];

		$interchangeControl->shouldReceive('getDescendant')
			->once()
			->andReturn($descendant);

		$this->cache->shouldReceive('processFunctionalGroup')
			->once()
			->with($descendant[0], [ 'ISA' => $isa ]);

		$this->cache->processInterchangeControl($interchangeControl);
	}

	/**
	 * @covers SunCoastConnection\ClaimsToOEMR\X12N837\Cache::processFunctionalGroup()
	 */
	public function testProcessFunctionalGroup() {
		$functionalGroup = $this->getMockery(
			Envelope\FunctionalGroup::class
		);

		$gs = $this->getMockery(
			Segment\GS::class
		);

		$header = [
			$gs
		];

		$functionalGroup->shouldReceive('getHeader')
			->once()
			->andReturn($header);

		$this->cache->shouldAllowMockingProtectedMethods();

		$this->cache->shouldReceive('findNextSegment')
			->once()
			->with($header, [ 'GS' ])
			->andReturn($gs);

		$descendant = [
			$this->getMockery(
				Envelope\TransactionSet::class
			)
		];

		$functionalGroup->shouldReceive('getDescendant')
			->once()
			->andReturn($descendant);

		$isa = $this->getMockery(
			Segment\ISA::class
		);

		$this->cache->shouldReceive('processTransactionSet')
			->once()
			->with($descendant[0], [ 'ISA' => $isa, 'GS' => $gs ]);

		$data = [ 'ISA' => $isa ];

		$this->callProtectedMethod(
			$this->cache,
			'processFunctionalGroup',
			[
				$functionalGroup,
				&$data
			]
		);
	}

	/**
	 * @covers SunCoastConnection\ClaimsToOEMR\X12N837\Cache::processTransactionSet()
	 */
	public function testProcessTransactionSet() {
		$transactionSet = $this->getMockery(
			Envelope\TransactionSet::class
		);

		$bht = $this->getMockery(
			Segment\BHT::class
		);

		$header = [
			$bht
		];

		$transactionSet->shouldReceive('getHeader')
			->once()
			->andReturn($header);

		$this->cache->shouldAllowMockingProtectedMethods();

		$this->cache->shouldReceive('findNextSegment')
			->once()
			->with($header, [ 'BHT' ])
			->andReturn($bht);

		$bht->shouldReceive('elementEquals')
			->once()
			->with('BHT06', 'RP')
			->andReturn(false);

		$descendant = [
			$this->getMockery(
				Loop\Loop1000::class
			),
			$this->getMockery(
				Loop\Loop2000::class
			)
		];

		$transactionSet->shouldReceive('getDescendant')
			->once()
			->andReturn($descendant);

		$descendant[0]->shouldReceive('getName')
			->once()
			->andReturn('Loop1000');

		$descendant[1]->shouldReceive('getName')
			->once()
			->andReturn('Loop2000');

		$data = [
			'ISA' => $this->getMockery(
				Segment\ISA::class
			),
			'GS' => $this->getMockery(
				Segment\GS::class
			)
		];

		$this->cache->shouldReceive('processLoop1000')
			->once()
			->with($descendant[0], $data);

		$this->cache->shouldReceive('processLoop2000')
			->once()
			->with($descendant[1], $data);

		$this->callProtectedMethod(
			$this->cache,
			'processTransactionSet',
			[
				$transactionSet,
				&$data
			]
		);
	}

	/**
	 * @covers SunCoastConnection\ClaimsToOEMR\X12N837\Cache::processLoop1000()
	 */
	public function testProcessLoop1000WithSegmentNM101_40() {
		$mockObjects = $this->setupTestProcessLoop(
			Loop\Loop1000::class,
			[ 'NM1', 'N3', 'N4', 'REF' ],
			Segment\NM1::class
		);

		$mockObjects['segments'][0]->shouldReceive('element')
			->once()
			->with('NM101')
			->andReturn('40');

		$mockObjects['segments'][0]->shouldReceive('elementEquals')
			->once()
			->with('NM102', '2')
			->andReturn(true);

		$data = [
			'ISA' => $this->getMockery(
				Segment\ISA::class
			),
			'GS' => $this->getMockery(
				Segment\GS::class
			),
		];

		$this->cache->shouldReceive('storeX12Partner')
			->once()
			->with([
				'NM1' => $mockObjects['segments'][0],
				'ISA' => $data['ISA'],
				'GS' => $data['GS']
			])
			->andReturn(123);

		$this->callProtectedMethod(
			$this->cache,
			'processLoop1000',
			[
				$mockObjects['loop'],
				&$data
			]
		);

		$this->assertSame(
			$mockObjects['segments'][0],
			$data['Loop1000_NM1'],
			'Loop1000 NM1 was not set correctly'
		);

		$this->assertEquals(
			123,
			$data['CurrentX12Partner'],
			'Current X12Partner was not set correctly'
		);
	}

	/**
	 * @covers SunCoastConnection\ClaimsToOEMR\X12N837\Cache::processLoop1000()
	 */
	public function testProcessLoop1000WithSegmentNM101_41() {
		$mockObjects = $this->setupTestProcessLoop(
			Loop\Loop1000::class,
			[ 'NM1', 'N3', 'N4', 'REF' ],
			Segment\NM1::class
		);

		$mockObjects['segments'][0]->shouldReceive('element')
			->once()
			->with('NM101')
			->andReturn('41');

		$this->cache->shouldReceive('existsAdd')
			->times(3);

		$this->cache->shouldReceive('storeFacility')
			->once()
			->andReturn(123);

		$data = [];

		$this->callProtectedMethod(
			$this->cache,
			'processLoop1000',
			[
				$mockObjects['loop'],
				&$data
			]
		);

		$this->assertSame(
			$mockObjects['segments'][0],
			$data['Loop1000_NM1'],
			'Loop1000 NM1 was not set correctly'
		);

		$this->assertEquals(
			123,
			$data['CurrentFacility'],
			'Current Facility was not set correctly'
		);
	}

	/**
	 * @covers SunCoastConnection\ClaimsToOEMR\X12N837\Cache::processLoop1000()
	 */
	public function testProcessLoop1000WithSegmentN3() {
		$mockObjects = $this->setupTestProcessLoop(
			Loop\Loop1000::class,
			[ 'NM1', 'N3', 'N4', 'REF' ],
			Segment\N3::class
		);

		$data = [];

		$this->callProtectedMethod(
			$this->cache,
			'processLoop1000',
			[
				$mockObjects['loop'],
				&$data
			]
		);

		$this->assertSame(
			$mockObjects['segments'][0],
			$data['Loop1000_N3'],
			'Loop1000 N3 was not set correctly'
		);
	}

	/**
	 * @covers SunCoastConnection\ClaimsToOEMR\X12N837\Cache::processLoop1000()
	 */
	public function testProcessLoop1000WithSegmentN4() {
		$mockObjects = $this->setupTestProcessLoop(
			Loop\Loop1000::class,
			[ 'NM1', 'N3', 'N4', 'REF' ],
			Segment\N4::class
		);

		$data = [];

		$this->callProtectedMethod(
			$this->cache,
			'processLoop1000',
			[
				$mockObjects['loop'],
				&$data
			]
		);

		$this->assertSame(
			$mockObjects['segments'][0],
			$data['Loop1000_N4'],
			'Loop1000 N4 was not set correctly'
		);
	}

	/**
	 * @covers SunCoastConnection\ClaimsToOEMR\X12N837\Cache::processLoop1000()
	 */
	public function testProcessLoop1000WithSegmentREF() {
		$mockObjects = $this->setupTestProcessLoop(
			Loop\Loop1000::class,
			[ 'NM1', 'N3', 'N4', 'REF' ],
			Segment\REF::class
		);

		$data = [];

		$this->callProtectedMethod(
			$this->cache,
			'processLoop1000',
			[
				$mockObjects['loop'],
				&$data
			]
		);

		$this->assertSame(
			$mockObjects['segments'][0],
			$data['Loop1000_REF'],
			'Loop1000 REF was not set correctly'
		);
	}

	/**
	 * @covers SunCoastConnection\ClaimsToOEMR\X12N837\Cache::processLoop2000()
	 */
	public function testProcessLoop2000WithDescendantAndNoSegment() {
		$mockObjects = $this->setupTestProcessLoop(
			Loop\Loop2000::class,
			[ 'PRV', 'SBR', 'PAT' ]
		);

		$descendant = [
			$this->getMockery(
				Loop\Loop2010::class
			),
			$this->getMockery(
				Loop\Loop2300::class
			)
		];

		$mockObjects['loop']->shouldReceive('getDescendant')
			->once()
			->andReturn($descendant);

		$descendant[0]->shouldReceive('getName')
			->once()
			->andReturn('Loop2010');

		$descendant[1]->shouldReceive('getName')
			->once()
			->andReturn('Loop2300');

		$data = [];

		$this->cache->shouldReceive('processLoop2010')
			->once()
			->with($descendant[0], $data);

		$this->cache->shouldReceive('processLoop2300')
			->once()
			->with($descendant[1], $data);

		$this->callProtectedMethod(
			$this->cache,
			'processLoop2000',
			[
				$mockObjects['loop'],
				&$data
			]
		);
	}

	/**
	 * @covers SunCoastConnection\ClaimsToOEMR\X12N837\Cache::processLoop2000()
	 */
	public function testProcessLoop2000WithSegmentPRV() {
		$mockObjects = $this->setupTestProcessLoop(
			Loop\Loop2000::class,
			[ 'PRV', 'SBR', 'PAT' ],
			Segment\PRV::class
		);

		$data = [];

		$mockObjects['loop']->shouldReceive('getDescendant')
			->once()
			->andReturnNull();

		$this->callProtectedMethod(
			$this->cache,
			'processLoop2000',
			[
				$mockObjects['loop'],
				&$data
			]
		);

		$this->assertSame(
			$mockObjects['segments'][0],
			$data['Loop2000_PRV'],
			'Loop2000 PRV was not set correctly'
		);
	}

	/**
	 * @covers SunCoastConnection\ClaimsToOEMR\X12N837\Cache::processLoop2000()
	 */
	public function testProcessLoop2000WithSegmentSBR() {
		$mockObjects = $this->setupTestProcessLoop(
			Loop\Loop2000::class,
			[ 'PRV', 'SBR', 'PAT' ],
			Segment\SBR::class
		);

		$mockObjects['loop']->shouldReceive('getDescendant')
			->once()
			->andReturnNull();

		$data = [];

		$this->callProtectedMethod(
			$this->cache,
			'processLoop2000',
			[
				$mockObjects['loop'],
				&$data
			]
		);

		$this->assertSame(
			$mockObjects['segments'][0],
			$data['Loop2000_SBR'],
			'Loop2000 SBR was not set correctly'
		);
	}

	/**
	 * @covers SunCoastConnection\ClaimsToOEMR\X12N837\Cache::processLoop2000()
	 */
	public function testProcessLoop2000WithSegmentPAT() {
		$mockObjects = $this->setupTestProcessLoop(
			Loop\Loop2000::class,
			[ 'PRV', 'SBR', 'PAT' ],
			Segment\PAT::class
		);

		$mockObjects['loop']->shouldReceive('getDescendant')
			->once()
			->andReturnNull();

		$data = [];

		$this->callProtectedMethod(
			$this->cache,
			'processLoop2000',
			[
				$mockObjects['loop'],
				&$data
			]
		);

		$this->assertSame(
			$mockObjects['segments'][0],
			$data['Loop2000_PAT'],
			'Loop2000 PAT was not set correctly'
		);
	}

	/**
	 * @covers SunCoastConnection\ClaimsToOEMR\X12N837\Cache::processLoop2010()
	 */
	public function testProcessLoop2010WithSegmentNM101_85() {
		$mockObjects = $this->setupTestProcessLoop(
			Loop\Loop2010::class,
			[ 'NM1', 'N3', 'N4', 'DMG', 'REF' ],
			Segment\NM1::class
		);

		$mockObjects['segments'][0]->shouldReceive('element')
			->once()
			->with('NM101')
			->andReturn('85');

		$this->cache->shouldReceive('existsAdd')
			->times(4);

		$this->cache->shouldReceive('storeFacility')
			->once()
			->andReturn(123);

		$this->cache->shouldReceive('storeUser')
			->once()
			->andReturn(234);

		$this->cache->shouldReceive('storeGroup')
			->once();

		$data = [];

		$this->callProtectedMethod(
			$this->cache,
			'processLoop2010',
			[
				$mockObjects['loop'],
				&$data
			]
		);

		$this->assertSame(
			$mockObjects['segments'][0],
			$data['Loop2010_NM1'],
			'Loop2010 NM1 was not set correctly'
		);

		$this->assertEquals(
			123,
			$data['CurrentFacility'],
			'Current Facility was not set correctly'
		);

		$this->assertEquals(
			234,
			$data['CurrentUser'],
			'Current User was not set correctly'
		);
	}

	/**
	 * @covers SunCoastConnection\ClaimsToOEMR\X12N837\Cache::processLoop2010()
	 */
	public function testProcessLoop2010WithSegmentNM101_87() {
		$mockObjects = $this->setupTestProcessLoop(
			Loop\Loop2010::class,
			[ 'NM1', 'N3', 'N4', 'DMG', 'REF' ],
			Segment\NM1::class
		);

		$mockObjects['segments'][0]->shouldReceive('element')
			->once()
			->with('NM101')
			->andReturn('87');

		$this->cache->shouldReceive('existsAdd')
			->times(4);

		$this->cache->shouldReceive('storeFacility')
			->once()
			->andReturn(123);

		$this->cache->shouldReceive('storeUser')
			->once()
			->andReturn(234);

		$this->cache->shouldReceive('storeGroup')
			->once();

		$data = [];

		$this->callProtectedMethod(
			$this->cache,
			'processLoop2010',
			[
				$mockObjects['loop'],
				&$data
			]
		);

		$this->assertSame(
			$mockObjects['segments'][0],
			$data['Loop2010_NM1'],
			'Loop2010 NM1 was not set correctly'
		);

		$this->assertEquals(
			123,
			$data['CurrentFacility'],
			'Current Facility was not set correctly'
		);

		$this->assertEquals(
			234,
			$data['CurrentUser'],
			'Current User was not set correctly'
		);
	}

	/**
	 * @covers SunCoastConnection\ClaimsToOEMR\X12N837\Cache::processLoop2010()
	 */
	public function testProcessLoop2010WithSegmentNM101_ILAndNoLoop2000_SBR() {
		$mockObjects = $this->setupTestProcessLoop(
			Loop\Loop2010::class,
			[ 'NM1', 'N3', 'N4', 'DMG', 'REF' ],
			Segment\NM1::class
		);

		$mockObjects['segments'][0]->shouldReceive('element')
			->once()
			->with('NM101')
			->andReturn('IL');

		$data = [];

		$this->callProtectedMethod(
			$this->cache,
			'processLoop2010',
			[
				$mockObjects['loop'],
				&$data
			]
		);

		$this->assertSame(
			$mockObjects['segments'][0],
			$data['Loop2010_NM1'],
			'Loop2010 NM1 was not set correctly'
		);
	}

	/**
	 * @covers SunCoastConnection\ClaimsToOEMR\X12N837\Cache::processLoop2010()
	 */
	public function testProcessLoop2010WithSegmentNM101_ILAndLoop2000_SBR() {
		$mockObjects = $this->setupTestProcessLoop(
			Loop\Loop2010::class,
			[ 'NM1', 'N3', 'N4', 'DMG', 'REF' ],
			Segment\NM1::class
		);

		$mockObjects['segments'][0]->shouldReceive('element')
			->once()
			->with('NM101')
			->andReturn('IL');

		$data = [
			'GS' => $this->getMockery(
				Segment\GS::class
			),
			'Loop2000_SBR' => $this->getMockery(
				Segment\SRB::class
			)
		];

		$data['Loop2000_SBR']->shouldReceive('elementEquals')
			->once()
			->with('SBR02', '18')
			->andReturn(true);

		$this->cache->shouldReceive('existsAdd')
			->times(6);

		$this->cache->shouldReceive('storePatientData')
			->once()
			->andReturn(123);

		$this->cache->shouldReceive('storeInsuranceData')
			->once()
			->andReturn(234);

		$this->callProtectedMethod(
			$this->cache,
			'processLoop2010',
			[
				$mockObjects['loop'],
				&$data
			]
		);

		$this->assertSame(
			$mockObjects['segments'][0],
			$data['Loop2010_NM1'],
			'Loop2010 NM1 was not set correctly'
		);

		$this->assertEquals(
			123,
			$data['CurrentPatientData'],
			'Current Patient Data was not set correctly'
		);

		$this->assertEquals(
			234,
			$data['CurrentInsuranceData'],
			'Current Insurance Data was not set correctly'
		);
	}

	/**
	 * @covers SunCoastConnection\ClaimsToOEMR\X12N837\Cache::processLoop2010()
	 */
	public function testProcessLoop2010WithSegmentNM101_PR() {
		$mockObjects = $this->setupTestProcessLoop(
			Loop\Loop2010::class,
			[ 'NM1', 'N3', 'N4', 'DMG', 'REF' ],
			Segment\NM1::class
		);

		$mockObjects['segments'][0]->shouldReceive('element')
			->once()
			->with('NM101')
			->andReturn('PR');

		$this->cache->shouldReceive('storeInsuranceCompany')
			->once()
			->andReturn(123);

		$this->cache->shouldReceive('existsAdd')
			->times(2);

		$this->cache->shouldReceive('storeAddress')
			->once();

		$this->cache->shouldReceive('storePhoneNumber')
			->once();

		$data = [ 'CurrentX12Partner' => 234 ];

		$this->callProtectedMethod(
			$this->cache,
			'processLoop2010',
			[
				$mockObjects['loop'],
				&$data
			]
		);

		$this->assertSame(
			$mockObjects['segments'][0],
			$data['Loop2010_NM1'],
			'Loop2010 NM1 was not set correctly'
		);

		$this->assertEquals(
			123,
			$data['CurrentInsuranceCompany'],
			'Current Insurance Company was not set correctly'
		);
	}

	/**
	 * @covers SunCoastConnection\ClaimsToOEMR\X12N837\Cache::processLoop2010()
	 */
	public function testProcessLoop2010WithSegmentNM101_QC() {
		$mockObjects = $this->setupTestProcessLoop(
			Loop\Loop2010::class,
			[ 'NM1', 'N3', 'N4', 'DMG', 'REF' ],
			Segment\NM1::class
		);

		$mockObjects['segments'][0]->shouldReceive('element')
			->once()
			->with('NM101')
			->andReturn('QC');

		$this->cache->shouldReceive('existsAdd')
			->times(3);

		$this->cache->shouldReceive('storePatientData')
			->once()
			->andReturn(123);

		$data = [
			'GS' => $this->getMockery(
				Segment\GS::class
			)
		];

		$this->callProtectedMethod(
			$this->cache,
			'processLoop2010',
			[
				$mockObjects['loop'],
				&$data
			]
		);

		$this->assertSame(
			$mockObjects['segments'][0],
			$data['Loop2010_NM1'],
			'Loop2010 NM1 was not set correctly'
		);

		$this->assertEquals(
			123,
			$data['CurrentPatientData'],
			'Current Patient Data was not set correctly'
		);
	}

	/**
	 * @covers SunCoastConnection\ClaimsToOEMR\X12N837\Cache::processLoop2010()
	 */
	public function testProcessLoop2010WithSegmentN3() {
		$mockObjects = $this->setupTestProcessLoop(
			Loop\Loop2010::class,
			[ 'NM1', 'N3', 'N4', 'DMG', 'REF' ],
			Segment\N3::class
		);

		$data = [];

		$this->callProtectedMethod(
			$this->cache,
			'processLoop2010',
			[
				$mockObjects['loop'],
				&$data
			]
		);

		$this->assertSame(
			$mockObjects['segments'][0],
			$data['Loop2010_N3'],
			'Loop2010 N3 was not set correctly'
		);
	}

	/**
	 * @covers SunCoastConnection\ClaimsToOEMR\X12N837\Cache::processLoop2010()
	 */
	public function testProcessLoop2010WithSegmentN4() {
		$mockObjects = $this->setupTestProcessLoop(
			Loop\Loop2010::class,
			[ 'NM1', 'N3', 'N4', 'DMG', 'REF' ],
			Segment\N4::class
		);

		$data = [];

		$this->callProtectedMethod(
			$this->cache,
			'processLoop2010',
			[
				$mockObjects['loop'],
				&$data
			]
		);

		$this->assertSame(
			$mockObjects['segments'][0],
			$data['Loop2010_N4'],
			'Loop2010 N4 was not set correctly'
		);
	}

	/**
	 * @covers SunCoastConnection\ClaimsToOEMR\X12N837\Cache::processLoop2010()
	 */
	public function testProcessLoop2010WithSegmentDMG() {
		$mockObjects = $this->setupTestProcessLoop(
			Loop\Loop2010::class,
			[ 'NM1', 'N3', 'N4', 'DMG', 'REF' ],
			Segment\DMG::class
		);

		$data = [];

		$this->callProtectedMethod(
			$this->cache,
			'processLoop2010',
			[
				$mockObjects['loop'],
				&$data
			]
		);

		$this->assertSame(
			$mockObjects['segments'][0],
			$data['Loop2010_DMG'],
			'Loop2010 DMG was not set correctly'
		);
	}

	/**
	 * @covers SunCoastConnection\ClaimsToOEMR\X12N837\Cache::processLoop2010()
	 */
	public function testProcessLoop2010WithSegmentREF() {
		$mockObjects = $this->setupTestProcessLoop(
			Loop\Loop2010::class,
			[ 'NM1', 'N3', 'N4', 'DMG', 'REF' ],
			Segment\REF::class
		);

		$data = [];

		$this->callProtectedMethod(
			$this->cache,
			'processLoop2010',
			[
				$mockObjects['loop'],
				&$data
			]
		);

		$this->assertSame(
			$mockObjects['segments'][0],
			$data['Loop2010_REF'],
			'Loop2010 REF was not set correctly'
		);
	}

	/**
	 * @covers SunCoastConnection\ClaimsToOEMR\X12N837\Cache::processLoop2300()
	 */
	public function testProcessLoop2300WithDescendantAndNoSegment() {
		$mockObjects = $this->setupTestProcessLoop(
			Loop\Loop2300::class,
			[ 'CLM', 'DTP', 'REF', 'NTE', 'HI' ]
		);

		$descendant = [
			$this->getMockery(
				Loop\Loop2310::class
			),
			$this->getMockery(
				Loop\Loop2320::class
			),
			$this->getMockery(
				Loop\Loop2400::class
			)
		];

		$mockObjects['loop']->shouldReceive('getDescendant')
			->once()
			->andReturn($descendant);

		$descendant[0]->shouldReceive('getName')
			->once()
			->andReturn('Loop2310');

		$descendant[1]->shouldReceive('getName')
			->once()
			->andReturn('Loop2320');

		$descendant[2]->shouldReceive('getName')
			->once()
			->andReturn('Loop2400');

		$data = [];

		$this->cache->shouldReceive('processLoop2310')
			->once()
			->with($descendant[0], $data);

		$this->cache->shouldReceive('processLoop2320')
			->once()
			->with($descendant[1], $data);

		$this->cache->shouldReceive('processLoop2400')
			->once()
			->with($descendant[2], $data);

		$this->callProtectedMethod(
			$this->cache,
			'processLoop2300',
			[
				$mockObjects['loop'],
				&$data
			]
		);
	}

	/**
	 * @covers SunCoastConnection\ClaimsToOEMR\X12N837\Cache::processLoop2300()
	 */
	public function testProcessLoop2300WithSegmentCLM() {
		$mockObjects = $this->setupTestProcessLoop(
			Loop\Loop2300::class,
			[ 'CLM', 'DTP', 'REF', 'NTE', 'HI' ],
			Segment\CLM::class
		);

		$this->cache->shouldReceive('existsAdd')
			->times(9);

		$this->cache->shouldReceive('storeFormEncounter')
			->once()
			->andReturn(123);

		$this->cache->shouldReceive('storeForm')
			->once();

		$mockObjects['loop']->shouldReceive('getDescendant')
			->once()
			->andReturnNull();

		$data = [
		];

		$this->callProtectedMethod(
			$this->cache,
			'processLoop2300',
			[
				$mockObjects['loop'],
				&$data
			]
		);

		$this->assertSame(
			$mockObjects['segments'][0],
			$data['Loop2300_CLM'],
			'Loop2300 CLM was not set correctly'
		);

		$this->assertEquals(
			123,
			$data['CurrentFormEncounter'],
			'Current Form Encounter was not set correctly'
		);
	}

	/**
	 * @covers SunCoastConnection\ClaimsToOEMR\X12N837\Cache::processLoop2300()
	 */
	public function testProcessLoop2300WithSegmentDTP01Equals431() {
		$mockObjects = $this->setupTestProcessLoop(
			Loop\Loop2300::class,
			[ 'CLM', 'DTP', 'REF', 'NTE', 'HI' ],
			Segment\DTP::class
		);

		$mockObjects['segments'][0]->shouldReceive('elementEquals')
			->once()
			->with('DTP01', '431')
			->andReturn(true);

		$mockObjects['loop']->shouldReceive('getDescendant')
			->once()
			->andReturnNull();

		$data = [];

		$this->callProtectedMethod(
			$this->cache,
			'processLoop2300',
			[
				$mockObjects['loop'],
				&$data
			]
		);

		$this->assertSame(
			$mockObjects['segments'][0],
			$data['Loop2300_DTP_431'],
			'Loop2300 DTP 431 was not set correctly'
		);
	}

	/**
	 * @covers SunCoastConnection\ClaimsToOEMR\X12N837\Cache::processLoop2300()
	 */
	public function testProcessLoop2300WithSegmentDTP01Equals472() {
		$mockObjects = $this->setupTestProcessLoop(
			Loop\Loop2300::class,
			[ 'CLM', 'DTP', 'REF', 'NTE', 'HI' ],
			Segment\DTP::class
		);

		$mockObjects['segments'][0]->shouldReceive('elementEquals')
			->once()
			->with('DTP01', '431')
			->andReturn(false);

		$mockObjects['segments'][0]->shouldReceive('elementEquals')
			->once()
			->with('DTP01', '472')
			->andReturn(true);

		$mockObjects['loop']->shouldReceive('getDescendant')
			->once()
			->andReturnNull();

		$data = [];

		$this->callProtectedMethod(
			$this->cache,
			'processLoop2300',
			[
				$mockObjects['loop'],
				&$data
			]
		);

		$this->assertSame(
			$mockObjects['segments'][0],
			$data['Loop2300_DTP'],
			'Loop2300 DTP was not set correctly'
		);
	}

	/**
	 * @covers SunCoastConnection\ClaimsToOEMR\X12N837\Cache::processLoop2300()
	 */
	public function testProcessLoop2300WithSegmentREF() {
		$mockObjects = $this->setupTestProcessLoop(
			Loop\Loop2300::class,
			[ 'CLM', 'DTP', 'REF', 'NTE', 'HI' ],
			Segment\REF::class
		);

		$mockObjects['loop']->shouldReceive('getDescendant')
			->once()
			->andReturnNull();

		$data = [];

		$this->callProtectedMethod(
			$this->cache,
			'processLoop2300',
			[
				$mockObjects['loop'],
				&$data
			]
		);

		$this->assertSame(
			$mockObjects['segments'][0],
			$data['Loop2300_REF'],
			'Loop2300 REF was not set correctly'
		);
	}

	/**
	 * @covers SunCoastConnection\ClaimsToOEMR\X12N837\Cache::processLoop2300()
	 */
	public function testProcessLoop2300WithSegmentNTE() {
		$mockObjects = $this->setupTestProcessLoop(
			Loop\Loop2300::class,
			[ 'CLM', 'DTP', 'REF', 'NTE', 'HI' ],
			Segment\NTE::class
		);

		$mockObjects['loop']->shouldReceive('getDescendant')
			->once()
			->andReturnNull();

		$data = [];

		$this->callProtectedMethod(
			$this->cache,
			'processLoop2300',
			[
				$mockObjects['loop'],
				&$data
			]
		);

		$this->assertSame(
			$mockObjects['segments'][0],
			$data['Loop2300_NTE'],
			'Loop2300 NTE was not set correctly'
		);
	}

	/**
	 * @covers SunCoastConnection\ClaimsToOEMR\X12N837\Cache::processLoop2300()
	 */
	public function testProcessLoop2300WithSegmentHI() {
		$mockObjects = $this->setupTestProcessLoop(
			Loop\Loop2300::class,
			[ 'CLM', 'DTP', 'REF', 'NTE', 'HI' ],
			Segment\HI::class
		);

		$mockObjects['loop']->shouldReceive('getDescendant')
			->once()
			->andReturnNull();

		$data = [];

		$this->callProtectedMethod(
			$this->cache,
			'processLoop2300',
			[
				$mockObjects['loop'],
				&$data
			]
		);

		$this->assertSame(
			$mockObjects['segments'][0],
			$data['Loop2300_HI'][0],
			'Loop2300 HI was not set correctly'
		);
	}

	/**
	 * @covers SunCoastConnection\ClaimsToOEMR\X12N837\Cache::processLoop2310()
	 */
	public function testProcessLoop2310WithSegmentNM101_77() {
		$mockObjects = $this->setupTestProcessLoop(
			Loop\Loop2310::class,
			[ 'NM1', 'N3', 'N4', 'PRV' ],
			Segment\NM1::class
		);

		$mockObjects['segments'][0]->shouldReceive('element')
			->once()
			->with('NM101')
			->andReturn('77');

		$mockObjects['segments'][0]->shouldReceive('elementEquals')
			->once()
			->with('NM102', '2')
			->andReturn(true);

		$this->cache->shouldReceive('existsAdd')
			->times(3);

		$this->cache->shouldReceive('storeFacility')
			->once()
			->andReturn(123);

		$data = [];

		$this->callProtectedMethod(
			$this->cache,
			'processLoop2310',
			[
				$mockObjects['loop'],
				&$data
			]
		);

		$this->assertSame(
			$mockObjects['segments'][0],
			$data['Loop2310_NM1'],
			'Loop2310 NM1 was not set correctly'
		);

		$this->assertEquals(
			123,
			$data['CurrentFacility'],
			'Current Facility was not set correctly'
		);
	}

	/**
	 * @covers SunCoastConnection\ClaimsToOEMR\X12N837\Cache::processLoop2310()
	 */
	public function testProcessLoop2310WithSegmentNM101_82() {
		$mockObjects = $this->setupTestProcessLoop(
			Loop\Loop2310::class,
			[ 'NM1', 'N3', 'N4', 'PRV' ],
			Segment\NM1::class
		);

		$mockObjects['segments'][0]->shouldReceive('element')
			->once()
			->with('NM101')
			->andReturn('82');

		$this->cache->shouldReceive('existsAdd')
			->times(1);

		$this->cache->shouldReceive('storeUser')
			->once()
			->andReturn(123);

		$this->cache->shouldReceive('storeGroup')
			->once();

		$data = [];

		$this->callProtectedMethod(
			$this->cache,
			'processLoop2310',
			[
				$mockObjects['loop'],
				&$data
			]
		);

		$this->assertSame(
			$mockObjects['segments'][0],
			$data['Loop2310_NM1'],
			'Loop2310 NM1 was not set correctly'
		);

		$this->assertEquals(
			123,
			$data['CurrentUser'],
			'Current User was not set correctly'
		);
	}

	/**
	 * @covers SunCoastConnection\ClaimsToOEMR\X12N837\Cache::processLoop2310()
	 */
	public function testProcessLoop2310WithSegmentNM101_DN() {
		$mockObjects = $this->setupTestProcessLoop(
			Loop\Loop2310::class,
			[ 'NM1', 'N3', 'N4', 'PRV' ],
			Segment\NM1::class
		);

		$mockObjects['segments'][0]->shouldReceive('element')
			->once()
			->with('NM101')
			->andReturn('DN');

		$this->cache->shouldReceive('existsAdd')
			->times(1);

		$this->cache->shouldReceive('storeUser')
			->once()
			->andReturn(123);

		$this->cache->shouldReceive('storeGroup')
			->once();

		$data = [];

		$this->callProtectedMethod(
			$this->cache,
			'processLoop2310',
			[
				$mockObjects['loop'],
				&$data
			]
		);

		$this->assertSame(
			$mockObjects['segments'][0],
			$data['Loop2310_NM1'],
			'Loop2310 NM1 was not set correctly'
		);

		$this->assertEquals(
			123,
			$data['CurrentUser'],
			'Current User was not set correctly'
		);
	}

	/**
	 * @covers SunCoastConnection\ClaimsToOEMR\X12N837\Cache::processLoop2310()
	 */
	public function testProcessLoop2310WithSegmentNM101_DQ() {
		$mockObjects = $this->setupTestProcessLoop(
			Loop\Loop2310::class,
			[ 'NM1', 'N3', 'N4', 'PRV' ],
			Segment\NM1::class
		);

		$mockObjects['segments'][0]->shouldReceive('element')
			->once()
			->with('NM101')
			->andReturn('DQ');

		$data = [];

		$this->callProtectedMethod(
			$this->cache,
			'processLoop2310',
			[
				$mockObjects['loop'],
				&$data
			]
		);

		$this->assertSame(
			$mockObjects['segments'][0],
			$data['Loop2310_NM1'],
			'Loop2310 NM1 was not set correctly'
		);
	}

	/**
	 * @covers SunCoastConnection\ClaimsToOEMR\X12N837\Cache::processLoop2310()
	 */
	public function testProcessLoop2310WithSegmentN3() {
		$mockObjects = $this->setupTestProcessLoop(
			Loop\Loop2310::class,
			[ 'NM1', 'N3', 'N4', 'PRV' ],
			Segment\N3::class
		);

		$data = [];

		$this->callProtectedMethod(
			$this->cache,
			'processLoop2310',
			[
				$mockObjects['loop'],
				&$data
			]
		);

		$this->assertSame(
			$mockObjects['segments'][0],
			$data['Loop2310_N3'],
			'Loop2310 N3 was not set correctly'
		);
	}

	/**
	 * @covers SunCoastConnection\ClaimsToOEMR\X12N837\Cache::processLoop2310()
	 */
	public function testProcessLoop2310WithSegmentN4() {
		$mockObjects = $this->setupTestProcessLoop(
			Loop\Loop2310::class,
			[ 'NM1', 'N3', 'N4', 'PRV' ],
			Segment\N4::class
		);

		$data = [];

		$this->callProtectedMethod(
			$this->cache,
			'processLoop2310',
			[
				$mockObjects['loop'],
				&$data
			]
		);

		$this->assertSame(
			$mockObjects['segments'][0],
			$data['Loop2310_N4'],
			'Loop2310 N4 was not set correctly'
		);
	}

	/**
	 * @covers SunCoastConnection\ClaimsToOEMR\X12N837\Cache::processLoop2310()
	 */
	public function testProcessLoop2310WithSegmentPRV() {
		$mockObjects = $this->setupTestProcessLoop(
			Loop\Loop2310::class,
			[ 'NM1', 'N3', 'N4', 'PRV' ],
			Segment\PRV::class
		);

		$data = [];

		$this->callProtectedMethod(
			$this->cache,
			'processLoop2310',
			[
				$mockObjects['loop'],
				&$data
			]
		);

		$this->assertSame(
			$mockObjects['segments'][0],
			$data['Loop2310_PRV'],
			'Loop2310 PRV was not set correctly'
		);
	}

	/**
	 * @covers SunCoastConnection\ClaimsToOEMR\X12N837\Cache::processLoop2320()
	 */
	public function testProcessLoop2320WithDescendantAndNoSegment() {
		$mockObjects = $this->setupTestProcessLoop(
			Loop\Loop2320::class,
			[ 'SBR' ]
		);

		$descendant = [
			$this->getMockery(
				Loop\Loop2330::class
			)
		];

		$mockObjects['loop']->shouldReceive('getDescendant')
			->once()
			->andReturn($descendant);

		$descendant[0]->shouldReceive('getName')
			->once()
			->andReturn('Loop2330');

		$data = [];

		$this->cache->shouldReceive('processLoop2330')
			->once()
			->with($descendant[0], $data);

		$this->callProtectedMethod(
			$this->cache,
			'processLoop2320',
			[
				$mockObjects['loop'],
				&$data
			]
		);
	}

	/**
	 * @covers SunCoastConnection\ClaimsToOEMR\X12N837\Cache::processLoop2320()
	 */
	public function testProcessLoop2320WithSegmentSBR() {
		$mockObjects = $this->setupTestProcessLoop(
			Loop\Loop2320::class,
			[ 'SBR' ],
			Segment\SBR::class
		);

		$mockObjects['loop']->shouldReceive('getDescendant')
			->once()
			->andReturnNull();

		$data = [];

		$this->callProtectedMethod(
			$this->cache,
			'processLoop2320',
			[
				$mockObjects['loop'],
				&$data
			]
		);

		$this->assertSame(
			$mockObjects['segments'][0],
			$data['Loop2320_SBR'],
			'Loop2320 SBR was not set correctly'
		);
	}

	/**
	 * @covers SunCoastConnection\ClaimsToOEMR\X12N837\Cache::processLoop2330()
	 */
	public function testProcessLoop2330WithSegmentNM101_77() {
		$mockObjects = $this->setupTestProcessLoop(
			Loop\Loop2330::class,
			[ 'NM1', 'N3', 'N4', 'REF' ],
			Segment\NM1::class
		);

		$mockObjects['segments'][0]->shouldReceive('element')
			->once()
			->with('NM101')
			->andReturn('77');

		$mockObjects['segments'][0]->shouldReceive('elementEquals')
			->once()
			->with('NM102', '2')
			->andReturn(true);

		$this->cache->shouldReceive('existsAdd')
			->times(3);

		$this->cache->shouldReceive('storeFacility')
			->once()
			->andReturn(123);

		$data = [];

		$this->callProtectedMethod(
			$this->cache,
			'processLoop2330',
			[
				$mockObjects['loop'],
				&$data
			]
		);

		$this->assertSame(
			$mockObjects['segments'][0],
			$data['Loop2330_NM1'],
			'Loop2330 NM1 was not set correctly'
		);

		$this->assertEquals(
			123,
			$data['CurrentFacility'],
			'Current Facility was not set correctly'
		);
	}

	/**
	 * @covers SunCoastConnection\ClaimsToOEMR\X12N837\Cache::processLoop2330()
	 */
	public function testProcessLoop2330WithSegmentNM101_82() {
		$mockObjects = $this->setupTestProcessLoop(
			Loop\Loop2330::class,
			[ 'NM1', 'N3', 'N4', 'REF' ],
			Segment\NM1::class
		);

		$mockObjects['segments'][0]->shouldReceive('element')
			->once()
			->with('NM101')
			->andReturn('82');

		$this->cache->shouldReceive('existsAdd')
			->times(1);

		$this->cache->shouldReceive('storeUser')
			->once()
			->andReturn(123);

		$this->cache->shouldReceive('storeGroup')
			->once();

		$data = [];

		$this->callProtectedMethod(
			$this->cache,
			'processLoop2330',
			[
				$mockObjects['loop'],
				&$data
			]
		);

		$this->assertSame(
			$mockObjects['segments'][0],
			$data['Loop2330_NM1'],
			'Loop2330 NM1 was not set correctly'
		);

		$this->assertEquals(
			123,
			$data['CurrentUser'],
			'Current User was not set correctly'
		);
	}

	/**
	 * @covers SunCoastConnection\ClaimsToOEMR\X12N837\Cache::processLoop2330()
	 */
	public function testProcessLoop2330WithSegmentNM101_85() {
		$mockObjects = $this->setupTestProcessLoop(
			Loop\Loop2330::class,
			[ 'NM1', 'N3', 'N4', 'REF' ],
			Segment\NM1::class
		);

		$mockObjects['segments'][0]->shouldReceive('element')
			->once()
			->with('NM101')
			->andReturn('85');

		$this->cache->shouldReceive('existsAdd')
			->times(6);

		$this->cache->shouldReceive('storeFacility')
			->once()
			->andReturn(123);

		$this->cache->shouldReceive('storeUser')
			->once()
			->andReturn(234);

		$this->cache->shouldReceive('storeGroup')
			->once();

		$data = [];

		$this->callProtectedMethod(
			$this->cache,
			'processLoop2330',
			[
				$mockObjects['loop'],
				&$data
			]
		);

		$this->assertSame(
			$mockObjects['segments'][0],
			$data['Loop2330_NM1'],
			'Loop2330 NM1 was not set correctly'
		);

		$this->assertEquals(
			123,
			$data['CurrentFacility'],
			'Current Facility was not set correctly'
		);

		$this->assertEquals(
			234,
			$data['CurrentUser'],
			'Current User was not set correctly'
		);
	}

	/**
	 * @covers SunCoastConnection\ClaimsToOEMR\X12N837\Cache::processLoop2330()
	 */
	public function testProcessLoop2330WithSegmentNM101_DN() {
		$mockObjects = $this->setupTestProcessLoop(
			Loop\Loop2330::class,
			[ 'NM1', 'N3', 'N4', 'REF' ],
			Segment\NM1::class
		);

		$mockObjects['segments'][0]->shouldReceive('element')
			->once()
			->with('NM101')
			->andReturn('DN');

		$this->cache->shouldReceive('existsAdd')
			->times(1);

		$this->cache->shouldReceive('storeUser')
			->once()
			->andReturn(123);

		$this->cache->shouldReceive('storeGroup')
			->once();

		$data = [];

		$this->callProtectedMethod(
			$this->cache,
			'processLoop2330',
			[
				$mockObjects['loop'],
				&$data
			]
		);

		$this->assertSame(
			$mockObjects['segments'][0],
			$data['Loop2330_NM1'],
			'Loop2330 NM1 was not set correctly'
		);

		$this->assertEquals(
			123,
			$data['CurrentUser'],
			'Current User was not set correctly'
		);
	}

	/**
	 * @covers SunCoastConnection\ClaimsToOEMR\X12N837\Cache::processLoop2330()
	 */
	public function testProcessLoop2330WithSegmentNM101_DQ() {
		$mockObjects = $this->setupTestProcessLoop(
			Loop\Loop2330::class,
			[ 'NM1', 'N3', 'N4', 'REF' ],
			Segment\NM1::class
		);

		$mockObjects['segments'][0]->shouldReceive('element')
			->once()
			->with('NM101')
			->andReturn('DQ');

		$data = [];

		$this->callProtectedMethod(
			$this->cache,
			'processLoop2330',
			[
				$mockObjects['loop'],
				&$data
			]
		);

		$this->assertSame(
			$mockObjects['segments'][0],
			$data['Loop2330_NM1'],
			'Loop2330 NM1 was not set correctly'
		);
	}

	/**
	 * @covers SunCoastConnection\ClaimsToOEMR\X12N837\Cache::processLoop2330()
	 */
	public function testProcessLoop2330WithSegmentNM101_IL() {
		$mockObjects = $this->setupTestProcessLoop(
			Loop\Loop2330::class,
			[ 'NM1', 'N3', 'N4', 'REF' ],
			Segment\NM1::class
		);

		$mockObjects['segments'][0]->shouldReceive('element')
			->once()
			->with('NM101')
			->andReturn('IL');

		$data = [
			'GS' => $this->getMockery(
				Segment\GS::class
			),
			'Loop2320_SBR' => $this->getMockery(
				Segment\SBR::class
			)
		];

		$data['Loop2320_SBR']->shouldReceive('elementEquals')
			->once()
			->with('SBR02', '18')
			->andReturn(true);

		$this->cache->shouldReceive('existsAdd')
			->times(10);

		$this->cache->shouldReceive('storePatientData')
			->once()
			->andReturn(123);

		$this->cache->shouldReceive('storeInsuranceData')
			->once()
			->andReturn(234);

		$this->callProtectedMethod(
			$this->cache,
			'processLoop2330',
			[
				$mockObjects['loop'],
				&$data
			]
		);

		$this->assertSame(
			$mockObjects['segments'][0],
			$data['Loop2330_NM1'],
			'Loop2330 NM1 was not set correctly'
		);

		$this->assertEquals(
			123,
			$data['CurrentPatientData'],
			'Current Patient Data was not set correctly'
		);

		$this->assertEquals(
			234,
			$data['CurrentInsuranceData'],
			'Current Insurance Data was not set correctly'
		);
	}

	/**
	 * @covers SunCoastConnection\ClaimsToOEMR\X12N837\Cache::processLoop2330()
	 */
	public function testProcessLoop2330WithSegmentNM101_PR() {
		$mockObjects = $this->setupTestProcessLoop(
			Loop\Loop2330::class,
			[ 'NM1', 'N3', 'N4', 'REF' ],
			Segment\NM1::class
		);

		$mockObjects['segments'][0]->shouldReceive('element')
			->once()
			->with('NM101')
			->andReturn('PR');

		$this->cache->shouldReceive('existsAdd')
			->times(3);

		$this->cache->shouldReceive('storeInsuranceCompany')
			->once()
			->andReturn(123);

		$this->cache->shouldReceive('storeAddress')
			->once();

		$this->cache->shouldReceive('storePhoneNumber')
			->once();

		$data = [];

		$this->callProtectedMethod(
			$this->cache,
			'processLoop2330',
			[
				$mockObjects['loop'],
				&$data
			]
		);

		$this->assertSame(
			$mockObjects['segments'][0],
			$data['Loop2330_NM1'],
			'Loop2330 NM1 was not set correctly'
		);

		$this->assertEquals(
			123,
			$data['CurrentInsuranceCompany'],
			'Current Insurance Company was not set correctly'
		);
	}

	/**
	 * @covers SunCoastConnection\ClaimsToOEMR\X12N837\Cache::processLoop2330()
	 */
	public function testProcessLoop2330WithSegmentN3() {
		$mockObjects = $this->setupTestProcessLoop(
			Loop\Loop2330::class,
			[ 'NM1', 'N3', 'N4', 'REF' ],
			Segment\N3::class
		);

		$data = [];

		$this->callProtectedMethod(
			$this->cache,
			'processLoop2330',
			[
				$mockObjects['loop'],
				&$data
			]
		);

		$this->assertSame(
			$mockObjects['segments'][0],
			$data['Loop2330_N3'],
			'Loop2330 N3 was not set correctly'
		);
	}

	/**
	 * @covers SunCoastConnection\ClaimsToOEMR\X12N837\Cache::processLoop2330()
	 */
	public function testProcessLoop2330WithSegmentN4() {
		$mockObjects = $this->setupTestProcessLoop(
			Loop\Loop2330::class,
			[ 'NM1', 'N3', 'N4', 'REF' ],
			Segment\N4::class
		);

		$data = [];

		$this->callProtectedMethod(
			$this->cache,
			'processLoop2330',
			[
				$mockObjects['loop'],
				&$data
			]
		);

		$this->assertSame(
			$mockObjects['segments'][0],
			$data['Loop2330_N4'],
			'Loop2330 N4 was not set correctly'
		);
	}

	/**
	 * @covers SunCoastConnection\ClaimsToOEMR\X12N837\Cache::processLoop2330()
	 */
	public function testProcessLoop2330WithSegmentREF() {
		$mockObjects = $this->setupTestProcessLoop(
			Loop\Loop2330::class,
			[ 'NM1', 'N3', 'N4', 'REF' ],
			Segment\REF::class
		);

		$data = [];

		$this->callProtectedMethod(
			$this->cache,
			'processLoop2330',
			[
				$mockObjects['loop'],
				&$data
			]
		);

		$this->assertSame(
			$mockObjects['segments'][0],
			$data['Loop2330_REF'],
			'Loop2330 REF was not set correctly'
		);
	}

	/**
	 * @covers SunCoastConnection\ClaimsToOEMR\X12N837\Cache::processLoop2400()
	 */
	public function testProcessLoop2400WithDescendantAndNoSegment() {
		$mockObjects = $this->setupTestProcessLoop(
			Loop\Loop2400::class,
			[ 'SV1', 'DTP', 'NTE' ]
		);

		$descendant = [
			$this->getMockery(
				Loop\Loop2420::class
			)
		];

		$mockObjects['loop']->shouldReceive('getDescendant')
			->once()
			->andReturn($descendant);

		$descendant[0]->shouldReceive('getName')
			->once()
			->andReturn('Loop2420');

		$data = [];

		$this->cache->shouldReceive('processLoop2420')
			->once()
			->with($descendant[0], $data);

		$this->callProtectedMethod(
			$this->cache,
			'processLoop2400',
			[
				$mockObjects['loop'],
				&$data
			]
		);
	}

	/**
	 * @covers SunCoastConnection\ClaimsToOEMR\X12N837\Cache::processLoop2400()
	 */
	public function testProcessLoop2400WithSegmentSV1() {
		$mockObjects = $this->setupTestProcessLoop(
			Loop\Loop2400::class,
			[ 'SV1', 'DTP', 'NTE' ],
			Segment\SV1::class
		);

		$this->cache->shouldReceive('existsAdd')
			->times(6);

		$elementSV101 = $this->getMockery(
			Element::class
		);

		$mockObjects['segments'][0]->shouldReceive('element')
			->once()
			->with('SV101')
			->andReturn($elementSV101);

		$elementSV101->shouldReceive('subElementEquals')
			->once()
			->with(0, [ 'HC', 'WK' ])
			->andReturn(true);

		$this->cache->shouldReceive('storeBilling')
			->once();

		$mockObjects['loop']->shouldReceive('getDescendant')
			->once()
			->andReturnNull();

		$data = [];

		$this->callProtectedMethod(
			$this->cache,
			'processLoop2400',
			[
				$mockObjects['loop'],
				&$data
			]
		);

		$this->assertSame(
			$mockObjects['segments'][0],
			$data['Loop2400_SV1'][0],
			'Loop2400 SV1 was not set correctly'
		);
	}

	/**
	 * @covers SunCoastConnection\ClaimsToOEMR\X12N837\Cache::processLoop2400()
	 */
	public function testProcessLoop2400WithSegmentDTP() {
		$mockObjects = $this->setupTestProcessLoop(
			Loop\Loop2400::class,
			[ 'SV1', 'DTP', 'NTE' ],
			Segment\DTP::class
		);

		$mockObjects['segments'][0]->shouldReceive('elementEquals')
			->once()
			->with('DTP01', '472')
			->andReturn(true);

		$mockObjects['loop']->shouldReceive('getDescendant')
			->once()
			->andReturnNull();

		$data = [];

		$this->callProtectedMethod(
			$this->cache,
			'processLoop2400',
			[
				$mockObjects['loop'],
				&$data
			]
		);

		$this->assertSame(
			$mockObjects['segments'][0],
			$data['Loop2400_DTP'],
			'Loop2400 DTP was not set correctly'
		);
	}

	/**
	 * @covers SunCoastConnection\ClaimsToOEMR\X12N837\Cache::processLoop2400()
	 */
	public function testProcessLoop2400WithSegmentNTE() {
		$mockObjects = $this->setupTestProcessLoop(
			Loop\Loop2400::class,
			[ 'SV1', 'DTP', 'NTE' ],
			Segment\NTE::class
		);

		$mockObjects['loop']->shouldReceive('getDescendant')
			->once()
			->andReturnNull();

		$data = [];

		$this->callProtectedMethod(
			$this->cache,
			'processLoop2400',
			[
				$mockObjects['loop'],
				&$data
			]
		);

		$this->assertSame(
			$mockObjects['segments'][0],
			$data['Loop2400_NTE'],
			'Loop2400 NTE was not set correctly'
		);
	}

	/**
	 * @covers SunCoastConnection\ClaimsToOEMR\X12N837\Cache::processLoop2400()
	 */
	public function testProcessLoop2400WithLoop2300_HI() {
		$mockObjects = $this->setupTestProcessLoop(
			Loop\Loop2400::class,
			[ 'SV1', 'DTP', 'NTE' ]
		);

		$data = [
			'Loop2300_HI' => [
				$this->getMockery(
					Segment\HI::class
				)
			]
		];

		$this->cache->shouldReceive('existsAdd')
			->times(5);

		$elementHI01 = $this->getMockery(
			Element::class
		);

		$data['Loop2300_HI'][0]->shouldReceive('element')
			->once()
			->with('HI01')
			->andReturn($elementHI01);

		$elementHI01->shouldReceive('subElementEquals')
			->once()
			->with(0, [ 'ABK', 'BK' ])
			->andReturn(true);

		$this->cache->shouldReceive('storeBilling')
			->once();

		$mockObjects['loop']->shouldReceive('getDescendant')
			->once()
			->andReturnNull();

		$this->callProtectedMethod(
			$this->cache,
			'processLoop2400',
			[
				$mockObjects['loop'],
				&$data
			]
		);
	}

	/**
	 * @covers SunCoastConnection\ClaimsToOEMR\X12N837\Cache::processLoop2420()
	 */
	public function testProcessLoop2420WithSegmentNM101_77() {
		$mockObjects = $this->setupTestProcessLoop(
			Loop\Loop2420::class,
			[ 'NM1', 'N3', 'N4', 'PRV' ],
			Segment\NM1::class
		);

		$mockObjects['segments'][0]->shouldReceive('element')
			->once()
			->with('NM101')
			->andReturn('77');

		$mockObjects['segments'][0]->shouldReceive('elementEquals')
			->once()
			->with('NM102', '2')
			->andReturn(true);

		$this->cache->shouldReceive('existsAdd')
			->times(3);

		$this->cache->shouldReceive('storeFacility')
			->once()
			->andReturn(123);

		$data = [];

		$this->callProtectedMethod(
			$this->cache,
			'processLoop2420',
			[
				$mockObjects['loop'],
				&$data
			]
		);

		$this->assertSame(
			$mockObjects['segments'][0],
			$data['Loop2420_NM1'],
			'Loop2420 NM1 was not set correctly'
		);

		$this->assertEquals(
			123,
			$data['CurrentFacility'],
			'Current Facility was not set correctly'
		);
	}

	/**
	 * @covers SunCoastConnection\ClaimsToOEMR\X12N837\Cache::processLoop2420()
	 */
	public function testProcessLoop2420WithSegmentNM101_82() {
		$mockObjects = $this->setupTestProcessLoop(
			Loop\Loop2420::class,
			[ 'NM1', 'N3', 'N4', 'PRV' ],
			Segment\NM1::class
		);

		$mockObjects['segments'][0]->shouldReceive('element')
			->once()
			->with('NM101')
			->andReturn('82');

		$this->cache->shouldReceive('existsAdd')
			->times(1);

		$this->cache->shouldReceive('storeUser')
			->once()
			->andReturn(123);

		$this->cache->shouldReceive('storeGroup')
			->once();

		$data = [];

		$this->callProtectedMethod(
			$this->cache,
			'processLoop2420',
			[
				$mockObjects['loop'],
				&$data
			]
		);

		$this->assertSame(
			$mockObjects['segments'][0],
			$data['Loop2420_NM1'],
			'Loop2420 NM1 was not set correctly'
		);

		$this->assertEquals(
			123,
			$data['CurrentUser'],
			'Current User was not set correctly'
		);
	}

	/**
	 * @covers SunCoastConnection\ClaimsToOEMR\X12N837\Cache::processLoop2420()
	 */
	public function testProcessLoop2420WithSegmentNM101_DK() {
		$mockObjects = $this->setupTestProcessLoop(
			Loop\Loop2420::class,
			[ 'NM1', 'N3', 'N4', 'PRV' ],
			Segment\NM1::class
		);

		$mockObjects['segments'][0]->shouldReceive('element')
			->once()
			->with('NM101')
			->andReturn('DK');

		$this->cache->shouldReceive('existsAdd')
			->times(4);

		$this->cache->shouldReceive('storeFacility')
			->once()
			->andReturn(123);

		$this->cache->shouldReceive('storeUser')
			->once()
			->andReturn(234);

		$this->cache->shouldReceive('storeGroup')
			->once();

		$data = [];

		$this->callProtectedMethod(
			$this->cache,
			'processLoop2420',
			[
				$mockObjects['loop'],
				&$data
			]
		);

		$this->assertSame(
			$mockObjects['segments'][0],
			$data['Loop2420_NM1'],
			'Loop2420 NM1 was not set correctly'
		);

		$this->assertEquals(
			123,
			$data['CurrentFacility'],
			'Current Facility was not set correctly'
		);

		$this->assertEquals(
			234,
			$data['CurrentUser'],
			'Current User was not set correctly'
		);
	}

	/**
	 * @covers SunCoastConnection\ClaimsToOEMR\X12N837\Cache::processLoop2420()
	 */
	public function testProcessLoop2420WithSegmentNM101_DN() {
		$mockObjects = $this->setupTestProcessLoop(
			Loop\Loop2420::class,
			[ 'NM1', 'N3', 'N4', 'PRV' ],
			Segment\NM1::class
		);

		$mockObjects['segments'][0]->shouldReceive('element')
			->once()
			->with('NM101')
			->andReturn('DN');

		$this->cache->shouldReceive('existsAdd')
			->times(1);

		$this->cache->shouldReceive('storeUser')
			->once()
			->andReturn(123);

		$this->cache->shouldReceive('storeGroup')
			->once();

		$data = [];

		$this->callProtectedMethod(
			$this->cache,
			'processLoop2420',
			[
				$mockObjects['loop'],
				&$data
			]
		);

		$this->assertSame(
			$mockObjects['segments'][0],
			$data['Loop2420_NM1'],
			'Loop2420 NM1 was not set correctly'
		);

		$this->assertEquals(
			123,
			$data['CurrentUser'],
			'Current User was not set correctly'
		);
	}

	/**
	 * @covers SunCoastConnection\ClaimsToOEMR\X12N837\Cache::processLoop2420()
	 */
	public function testProcessLoop2420WithSegmentN3() {
		$mockObjects = $this->setupTestProcessLoop(
			Loop\Loop2420::class,
			[ 'NM1', 'N3', 'N4', 'PRV' ],
			Segment\N3::class
		);

		$lastNM1 = $this->getMockery(
			Segment\NM1::class
		);

		$data = [];

		$this->callProtectedMethod(
			$this->cache,
			'processLoop2420',
			[
				$mockObjects['loop'],
				&$data
			]
		);

		$this->assertSame(
			$mockObjects['segments'][0],
			$data['Loop2420_N3'],
			'Loop2420 N3 was not set correctly'
		);
	}

	/**
	 * @covers SunCoastConnection\ClaimsToOEMR\X12N837\Cache::processLoop2420()
	 */
	public function testProcessLoop2420WithSegmentN4() {
		$mockObjects = $this->setupTestProcessLoop(
			Loop\Loop2420::class,
			[ 'NM1', 'N3', 'N4', 'PRV' ],
			Segment\N4::class
		);

		$lastNM1 = $this->getMockery(
			Segment\NM1::class
		);

		$data = [];

		$this->callProtectedMethod(
			$this->cache,
			'processLoop2420',
			[
				$mockObjects['loop'],
				&$data
			]
		);

		$this->assertSame(
			$mockObjects['segments'][0],
			$data['Loop2420_N4'],
			'Loop2420 N4 was not set correctly'
		);
	}

	/**
	 * @covers SunCoastConnection\ClaimsToOEMR\X12N837\Cache::processLoop2420()
	 */
	public function testProcessLoop2420WithSegmentPRV() {
		$mockObjects = $this->setupTestProcessLoop(
			Loop\Loop2420::class,
			[ 'NM1', 'N3', 'N4', 'PRV' ],
			Segment\PRV::class
		);

		$data = [];

		$this->callProtectedMethod(
			$this->cache,
			'processLoop2420',
			[
				$mockObjects['loop'],
				&$data
			]
		);

		$this->assertSame(
			$mockObjects['segments'][0],
			$data['Loop2420_PRV'],
			'Loop2420 PRV was not set correctly'
		);
	}
}