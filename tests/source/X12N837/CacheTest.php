<?php

namespace SunCoastConnection\ClaimsToOEMR\Tests\X12N837;

use \SunCoastConnection\ClaimsToOEMR\Tests\BaseTestCase,
	\SunCoastConnection\ClaimsToOEMR\Document\Options,
	\SunCoastConnection\ClaimsToOEMR\Document\Raw\Element,
	\SunCoastConnection\ClaimsToOEMR\Store,
	\SunCoastConnection\ClaimsToOEMR\X12N837,
	\SunCoastConnection\ClaimsToOEMR\X12N837\Cache,
	\SunCoastConnection\ClaimsToOEMR\X12N837\Envelope,
	\SunCoastConnection\ClaimsToOEMR\X12N837\Loop,
	\SunCoastConnection\ClaimsToOEMR\X12N837\Segment;

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
	 * @covers SunCoastConnection\ClaimsToOEMR\X12N837\Cache::processDocument()
	 */
	public function testProcessDocumentWithNoDescendantArray() {
		$x12N837 = $this->getMockery(
			X12N837::class
		);

		$x12N837->shouldReceive('getDescendant')
			->once()
			->andReturnNull();

		$this->cache->shouldAllowMockingProtectedMethods()
			->shouldNotReceive('processInterchangeControl');

		$this->cache->processDocument($x12N837);
	}

	/**
	 * @covers SunCoastConnection\ClaimsToOEMR\X12N837\Cache::processDocument()
	 */
	public function testProcessDocumentWithEmptyDescendantArray() {
		$descendant = [];

		$x12N837 = $this->getMockery(
			X12N837::class
		);

		$x12N837->shouldReceive('getDescendant')
			->once()
			->andReturn($descendant);

		$this->cache->shouldAllowMockingProtectedMethods()
			->shouldNotReceive('processInterchangeControl');

		$this->cache->processDocument($x12N837);
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

		$x12N837 = $this->getMockery(
			X12N837::class
		);

		$x12N837->shouldReceive('getDescendant')
			->once()
			->andReturn($descendant);

		$this->cache->shouldAllowMockingProtectedMethods()
			->shouldReceive('processInterchangeControl')
			->twice()
			->with($interchangeControl);

		$this->cache->processDocument($x12N837);
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
			[ 'NM1', 'N3', 'N4' ],
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

		$mockObjects['segments'][0]->shouldReceive('element')
			->once()
			->with('NM103')
			->andReturn($storeX12Partner['name']);

		$mockObjects['segments'][0]->shouldReceive('element')
			->once()
			->with('NM109')
			->andReturn($storeX12Partner['id_number']);

		$data = [
			'ISA' => $this->getMockery(
				Segment\ISA::class
			),
			'GS' => $this->getMockery(
				Segment\GS::class
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

		$data['GS']->shouldReceive('element')
			->once()
			->with('GS08')
			->andReturn($storeX12Partner['x12_version']);

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
			->with('GS02')
			->andReturn($storeX12Partner['x12_gs02']);

		$data['GS']->shouldReceive('element')
			->once()
			->with('GS03')
			->andReturn($storeX12Partner['x12_gs03']);

		$this->cache->shouldReceive('store->storeX12Partner')
			->once()
			->with($storeX12Partner)
			->andReturn('storeX12Partner');

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
			$data['LastNM1'],
			'Last NM1 was not set correctly'
		);

		$this->assertEquals(
			'storeX12Partner',
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
			[ 'NM1', 'N3', 'N4' ],
			Segment\NM1::class
		);

		$mockObjects['segments'][0]->shouldReceive('element')
			->once()
			->with('NM101')
			->andReturn('41');

		$mockObjects['segments'][0]->shouldReceive('element')
			->once()
			->with('NM103')
			->andReturn('SubmitterName');

		$mockObjects['segments'][0]->shouldReceive('element')
			->once()
			->with('NM109')
			->andReturn('SubmitterId');

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
			$data['LastNM1'],
			'Last NM1 was not set correctly'
		);

		$this->assertEquals(
			'SubmitterName',
			$data['SubmitterName'],
			'SubmitterName was not set correctly'
		);

		$this->assertEquals(
			'SubmitterId',
			$data['SubmitterId'],
			'SubmitterId was not set correctly'
		);
	}

	/**
	 * @covers SunCoastConnection\ClaimsToOEMR\X12N837\Cache::processLoop1000()
	 */
	public function testProcessLoop1000WithSegmentN3() {
		$mockObjects = $this->setupTestProcessLoop(
			Loop\Loop1000::class,
			[ 'NM1', 'N3', 'N4' ],
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
	}

	/**
	 * @covers SunCoastConnection\ClaimsToOEMR\X12N837\Cache::processLoop1000()
	 */
	public function testProcessLoop1000WithSegmentN4() {
		$mockObjects = $this->setupTestProcessLoop(
			Loop\Loop1000::class,
			[ 'NM1', 'N3', 'N4' ],
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

		$mockObjects['segments'][0]->shouldReceive('elementEquals')
			->once()
			->with('PRV01', 'BI')
			->andReturn(true);

		$mockObjects['segments'][0]->shouldReceive('element')
			->once()
			->with('PRV03')
			->andReturn('BillingProviderTaxonomy');

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

		$this->assertEquals(
			'BillingProviderTaxonomy',
			$data['BillingProviderTaxonomy'],
			'Billing Provider Taxonomy was not set correctly'
		);
	}

	/**
	 * @covers SunCoastConnection\ClaimsToOEMR\X12N837\Cache::processLoop2000()
	 */
	public function testProcessLoop2000WithSegmentSBR01_P() {
		$mockObjects = $this->setupTestProcessLoop(
			Loop\Loop2000::class,
			[ 'PRV', 'SBR', 'PAT' ],
			Segment\SBR::class
		);

		$mockObjects['segments'][0]->shouldReceive('element')
			->once()
			->with('SBR01')
			->andReturn('P');

		$mockObjects['segments'][0]->shouldReceive('elementEquals')
			->once()
			->with('SBR02', '18')
			->andReturn(true);

		$mockObjects['segments'][0]->shouldReceive('element')
			->once()
			->with('SBR03')
			->andReturn('PrimaryPolicy');

		$mockObjects['segments'][0]->shouldReceive('element')
			->once()
			->with('SBR04')
			->andReturn('PrimaryPlanName');

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

		$this->assertEquals(
			[
				'CurrentInsuranceType' => 1,
				'PrimarySubscriberRelation' => 'self',
				'PrimaryPolicy' => 'PrimaryPolicy',
				'PrimaryPlanName' => 'PrimaryPlanName',
			],
			$data,
			'Data not set correctly'
		);
	}

	/**
	 * @covers SunCoastConnection\ClaimsToOEMR\X12N837\Cache::processLoop2000()
	 */
	public function testProcessLoop2000WithSegmentSBR01_S() {
		$mockObjects = $this->setupTestProcessLoop(
			Loop\Loop2000::class,
			[ 'PRV', 'SBR', 'PAT' ],
			Segment\SBR::class
		);

		$mockObjects['segments'][0]->shouldReceive('element')
			->once()
			->with('SBR01')
			->andReturn('S');

		$mockObjects['segments'][0]->shouldReceive('elementEquals')
			->once()
			->with('SBR02', '18')
			->andReturn(true);

		$mockObjects['segments'][0]->shouldReceive('element')
			->once()
			->with('SBR03')
			->andReturn('SecondaryPolicy');

		$mockObjects['segments'][0]->shouldReceive('element')
			->once()
			->with('SBR04')
			->andReturn('SecondaryPlanName');

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

		$this->assertEquals(
			[
				'CurrentInsuranceType' => 2,
				'SecondarySubscriberRelation' => 'self',
				'SecondaryPolicy' => 'SecondaryPolicy',
				'SecondaryPlanName' => 'SecondaryPlanName',
			],
			$data,
			'Data not set correctly'
		);
	}

	/**
	 * @covers SunCoastConnection\ClaimsToOEMR\X12N837\Cache::processLoop2000()
	 */
	public function testProcessLoop2000WithSegmentSBR01_T() {
		$mockObjects = $this->setupTestProcessLoop(
			Loop\Loop2000::class,
			[ 'PRV', 'SBR', 'PAT' ],
			Segment\SBR::class
		);

		$mockObjects['segments'][0]->shouldReceive('element')
			->once()
			->with('SBR01')
			->andReturn('T');

		$mockObjects['segments'][0]->shouldReceive('elementEquals')
			->once()
			->with('SBR02', '18')
			->andReturn(true);

		$mockObjects['segments'][0]->shouldReceive('element')
			->once()
			->with('SBR03')
			->andReturn('TertiaryPolicy');

		$mockObjects['segments'][0]->shouldReceive('element')
			->once()
			->with('SBR04')
			->andReturn('TertiaryPlanName');

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

		$this->assertEquals(
			[
				'CurrentInsuranceType' => 3,
				'TertiarySubscriberRelation' => 'self',
				'TertiaryPolicy' => 'TertiaryPolicy',
				'TertiaryPlanName' => 'TertiaryPlanName',
			],
			$data,
			'Data not set correctly'
		);
	}

	/**
	 * @covers SunCoastConnection\ClaimsToOEMR\X12N837\Cache::processLoop2000()
	 */
	public function testProcessLoop2000WithSegmentPATAndNoInsuranceType() {
		$mockObjects = $this->setupTestProcessLoop(
			Loop\Loop2000::class,
			[ 'PRV', 'SBR', 'PAT' ],
			Segment\PAT::class
		);

		$mockObjects['loop']->shouldReceive('getDescendant')
			->once()
			->andReturnNull();

		$data = [];

		$this->expectOutputRegex('/^Missing: CurrentInsuranceType \[.+:\d+\]$/');

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
	public function testProcessLoop2000WithSegmentPATAndInsuranceType1() {
		$mockObjects = $this->setupTestProcessLoop(
			Loop\Loop2000::class,
			[ 'PRV', 'SBR', 'PAT' ],
			Segment\PAT::class
		);

		$mockObjects['segments'][0]->shouldReceive('element')
			->once()
			->with('PAT01')
			->andReturn('PrimaryPatientRelation');

		$mockObjects['loop']->shouldReceive('getDescendant')
			->once()
			->andReturnNull();

		$data = [
			'CurrentInsuranceType' => 1,
			'PrimarySubscriberRelation' => ''
		];

		$this->callProtectedMethod(
			$this->cache,
			'processLoop2000',
			[
				$mockObjects['loop'],
				&$data
			]
		);

		$this->assertEquals(
			'PrimaryPatientRelation',
			$data['PrimaryPatientRelation'],
			'Primary Patient Relation not set correctly'
		);
	}

	/**
	 * @covers SunCoastConnection\ClaimsToOEMR\X12N837\Cache::processLoop2000()
	 */
	public function testProcessLoop2000WithSegmentPATAndInsuranceType2() {
		$mockObjects = $this->setupTestProcessLoop(
			Loop\Loop2000::class,
			[ 'PRV', 'SBR', 'PAT' ],
			Segment\PAT::class
		);

		$mockObjects['segments'][0]->shouldReceive('element')
			->once()
			->with('PAT01')
			->andReturn('SecondaryPatientRelation');

		$mockObjects['loop']->shouldReceive('getDescendant')
			->once()
			->andReturnNull();

		$data = [
			'CurrentInsuranceType' => 2,
			'SecondarySubscriberRelation' => ''
		];

		$this->callProtectedMethod(
			$this->cache,
			'processLoop2000',
			[
				$mockObjects['loop'],
				&$data
			]
		);

		$this->assertEquals(
			'SecondaryPatientRelation',
			$data['SecondaryPatientRelation'],
			'Secondary Patient Relation not set correctly'
		);
	}

	/**
	 * @covers SunCoastConnection\ClaimsToOEMR\X12N837\Cache::processLoop2000()
	 */
	public function testProcessLoop2000WithSegmentPATAndInsuranceType3() {
		$mockObjects = $this->setupTestProcessLoop(
			Loop\Loop2000::class,
			[ 'PRV', 'SBR', 'PAT' ],
			Segment\PAT::class
		);

		$mockObjects['segments'][0]->shouldReceive('element')
			->once()
			->with('PAT01')
			->andReturn('TertiaryPatientRelation');

		$mockObjects['loop']->shouldReceive('getDescendant')
			->once()
			->andReturnNull();

		$data = [
			'CurrentInsuranceType' => 3,
			'TertiarySubscriberRelation' => ''
		];

		$this->callProtectedMethod(
			$this->cache,
			'processLoop2000',
			[
				$mockObjects['loop'],
				&$data
			]
		);

		$this->assertEquals(
			'TertiaryPatientRelation',
			$data['TertiaryPatientRelation'],
			'Tertiary Patient Relation not set correctly'
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

		$mockObjects['segments'][0]->shouldReceive('element')
			->once()
			->with('NM102')
			->andReturn('BillingType');

		$mockObjects['segments'][0]->shouldReceive('element')
			->once()
			->with('NM103')
			->andReturn('BillingProviderLastName');

		$mockObjects['segments'][0]->shouldReceive('element')
			->once()
			->with('NM104')
			->andReturn('BillingProviderFirstName');

		$mockObjects['segments'][0]->shouldReceive('element')
			->once()
			->with('NM105')
			->andReturn('BillingProviderMiddleName');

		$mockObjects['segments'][0]->shouldReceive('element')
			->once()
			->with('NM107')
			->andReturn('BillingProviderSuffix');

		$mockObjects['segments'][0]->shouldReceive('element')
			->once()
			->with('NM109')
			->andReturn('BillingProviderId');

		$data = [];

		$this->callProtectedMethod(
			$this->cache,
			'processLoop2010',
			[
				$mockObjects['loop'],
				&$data
			]
		);

		$this->assertEquals(
			[
				'LastNM1' => $mockObjects['segments'][0],
				'BillingType' => 'BillingType',
				'BillingProviderLastName' => 'BillingProviderLastName',
				'BillingProviderFirstName' => 'BillingProviderFirstName',
				'BillingProviderMiddleName' => 'BillingProviderMiddleName',
				'BillingProviderSuffix' => 'BillingProviderSuffix',
				'BillingProviderId' => 'BillingProviderId'
			],
			$data,
			'Data not set correctly'
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

		$mockObjects['segments'][0]->shouldReceive('element')
			->once()
			->with('NM102')
			->andReturn('PayToType');

		$mockObjects['segments'][0]->shouldReceive('element')
			->once()
			->with('NM103')
			->andReturn('PayToProviderLastName');

		$mockObjects['segments'][0]->shouldReceive('element')
			->once()
			->with('NM104')
			->andReturn('PayToProviderFirstName');

		$mockObjects['segments'][0]->shouldReceive('element')
			->once()
			->with('NM105')
			->andReturn('PayToProviderMiddleName');

		$mockObjects['segments'][0]->shouldReceive('element')
			->once()
			->with('NM107')
			->andReturn('PayToProviderSuffix');

		$mockObjects['segments'][0]->shouldReceive('element')
			->once()
			->with('NM109')
			->andReturn('PayToProviderId');

		$data = [];

		$this->callProtectedMethod(
			$this->cache,
			'processLoop2010',
			[
				$mockObjects['loop'],
				&$data
			]
		);

		$this->assertEquals(
			[
				'LastNM1' => $mockObjects['segments'][0],
				'PayToType' => 'PayToType',
				'PayToProviderLastName' => 'PayToProviderLastName',
				'PayToProviderFirstName' => 'PayToProviderFirstName',
				'PayToProviderMiddleName' => 'PayToProviderMiddleName',
				'PayToProviderSuffix' => 'PayToProviderSuffix',
				'PayToProviderId' => 'PayToProviderId'
			],
			$data,
			'Data not set correctly'
		);
	}

	/**
	 * @covers SunCoastConnection\ClaimsToOEMR\X12N837\Cache::processLoop2010()
	 */
	public function testProcessLoop2010WithSegmentNM101_ILAndNoInsuranceType() {
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

		$this->expectOutputRegex('/^Missing: CurrentInsuranceType \[.+:\d+\]$/');

		$this->callProtectedMethod(
			$this->cache,
			'processLoop2010',
			[
				$mockObjects['loop'],
				&$data
			]
		);

		$this->assertEquals(
			[
				'LastNM1' => $mockObjects['segments'][0],
			],
			$data,
			'Data not set correctly'
		);
	}

	/**
	 * @covers SunCoastConnection\ClaimsToOEMR\X12N837\Cache::processLoop2010()
	 */
	public function testProcessLoop2010WithSegmentNM101_ILAndInsuranceType1() {
		$mockObjects = $this->setupTestProcessLoop(
			Loop\Loop2010::class,
			[ 'NM1', 'N3', 'N4', 'DMG', 'REF' ],
			Segment\NM1::class
		);

		$mockObjects['segments'][0]->shouldReceive('element')
			->once()
			->with('NM101')
			->andReturn('IL');

		$mockObjects['segments'][0]->shouldReceive('element')
			->twice()
			->with('NM103')
			->andReturn('PrimarySubscriberLastName', 'PatientLastName');

		$mockObjects['segments'][0]->shouldReceive('element')
			->twice()
			->with('NM104')
			->andReturn('PrimarySubscriberFirstName', 'PatientFirstName');

		$mockObjects['segments'][0]->shouldReceive('element')
			->twice()
			->with('NM105')
			->andReturn('PrimarySubscriberMiddleName', 'PatientMiddleName');

		$mockObjects['segments'][0]->shouldReceive('element')
			->twice()
			->with('NM107')
			->andReturn('PrimarySubscriberSuffix', 'PatientSuffix');

		$mockObjects['segments'][0]->shouldReceive('element')
			->twice()
			->with('NM109')
			->andReturn('PrimarySubscriberId', 'PatientId');

		$data = [
			'CurrentInsuranceType' => 1,
			'PrimarySubscriberRelation' => 'self'
		];

		$this->callProtectedMethod(
			$this->cache,
			'processLoop2010',
			[
				$mockObjects['loop'],
				&$data
			]
		);

		$this->assertEquals(
			[
				'CurrentInsuranceType' => 1,
				'PrimarySubscriberRelation' => 'self',
				'LastNM1' => $mockObjects['segments'][0],
				'PrimarySubscriberLastName' => 'PrimarySubscriberLastName',
				'PrimarySubscriberFirstName' => 'PrimarySubscriberFirstName',
				'PrimarySubscriberMiddleName' => 'PrimarySubscriberMiddleName',
				'PrimarySubscriberSuffix' => 'PrimarySubscriberSuffix',
				'PrimarySubscriberId' => 'PrimarySubscriberId',
				'PatientLastName' => 'PatientLastName',
				'PatientFirstName' => 'PatientFirstName',
				'PatientMiddleName' => 'PatientMiddleName',
				'PatientSuffix' => 'PatientSuffix',
				'PatientId' => 'PatientId'
			],
			$data,
			'Data not set correctly'
		);
	}

	/**
	 * @covers SunCoastConnection\ClaimsToOEMR\X12N837\Cache::processLoop2010()
	 */
	public function testProcessLoop2010WithSegmentNM101_ILAndInsuranceType2() {
		$mockObjects = $this->setupTestProcessLoop(
			Loop\Loop2010::class,
			[ 'NM1', 'N3', 'N4', 'DMG', 'REF' ],
			Segment\NM1::class
		);

		$mockObjects['segments'][0]->shouldReceive('element')
			->once()
			->with('NM101')
			->andReturn('IL');

		$mockObjects['segments'][0]->shouldReceive('element')
			->twice()
			->with('NM103')
			->andReturn('SecondarySubscriberLastName', 'PatientLastName');

		$mockObjects['segments'][0]->shouldReceive('element')
			->twice()
			->with('NM104')
			->andReturn('SecondarySubscriberFirstName', 'PatientFirstName');

		$mockObjects['segments'][0]->shouldReceive('element')
			->twice()
			->with('NM105')
			->andReturn('SecondarySubscriberMiddleName', 'PatientMiddleName');

		$mockObjects['segments'][0]->shouldReceive('element')
			->twice()
			->with('NM107')
			->andReturn('SecondarySubscriberSuffix', 'PatientSuffix');

		$mockObjects['segments'][0]->shouldReceive('element')
			->twice()
			->with('NM109')
			->andReturn('SecondarySubscriberId', 'PatientId');

		$data = [
			'CurrentInsuranceType' => 2,
			'SecondarySubscriberRelation' => 'self'
		];

		$this->callProtectedMethod(
			$this->cache,
			'processLoop2010',
			[
				$mockObjects['loop'],
				&$data
			]
		);

		$this->assertEquals(
			[
				'CurrentInsuranceType' => 2,
				'SecondarySubscriberRelation' => 'self',
				'LastNM1' => $mockObjects['segments'][0],
				'SecondarySubscriberLastName' => 'SecondarySubscriberLastName',
				'SecondarySubscriberFirstName' => 'SecondarySubscriberFirstName',
				'SecondarySubscriberMiddleName' => 'SecondarySubscriberMiddleName',
				'SecondarySubscriberSuffix' => 'SecondarySubscriberSuffix',
				'SecondarySubscriberId' => 'SecondarySubscriberId',
				'PatientLastName' => 'PatientLastName',
				'PatientFirstName' => 'PatientFirstName',
				'PatientMiddleName' => 'PatientMiddleName',
				'PatientSuffix' => 'PatientSuffix',
				'PatientId' => 'PatientId'
			],
			$data,
			'Data not set correctly'
		);
	}

	/**
	 * @covers SunCoastConnection\ClaimsToOEMR\X12N837\Cache::processLoop2010()
	 */
	public function testProcessLoop2010WithSegmentNM101_ILAndInsuranceType3() {
		$mockObjects = $this->setupTestProcessLoop(
			Loop\Loop2010::class,
			[ 'NM1', 'N3', 'N4', 'DMG', 'REF' ],
			Segment\NM1::class
		);

		$mockObjects['segments'][0]->shouldReceive('element')
			->once()
			->with('NM101')
			->andReturn('IL');

		$mockObjects['segments'][0]->shouldReceive('element')
			->twice()
			->with('NM103')
			->andReturn('TertiarySubscriberLastName', 'PatientLastName');

		$mockObjects['segments'][0]->shouldReceive('element')
			->twice()
			->with('NM104')
			->andReturn('TertiarySubscriberFirstName', 'PatientFirstName');

		$mockObjects['segments'][0]->shouldReceive('element')
			->twice()
			->with('NM105')
			->andReturn('TertiarySubscriberMiddleName', 'PatientMiddleName');

		$mockObjects['segments'][0]->shouldReceive('element')
			->twice()
			->with('NM107')
			->andReturn('TertiarySubscriberSuffix', 'PatientSuffix');

		$mockObjects['segments'][0]->shouldReceive('element')
			->twice()
			->with('NM109')
			->andReturn('TertiarySubscriberId', 'PatientId');

		$data = [
			'CurrentInsuranceType' => 3,
			'TertiarySubscriberRelation' => 'self'
		];

		$this->callProtectedMethod(
			$this->cache,
			'processLoop2010',
			[
				$mockObjects['loop'],
				&$data
			]
		);

		$this->assertEquals(
			[
				'CurrentInsuranceType' => 3,
				'TertiarySubscriberRelation' => 'self',
				'LastNM1' => $mockObjects['segments'][0],
				'TertiarySubscriberLastName' => 'TertiarySubscriberLastName',
				'TertiarySubscriberFirstName' => 'TertiarySubscriberFirstName',
				'TertiarySubscriberMiddleName' => 'TertiarySubscriberMiddleName',
				'TertiarySubscriberSuffix' => 'TertiarySubscriberSuffix',
				'TertiarySubscriberId' => 'TertiarySubscriberId',
				'PatientLastName' => 'PatientLastName',
				'PatientFirstName' => 'PatientFirstName',
				'PatientMiddleName' => 'PatientMiddleName',
				'PatientSuffix' => 'PatientSuffix',
				'PatientId' => 'PatientId'
			],
			$data,
			'Data not set correctly'
		);
	}

	/**
	 * @covers SunCoastConnection\ClaimsToOEMR\X12N837\Cache::processLoop2010()
	 */
	public function testProcessLoop2010WithSegmentNM101_PRAndNoInsuranceType() {
		$mockObjects = $this->setupTestProcessLoop(
			Loop\Loop2010::class,
			[ 'NM1', 'N3', 'N4', 'DMG', 'REF' ],
			Segment\NM1::class
		);

		$mockObjects['segments'][0]->shouldReceive('element')
			->once()
			->with('NM101')
			->andReturn('PR');

		$data = [];

		$this->expectOutputRegex('/^Missing: CurrentInsuranceType \[.+:\d+\]$/');

		$this->callProtectedMethod(
			$this->cache,
			'processLoop2010',
			[
				$mockObjects['loop'],
				&$data
			]
		);

		$this->assertEquals(
			[
				'LastNM1' => $mockObjects['segments'][0],
			],
			$data,
			'Data not set correctly'
		);
	}

	/**
	 * @covers SunCoastConnection\ClaimsToOEMR\X12N837\Cache::processLoop2010()
	 */
	public function testProcessLoop2010WithSegmentNM101_PRAndInsuranceType1() {
		$mockObjects = $this->setupTestProcessLoop(
			Loop\Loop2010::class,
			[ 'NM1', 'N3', 'N4', 'DMG', 'REF' ],
			Segment\NM1::class
		);

		$mockObjects['segments'][0]->shouldReceive('element')
			->once()
			->with('NM101')
			->andReturn('PR');

		$mockObjects['segments'][0]->shouldReceive('element')
			->once()
			->with('NM103')
			->andReturn('PrimaryPayerName');

		$mockObjects['segments'][0]->shouldReceive('element')
			->once()
			->with('NM109')
			->andReturn('PrimaryPayerId');

		$data = [
			'CurrentInsuranceType' => 1
		];

		$this->callProtectedMethod(
			$this->cache,
			'processLoop2010',
			[
				$mockObjects['loop'],
				&$data
			]
		);

		$this->assertEquals(
			[
				'CurrentInsuranceType' => 1,
				'LastNM1' => $mockObjects['segments'][0],
				'PrimaryPayerName' => 'PrimaryPayerName',
				'PrimaryPayerId' => 'PrimaryPayerId'
			],
			$data,
			'Data not set correctly'
		);
	}

	/**
	 * @covers SunCoastConnection\ClaimsToOEMR\X12N837\Cache::processLoop2010()
	 */
	public function testProcessLoop2010WithSegmentNM101_PRAndInsuranceType2() {
		$mockObjects = $this->setupTestProcessLoop(
			Loop\Loop2010::class,
			[ 'NM1', 'N3', 'N4', 'DMG', 'REF' ],
			Segment\NM1::class
		);

		$mockObjects['segments'][0]->shouldReceive('element')
			->once()
			->with('NM101')
			->andReturn('PR');

		$mockObjects['segments'][0]->shouldReceive('element')
			->once()
			->with('NM103')
			->andReturn('SecondaryPayerName');

		$mockObjects['segments'][0]->shouldReceive('element')
			->once()
			->with('NM109')
			->andReturn('SecondaryPayerId');

		$data = [
			'CurrentInsuranceType' => 2
		];

		$this->callProtectedMethod(
			$this->cache,
			'processLoop2010',
			[
				$mockObjects['loop'],
				&$data
			]
		);

		$this->assertEquals(
			[
				'CurrentInsuranceType' => 2,
				'LastNM1' => $mockObjects['segments'][0],
				'SecondaryPayerName' => 'SecondaryPayerName',
				'SecondaryPayerId' => 'SecondaryPayerId'
			],
			$data,
			'Data not set correctly'
		);
	}

	/**
	 * @covers SunCoastConnection\ClaimsToOEMR\X12N837\Cache::processLoop2010()
	 */
	public function testProcessLoop2010WithSegmentNM101_PRAndInsuranceType3() {
		$mockObjects = $this->setupTestProcessLoop(
			Loop\Loop2010::class,
			[ 'NM1', 'N3', 'N4', 'DMG', 'REF' ],
			Segment\NM1::class
		);

		$mockObjects['segments'][0]->shouldReceive('element')
			->once()
			->with('NM101')
			->andReturn('PR');

		$mockObjects['segments'][0]->shouldReceive('element')
			->once()
			->with('NM103')
			->andReturn('TertiaryPayerName');

		$mockObjects['segments'][0]->shouldReceive('element')
			->once()
			->with('NM109')
			->andReturn('TertiaryPayerId');

		$data = [
			'CurrentInsuranceType' => 3
		];

		$this->callProtectedMethod(
			$this->cache,
			'processLoop2010',
			[
				$mockObjects['loop'],
				&$data
			]
		);

		$this->assertEquals(
			[
				'CurrentInsuranceType' => 3,
				'LastNM1' => $mockObjects['segments'][0],
				'TertiaryPayerName' => 'TertiaryPayerName',
				'TertiaryPayerId' => 'TertiaryPayerId'
			],
			$data,
			'Data not set correctly'
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

		$mockObjects['segments'][0]->shouldReceive('element')
			->once()
			->with('NM103')
			->andReturn('PatientLastName');

		$mockObjects['segments'][0]->shouldReceive('element')
			->once()
			->with('NM104')
			->andReturn('PatientFirstName');

		$mockObjects['segments'][0]->shouldReceive('element')
			->once()
			->with('NM105')
			->andReturn('PatientMiddleName');

		$mockObjects['segments'][0]->shouldReceive('element')
			->once()
			->with('NM107')
			->andReturn('PatientSuffix');

		$mockObjects['segments'][0]->shouldReceive('element')
			->once()
			->with('NM109')
			->andReturn('PatientId');

		$data = [];

		$this->callProtectedMethod(
			$this->cache,
			'processLoop2010',
			[
				$mockObjects['loop'],
				&$data
			]
		);

		$this->assertEquals(
			[
				'LastNM1' => $mockObjects['segments'][0],
				'PatientLastName' => 'PatientLastName',
				'PatientFirstName' => 'PatientFirstName',
				'PatientMiddleName' => 'PatientMiddleName',
				'PatientSuffix' => 'PatientSuffix',
				'PatientId' => 'PatientId'
			],
			$data,
			'Data not set correctly'
		);
	}

	/**
	 * @covers SunCoastConnection\ClaimsToOEMR\X12N837\Cache::processLoop2010()
	 */
	public function testProcessLoop2010WithSegmentN3AndNM101_85() {
		$mockObjects = $this->setupTestProcessLoop(
			Loop\Loop2010::class,
			[ 'NM1', 'N3', 'N4', 'DMG', 'REF' ],
			Segment\N3::class
		);

		$lastNM1 = $this->getMockery(
			Segment\NM1::class
		);

		$lastNM1->shouldReceive('element')
			->once()
			->with('NM101')
			->andReturn('85');

		$mockObjects['segments'][0]->shouldReceive('element')
			->once()
			->with('N301')
			->andReturn('BillingProviderAddress1');

		$mockObjects['segments'][0]->shouldReceive('element')
			->once()
			->with('N302')
			->andReturn('BillingProviderAddress2');

		$data = [
			'LastNM1' => $lastNM1
		];

		$this->callProtectedMethod(
			$this->cache,
			'processLoop2010',
			[
				$mockObjects['loop'],
				&$data
			]
		);

		$this->assertEquals(
			[
				'LastNM1' => $lastNM1,
				'BillingProviderAddress1' => 'BillingProviderAddress1',
				'BillingProviderAddress2' => 'BillingProviderAddress2'
			],
			$data,
			'Data not set correctly'
		);
	}

	/**
	 * @covers SunCoastConnection\ClaimsToOEMR\X12N837\Cache::processLoop2010()
	 */
	public function testProcessLoop2010WithSegmentN3AndNM101_87() {
		$mockObjects = $this->setupTestProcessLoop(
			Loop\Loop2010::class,
			[ 'NM1', 'N3', 'N4', 'DMG', 'REF' ],
			Segment\N3::class
		);

		$lastNM1 = $this->getMockery(
			Segment\NM1::class
		);

		$lastNM1->shouldReceive('element')
			->once()
			->with('NM101')
			->andReturn('87');

		$mockObjects['segments'][0]->shouldReceive('element')
			->once()
			->with('N301')
			->andReturn('PayToProviderAddress1');

		$mockObjects['segments'][0]->shouldReceive('element')
			->once()
			->with('N302')
			->andReturn('PayToProviderAddress2');

		$data = [
			'LastNM1' => $lastNM1
		];

		$this->callProtectedMethod(
			$this->cache,
			'processLoop2010',
			[
				$mockObjects['loop'],
				&$data
			]
		);

		$this->assertEquals(
			[
				'LastNM1' => $lastNM1,
				'PayToProviderAddress1' => 'PayToProviderAddress1',
				'PayToProviderAddress2' => 'PayToProviderAddress2'
			],
			$data,
			'Data not set correctly'
		);
	}

	/**
	 * @covers SunCoastConnection\ClaimsToOEMR\X12N837\Cache::processLoop2010()
	 */
	public function testProcessLoop2010WithSegmentN3AndNM101_ILAndNoInsuranceType() {
		$mockObjects = $this->setupTestProcessLoop(
			Loop\Loop2010::class,
			[ 'NM1', 'N3', 'N4', 'DMG', 'REF' ],
			Segment\N3::class
		);

		$lastNM1 = $this->getMockery(
			Segment\NM1::class
		);

		$lastNM1->shouldReceive('element')
			->once()
			->with('NM101')
			->andReturn('IL');

		$data = [
			'LastNM1' => $lastNM1
		];

		$this->expectOutputRegex('/^Missing: CurrentInsuranceType \[.+:\d+\]$/');

		$this->callProtectedMethod(
			$this->cache,
			'processLoop2010',
			[
				$mockObjects['loop'],
				&$data
			]
		);

		$this->assertEquals(
			[
				'LastNM1' => $lastNM1,
			],
			$data,
			'Data not set correctly'
		);
	}

	/**
	 * @covers SunCoastConnection\ClaimsToOEMR\X12N837\Cache::processLoop2010()
	 */
	public function testProcessLoop2010WithSegmentN3AndNM101_ILAndInsuranceType1() {
		$mockObjects = $this->setupTestProcessLoop(
			Loop\Loop2010::class,
			[ 'NM1', 'N3', 'N4', 'DMG', 'REF' ],
			Segment\N3::class
		);

		$lastNM1 = $this->getMockery(
			Segment\NM1::class
		);

		$lastNM1->shouldReceive('element')
			->once()
			->with('NM101')
			->andReturn('IL');

		$mockObjects['segments'][0]->shouldReceive('element')
			->twice()
			->with('N301')
			->andReturn('PrimarySubscriberAddress1', 'PatientAddress1');

		$mockObjects['segments'][0]->shouldReceive('element')
			->twice()
			->with('N302')
			->andReturn('PrimarySubscriberAddress2', 'PatientAddress2');

		$data = [
			'LastNM1' => $lastNM1,
			'CurrentInsuranceType' => 1,
			'PrimarySubscriberRelation' => 'self'
		];

		$this->callProtectedMethod(
			$this->cache,
			'processLoop2010',
			[
				$mockObjects['loop'],
				&$data
			]
		);

		$this->assertEquals(
			[
				'LastNM1' => $lastNM1,
				'CurrentInsuranceType' => 1,
				'PrimarySubscriberRelation' => 'self',
				'PrimarySubscriberAddress1' => 'PrimarySubscriberAddress1',
				'PrimarySubscriberAddress2' => 'PrimarySubscriberAddress2',
				'PatientAddress1' => 'PatientAddress1',
				'PatientAddress2' => 'PatientAddress2'
			],
			$data,
			'Data not set correctly'
		);
	}

	/**
	 * @covers SunCoastConnection\ClaimsToOEMR\X12N837\Cache::processLoop2010()
	 */
	public function testProcessLoop2010WithSegmentN3AndNM101_ILAndInsuranceType2() {
		$mockObjects = $this->setupTestProcessLoop(
			Loop\Loop2010::class,
			[ 'NM1', 'N3', 'N4', 'DMG', 'REF' ],
			Segment\N3::class
		);

		$lastNM1 = $this->getMockery(
			Segment\NM1::class
		);

		$lastNM1->shouldReceive('element')
			->once()
			->with('NM101')
			->andReturn('IL');

		$mockObjects['segments'][0]->shouldReceive('element')
			->twice()
			->with('N301')
			->andReturn('SecondarySubscriberAddress1', 'PatientAddress1');

		$mockObjects['segments'][0]->shouldReceive('element')
			->twice()
			->with('N302')
			->andReturn('SecondarySubscriberAddress2', 'PatientAddress2');

		$data = [
			'LastNM1' => $lastNM1,
			'CurrentInsuranceType' => 2,
			'SecondarySubscriberRelation' => 'self'
		];

		$this->callProtectedMethod(
			$this->cache,
			'processLoop2010',
			[
				$mockObjects['loop'],
				&$data
			]
		);

		$this->assertEquals(
			[
				'LastNM1' => $lastNM1,
				'CurrentInsuranceType' => 2,
				'SecondarySubscriberRelation' => 'self',
				'SecondarySubscriberAddress1' => 'SecondarySubscriberAddress1',
				'SecondarySubscriberAddress2' => 'SecondarySubscriberAddress2',
				'PatientAddress1' => 'PatientAddress1',
				'PatientAddress2' => 'PatientAddress2'
			],
			$data,
			'Data not set correctly'
		);
	}

	/**
	 * @covers SunCoastConnection\ClaimsToOEMR\X12N837\Cache::processLoop2010()
	 */
	public function testProcessLoop2010WithSegmentN3AndNM101_ILAndInsuranceType3() {
		$mockObjects = $this->setupTestProcessLoop(
			Loop\Loop2010::class,
			[ 'NM1', 'N3', 'N4', 'DMG', 'REF' ],
			Segment\N3::class
		);

		$lastNM1 = $this->getMockery(
			Segment\NM1::class
		);

		$lastNM1->shouldReceive('element')
			->once()
			->with('NM101')
			->andReturn('IL');

		$mockObjects['segments'][0]->shouldReceive('element')
			->twice()
			->with('N301')
			->andReturn('TertiarySubscriberAddress1', 'PatientAddress1');

		$mockObjects['segments'][0]->shouldReceive('element')
			->twice()
			->with('N302')
			->andReturn('TertiarySubscriberAddress2', 'PatientAddress2');

		$data = [
			'LastNM1' => $lastNM1,
			'CurrentInsuranceType' => 3,
			'TertiarySubscriberRelation' => 'self'
		];

		$this->callProtectedMethod(
			$this->cache,
			'processLoop2010',
			[
				$mockObjects['loop'],
				&$data
			]
		);

		$this->assertEquals(
			[
				'LastNM1' => $lastNM1,
				'CurrentInsuranceType' => 3,
				'TertiarySubscriberRelation' => 'self',
				'TertiarySubscriberAddress1' => 'TertiarySubscriberAddress1',
				'TertiarySubscriberAddress2' => 'TertiarySubscriberAddress2',
				'PatientAddress1' => 'PatientAddress1',
				'PatientAddress2' => 'PatientAddress2'
			],
			$data,
			'Data not set correctly'
		);
	}

	/**
	 * @covers SunCoastConnection\ClaimsToOEMR\X12N837\Cache::processLoop2010()
	 */
	public function testProcessLoop2010WithSegmentN3AndNM101_PRAndNoInsuranceType() {
		$mockObjects = $this->setupTestProcessLoop(
			Loop\Loop2010::class,
			[ 'NM1', 'N3', 'N4', 'DMG', 'REF' ],
			Segment\N3::class
		);

		$lastNM1 = $this->getMockery(
			Segment\NM1::class
		);

		$lastNM1->shouldReceive('element')
			->once()
			->with('NM101')
			->andReturn('PR');

		$data = [
			'LastNM1' => $lastNM1
		];

		$this->expectOutputRegex('/^Missing: CurrentInsuranceType \[.+:\d+\]$/');

		$this->callProtectedMethod(
			$this->cache,
			'processLoop2010',
			[
				$mockObjects['loop'],
				&$data
			]
		);

		$this->assertEquals(
			[
				'LastNM1' => $lastNM1,
			],
			$data,
			'Data not set correctly'
		);
	}

	/**
	 * @covers SunCoastConnection\ClaimsToOEMR\X12N837\Cache::processLoop2010()
	 */
	public function testProcessLoop2010WithSegmentN3AndNM101_PRAndInsuranceType1() {
		$mockObjects = $this->setupTestProcessLoop(
			Loop\Loop2010::class,
			[ 'NM1', 'N3', 'N4', 'DMG', 'REF' ],
			Segment\N3::class
		);

		$lastNM1 = $this->getMockery(
			Segment\NM1::class
		);

		$lastNM1->shouldReceive('element')
			->once()
			->with('NM101')
			->andReturn('PR');

		$mockObjects['segments'][0]->shouldReceive('element')
			->once()
			->with('N301')
			->andReturn('PrimaryPayerAddress1');

		$mockObjects['segments'][0]->shouldReceive('element')
			->once()
			->with('N302')
			->andReturn('PrimaryPayerAddress2');

		$data = [
			'LastNM1' => $lastNM1,
			'CurrentInsuranceType' => 1,
		];

		$this->callProtectedMethod(
			$this->cache,
			'processLoop2010',
			[
				$mockObjects['loop'],
				&$data
			]
		);

		$this->assertEquals(
			[
				'LastNM1' => $lastNM1,
				'CurrentInsuranceType' => 1,
				'PrimaryPayerAddress1' => 'PrimaryPayerAddress1',
				'PrimaryPayerAddress2' => 'PrimaryPayerAddress2'
			],
			$data,
			'Data not set correctly'
		);
	}

	/**
	 * @covers SunCoastConnection\ClaimsToOEMR\X12N837\Cache::processLoop2010()
	 */
	public function testProcessLoop2010WithSegmentN3AndNM101_PRAndInsuranceType2() {
		$mockObjects = $this->setupTestProcessLoop(
			Loop\Loop2010::class,
			[ 'NM1', 'N3', 'N4', 'DMG', 'REF' ],
			Segment\N3::class
		);

		$lastNM1 = $this->getMockery(
			Segment\NM1::class
		);

		$lastNM1->shouldReceive('element')
			->once()
			->with('NM101')
			->andReturn('PR');

		$mockObjects['segments'][0]->shouldReceive('element')
			->once()
			->with('N301')
			->andReturn('SecondaryPayerAddress1');

		$mockObjects['segments'][0]->shouldReceive('element')
			->once()
			->with('N302')
			->andReturn('SecondaryPayerAddress2');

		$data = [
			'LastNM1' => $lastNM1,
			'CurrentInsuranceType' => 2,
		];

		$this->callProtectedMethod(
			$this->cache,
			'processLoop2010',
			[
				$mockObjects['loop'],
				&$data
			]
		);

		$this->assertEquals(
			[
				'LastNM1' => $lastNM1,
				'CurrentInsuranceType' => 2,
				'SecondaryPayerAddress1' => 'SecondaryPayerAddress1',
				'SecondaryPayerAddress2' => 'SecondaryPayerAddress2'
			],
			$data,
			'Data not set correctly'
		);
	}

	/**
	 * @covers SunCoastConnection\ClaimsToOEMR\X12N837\Cache::processLoop2010()
	 */
	public function testProcessLoop2010WithSegmentN3AndNM101_PRAndInsuranceType3() {
		$mockObjects = $this->setupTestProcessLoop(
			Loop\Loop2010::class,
			[ 'NM1', 'N3', 'N4', 'DMG', 'REF' ],
			Segment\N3::class
		);

		$lastNM1 = $this->getMockery(
			Segment\NM1::class
		);

		$lastNM1->shouldReceive('element')
			->once()
			->with('NM101')
			->andReturn('PR');

		$mockObjects['segments'][0]->shouldReceive('element')
			->once()
			->with('N301')
			->andReturn('TertiaryPayerAddress1');

		$mockObjects['segments'][0]->shouldReceive('element')
			->once()
			->with('N302')
			->andReturn('TertiaryPayerAddress2');

		$data = [
			'LastNM1' => $lastNM1,
			'CurrentInsuranceType' => 3,
		];

		$this->callProtectedMethod(
			$this->cache,
			'processLoop2010',
			[
				$mockObjects['loop'],
				&$data
			]
		);

		$this->assertEquals(
			[
				'LastNM1' => $lastNM1,
				'CurrentInsuranceType' => 3,
				'TertiaryPayerAddress1' => 'TertiaryPayerAddress1',
				'TertiaryPayerAddress2' => 'TertiaryPayerAddress2'
			],
			$data,
			'Data not set correctly'
		);
	}

	/**
	 * @covers SunCoastConnection\ClaimsToOEMR\X12N837\Cache::processLoop2010()
	 */
	public function testProcessLoop2010WithSegmentN3AndNM101_QC() {
		$mockObjects = $this->setupTestProcessLoop(
			Loop\Loop2010::class,
			[ 'NM1', 'N3', 'N4', 'DMG', 'REF' ],
			Segment\N3::class
		);

		$lastNM1 = $this->getMockery(
			Segment\NM1::class
		);

		$lastNM1->shouldReceive('element')
			->once()
			->with('NM101')
			->andReturn('QC');

		$mockObjects['segments'][0]->shouldReceive('element')
			->once()
			->with('N301')
			->andReturn('PatientAddress1');

		$mockObjects['segments'][0]->shouldReceive('element')
			->once()
			->with('N302')
			->andReturn('PatientAddress2');

		$data = [
			'LastNM1' => $lastNM1
		];

		$this->callProtectedMethod(
			$this->cache,
			'processLoop2010',
			[
				$mockObjects['loop'],
				&$data
			]
		);

		$this->assertEquals(
			[
				'LastNM1' => $lastNM1,
				'PatientAddress1' => 'PatientAddress1',
				'PatientAddress2' => 'PatientAddress2',
			],
			$data,
			'Data not set correctly'
		);
	}

	/**
	 * @covers SunCoastConnection\ClaimsToOEMR\X12N837\Cache::processLoop2010()
	 */
	public function testProcessLoop2010WithSegmentN4AndNM101_85() {
		$mockObjects = $this->setupTestProcessLoop(
			Loop\Loop2010::class,
			[ 'NM1', 'N3', 'N4', 'DMG', 'REF' ],
			Segment\N4::class
		);

		$lastNM1 = $this->getMockery(
			Segment\NM1::class
		);

		$lastNM1->shouldReceive('element')
			->once()
			->with('NM101')
			->andReturn('85');

		$mockObjects['segments'][0]->shouldReceive('element')
			->once()
			->with('N401')
			->andReturn('BillingProviderCity');

		$mockObjects['segments'][0]->shouldReceive('element')
			->once()
			->with('N402')
			->andReturn('BillingProviderState');

		$mockObjects['segments'][0]->shouldReceive('element')
			->once()
			->with('N403')
			->andReturn('BillingProviderZip');

		$data = [
			'LastNM1' => $lastNM1
		];

		$this->callProtectedMethod(
			$this->cache,
			'processLoop2010',
			[
				$mockObjects['loop'],
				&$data
			]
		);

		$this->assertEquals(
			[
				'LastNM1' => $lastNM1,
				'BillingProviderCity' => 'BillingProviderCity',
				'BillingProviderState' => 'BillingProviderState',
				'BillingProviderZip' => 'BillingProviderZip'
			],
			$data,
			'Data not set correctly'
		);
	}

	/**
	 * @covers SunCoastConnection\ClaimsToOEMR\X12N837\Cache::processLoop2010()
	 */
	public function testProcessLoop2010WithSegmentN4AndNM101_87() {
		$mockObjects = $this->setupTestProcessLoop(
			Loop\Loop2010::class,
			[ 'NM1', 'N3', 'N4', 'DMG', 'REF' ],
			Segment\N4::class
		);

		$lastNM1 = $this->getMockery(
			Segment\NM1::class
		);

		$lastNM1->shouldReceive('element')
			->once()
			->with('NM101')
			->andReturn('87');

		$mockObjects['segments'][0]->shouldReceive('element')
			->once()
			->with('N401')
			->andReturn('PayToProviderCity');

		$mockObjects['segments'][0]->shouldReceive('element')
			->once()
			->with('N402')
			->andReturn('PayToProviderState');

		$mockObjects['segments'][0]->shouldReceive('element')
			->once()
			->with('N403')
			->andReturn('PayToProviderZip');

		$data = [
			'LastNM1' => $lastNM1
		];

		$this->callProtectedMethod(
			$this->cache,
			'processLoop2010',
			[
				$mockObjects['loop'],
				&$data
			]
		);

		$this->assertEquals(
			[
				'LastNM1' => $lastNM1,
				'PayToProviderCity' => 'PayToProviderCity',
				'PayToProviderState' => 'PayToProviderState',
				'PayToProviderZip' => 'PayToProviderZip'
			],
			$data,
			'Data not set correctly'
		);
	}

	/**
	 * @covers SunCoastConnection\ClaimsToOEMR\X12N837\Cache::processLoop2010()
	 */
	public function testProcessLoop2010WithSegmentN4AndNM101_ILAndNoInsuranceType() {
		$mockObjects = $this->setupTestProcessLoop(
			Loop\Loop2010::class,
			[ 'NM1', 'N3', 'N4', 'DMG', 'REF' ],
			Segment\N4::class
		);

		$lastNM1 = $this->getMockery(
			Segment\NM1::class
		);

		$lastNM1->shouldReceive('element')
			->once()
			->with('NM101')
			->andReturn('IL');

		$data = [
			'LastNM1' => $lastNM1
		];

		$this->expectOutputRegex('/^Missing: CurrentInsuranceType \[.+:\d+\]$/');

		$this->callProtectedMethod(
			$this->cache,
			'processLoop2010',
			[
				$mockObjects['loop'],
				&$data
			]
		);

		$this->assertEquals(
			[
				'LastNM1' => $lastNM1,
			],
			$data,
			'Data not set correctly'
		);
	}

	/**
	 * @covers SunCoastConnection\ClaimsToOEMR\X12N837\Cache::processLoop2010()
	 */
	public function testProcessLoop2010WithSegmentN4AndNM101_ILAndInsuranceType1() {
		$mockObjects = $this->setupTestProcessLoop(
			Loop\Loop2010::class,
			[ 'NM1', 'N3', 'N4', 'DMG', 'REF' ],
			Segment\N4::class
		);

		$lastNM1 = $this->getMockery(
			Segment\NM1::class
		);

		$lastNM1->shouldReceive('element')
			->once()
			->with('NM101')
			->andReturn('IL');

		$mockObjects['segments'][0]->shouldReceive('element')
			->twice()
			->with('N401')
			->andReturn('PrimarySubscriberCity', 'PatientCity');

		$mockObjects['segments'][0]->shouldReceive('element')
			->twice()
			->with('N402')
			->andReturn('PrimarySubscriberState', 'PatientState');

		$mockObjects['segments'][0]->shouldReceive('element')
			->twice()
			->with('N403')
			->andReturn('PrimarySubscriberZip', 'PatientZip');

		$data = [
			'LastNM1' => $lastNM1,
			'CurrentInsuranceType' => 1,
			'PrimarySubscriberRelation' => 'self'
		];

		$this->callProtectedMethod(
			$this->cache,
			'processLoop2010',
			[
				$mockObjects['loop'],
				&$data
			]
		);

		$this->assertEquals(
			[
				'LastNM1' => $lastNM1,
				'CurrentInsuranceType' => 1,
				'PrimarySubscriberRelation' => 'self',
				'PrimarySubscriberCity' => 'PrimarySubscriberCity',
				'PrimarySubscriberState' => 'PrimarySubscriberState',
				'PrimarySubscriberZip' => 'PrimarySubscriberZip',
				'PatientCity' => 'PatientCity',
				'PatientState' => 'PatientState',
				'PatientZip' => 'PatientZip'
			],
			$data,
			'Data not set correctly'
		);
	}

	/**
	 * @covers SunCoastConnection\ClaimsToOEMR\X12N837\Cache::processLoop2010()
	 */
	public function testProcessLoop2010WithSegmentN4AndNM101_ILAndInsuranceType2() {
		$mockObjects = $this->setupTestProcessLoop(
			Loop\Loop2010::class,
			[ 'NM1', 'N3', 'N4', 'DMG', 'REF' ],
			Segment\N4::class
		);

		$lastNM1 = $this->getMockery(
			Segment\NM1::class
		);

		$lastNM1->shouldReceive('element')
			->once()
			->with('NM101')
			->andReturn('IL');

		$mockObjects['segments'][0]->shouldReceive('element')
			->twice()
			->with('N401')
			->andReturn('SecondarySubscriberCity', 'PatientCity');

		$mockObjects['segments'][0]->shouldReceive('element')
			->twice()
			->with('N402')
			->andReturn('SecondarySubscriberState', 'PatientState');

		$mockObjects['segments'][0]->shouldReceive('element')
			->twice()
			->with('N403')
			->andReturn('SecondarySubscriberZip', 'PatientZip');

		$data = [
			'LastNM1' => $lastNM1,
			'CurrentInsuranceType' => 2,
			'SecondarySubscriberRelation' => 'self'
		];

		$this->callProtectedMethod(
			$this->cache,
			'processLoop2010',
			[
				$mockObjects['loop'],
				&$data
			]
		);

		$this->assertEquals(
			[
				'LastNM1' => $lastNM1,
				'CurrentInsuranceType' => 2,
				'SecondarySubscriberRelation' => 'self',
				'SecondarySubscriberCity' => 'SecondarySubscriberCity',
				'SecondarySubscriberState' => 'SecondarySubscriberState',
				'SecondarySubscriberZip' => 'SecondarySubscriberZip',
				'PatientCity' => 'PatientCity',
				'PatientState' => 'PatientState',
				'PatientZip' => 'PatientZip'
			],
			$data,
			'Data not set correctly'
		);
	}

	/**
	 * @covers SunCoastConnection\ClaimsToOEMR\X12N837\Cache::processLoop2010()
	 */
	public function testProcessLoop2010WithSegmentN4AndNM101_ILAndInsuranceType3() {
		$mockObjects = $this->setupTestProcessLoop(
			Loop\Loop2010::class,
			[ 'NM1', 'N3', 'N4', 'DMG', 'REF' ],
			Segment\N4::class
		);

		$lastNM1 = $this->getMockery(
			Segment\NM1::class
		);

		$lastNM1->shouldReceive('element')
			->once()
			->with('NM101')
			->andReturn('IL');

		$mockObjects['segments'][0]->shouldReceive('element')
			->twice()
			->with('N401')
			->andReturn('TertiarySubscriberCity', 'PatientCity');

		$mockObjects['segments'][0]->shouldReceive('element')
			->twice()
			->with('N402')
			->andReturn('TertiarySubscriberState', 'PatientState');

		$mockObjects['segments'][0]->shouldReceive('element')
			->twice()
			->with('N403')
			->andReturn('TertiarySubscriberZip', 'PatientZip');

		$data = [
			'LastNM1' => $lastNM1,
			'CurrentInsuranceType' => 3,
			'TertiarySubscriberRelation' => 'self'
		];

		$this->callProtectedMethod(
			$this->cache,
			'processLoop2010',
			[
				$mockObjects['loop'],
				&$data
			]
		);

		$this->assertEquals(
			[
				'LastNM1' => $lastNM1,
				'CurrentInsuranceType' => 3,
				'TertiarySubscriberRelation' => 'self',
				'TertiarySubscriberCity' => 'TertiarySubscriberCity',
				'TertiarySubscriberState' => 'TertiarySubscriberState',
				'TertiarySubscriberZip' => 'TertiarySubscriberZip',
				'PatientCity' => 'PatientCity',
				'PatientState' => 'PatientState',
				'PatientZip' => 'PatientZip'
			],
			$data,
			'Data not set correctly'
		);
	}

	/**
	 * @covers SunCoastConnection\ClaimsToOEMR\X12N837\Cache::processLoop2010()
	 */
	public function testProcessLoop2010WithSegmentN4AndNM101_PRAndNoInsuranceType() {
		$mockObjects = $this->setupTestProcessLoop(
			Loop\Loop2010::class,
			[ 'NM1', 'N3', 'N4', 'DMG', 'REF' ],
			Segment\N4::class
		);

		$lastNM1 = $this->getMockery(
			Segment\NM1::class
		);

		$lastNM1->shouldReceive('element')
			->once()
			->with('NM101')
			->andReturn('PR');

		$data = [
			'LastNM1' => $lastNM1
		];

		$this->expectOutputRegex('/^Missing: CurrentInsuranceType \[.+:\d+\]$/');

		$this->callProtectedMethod(
			$this->cache,
			'processLoop2010',
			[
				$mockObjects['loop'],
				&$data
			]
		);

		$this->assertEquals(
			[
				'LastNM1' => $lastNM1,
			],
			$data,
			'Data not set correctly'
		);
	}

	/**
	 * @covers SunCoastConnection\ClaimsToOEMR\X12N837\Cache::processLoop2010()
	 */
	public function testProcessLoop2010WithSegmentN4AndNM101_PRAndInsuranceType1() {
		$mockObjects = $this->setupTestProcessLoop(
			Loop\Loop2010::class,
			[ 'NM1', 'N3', 'N4', 'DMG', 'REF' ],
			Segment\N4::class
		);

		$lastNM1 = $this->getMockery(
			Segment\NM1::class
		);

		$lastNM1->shouldReceive('element')
			->once()
			->with('NM101')
			->andReturn('PR');

		$mockObjects['segments'][0]->shouldReceive('element')
			->once()
			->with('N401')
			->andReturn('PrimaryPayerCity');

		$mockObjects['segments'][0]->shouldReceive('element')
			->once()
			->with('N402')
			->andReturn('PrimaryPayerState');

		$mockObjects['segments'][0]->shouldReceive('element')
			->once()
			->with('N403')
			->andReturn('PrimaryPayerZip');

		$data = [
			'LastNM1' => $lastNM1,
			'CurrentInsuranceType' => 1,
		];

		$this->callProtectedMethod(
			$this->cache,
			'processLoop2010',
			[
				$mockObjects['loop'],
				&$data
			]
		);

		$this->assertEquals(
			[
				'LastNM1' => $lastNM1,
				'CurrentInsuranceType' => 1,
				'PrimaryPayerCity' => 'PrimaryPayerCity',
				'PrimaryPayerState' => 'PrimaryPayerState',
				'PrimaryPayerZip' => 'PrimaryPayerZip'
			],
			$data,
			'Data not set correctly'
		);
	}

	/**
	 * @covers SunCoastConnection\ClaimsToOEMR\X12N837\Cache::processLoop2010()
	 */
	public function testProcessLoop2010WithSegmentN4AndNM101_PRAndInsuranceType2() {
		$mockObjects = $this->setupTestProcessLoop(
			Loop\Loop2010::class,
			[ 'NM1', 'N3', 'N4', 'DMG', 'REF' ],
			Segment\N4::class
		);

		$lastNM1 = $this->getMockery(
			Segment\NM1::class
		);

		$lastNM1->shouldReceive('element')
			->once()
			->with('NM101')
			->andReturn('PR');

		$mockObjects['segments'][0]->shouldReceive('element')
			->once()
			->with('N401')
			->andReturn('SecondaryPayerCity');

		$mockObjects['segments'][0]->shouldReceive('element')
			->once()
			->with('N402')
			->andReturn('SecondaryPayerState');

		$mockObjects['segments'][0]->shouldReceive('element')
			->once()
			->with('N403')
			->andReturn('SecondaryPayerZip');

		$data = [
			'LastNM1' => $lastNM1,
			'CurrentInsuranceType' => 2,
		];

		$this->callProtectedMethod(
			$this->cache,
			'processLoop2010',
			[
				$mockObjects['loop'],
				&$data
			]
		);

		$this->assertEquals(
			[
				'LastNM1' => $lastNM1,
				'CurrentInsuranceType' => 2,
				'SecondaryPayerCity' => 'SecondaryPayerCity',
				'SecondaryPayerState' => 'SecondaryPayerState',
				'SecondaryPayerZip' => 'SecondaryPayerZip'
			],
			$data,
			'Data not set correctly'
		);
	}

	/**
	 * @covers SunCoastConnection\ClaimsToOEMR\X12N837\Cache::processLoop2010()
	 */
	public function testProcessLoop2010WithSegmentN4AndNM101_PRAndInsuranceType3() {
		$mockObjects = $this->setupTestProcessLoop(
			Loop\Loop2010::class,
			[ 'NM1', 'N3', 'N4', 'DMG', 'REF' ],
			Segment\N4::class
		);

		$lastNM1 = $this->getMockery(
			Segment\NM1::class
		);

		$lastNM1->shouldReceive('element')
			->once()
			->with('NM101')
			->andReturn('PR');

		$mockObjects['segments'][0]->shouldReceive('element')
			->once()
			->with('N401')
			->andReturn('TertiaryPayerCity');

		$mockObjects['segments'][0]->shouldReceive('element')
			->once()
			->with('N402')
			->andReturn('TertiaryPayerState');

		$mockObjects['segments'][0]->shouldReceive('element')
			->once()
			->with('N403')
			->andReturn('TertiaryPayerZip');

		$data = [
			'LastNM1' => $lastNM1,
			'CurrentInsuranceType' => 3,
		];

		$this->callProtectedMethod(
			$this->cache,
			'processLoop2010',
			[
				$mockObjects['loop'],
				&$data
			]
		);

		$this->assertEquals(
			[
				'LastNM1' => $lastNM1,
				'CurrentInsuranceType' => 3,
				'TertiaryPayerCity' => 'TertiaryPayerCity',
				'TertiaryPayerState' => 'TertiaryPayerState',
				'TertiaryPayerZip' => 'TertiaryPayerZip'
			],
			$data,
			'Data not set correctly'
		);
	}

	/**
	 * @covers SunCoastConnection\ClaimsToOEMR\X12N837\Cache::processLoop2010()
	 */
	public function testProcessLoop2010WithSegmentN4AndNM101_QC() {
		$mockObjects = $this->setupTestProcessLoop(
			Loop\Loop2010::class,
			[ 'NM1', 'N3', 'N4', 'DMG', 'REF' ],
			Segment\N4::class
		);

		$lastNM1 = $this->getMockery(
			Segment\NM1::class
		);

		$lastNM1->shouldReceive('element')
			->once()
			->with('NM101')
			->andReturn('QC');

		$mockObjects['segments'][0]->shouldReceive('element')
			->once()
			->with('N401')
			->andReturn('PatientCity');

		$mockObjects['segments'][0]->shouldReceive('element')
			->once()
			->with('N402')
			->andReturn('PatientState');

		$mockObjects['segments'][0]->shouldReceive('element')
			->once()
			->with('N403')
			->andReturn('PatientZip');

		$data = [
			'LastNM1' => $lastNM1
		];

		$this->callProtectedMethod(
			$this->cache,
			'processLoop2010',
			[
				$mockObjects['loop'],
				&$data
			]
		);

		$this->assertEquals(
			[
				'LastNM1' => $lastNM1,
				'PatientCity' => 'PatientCity',
				'PatientState' => 'PatientState',
				'PatientZip' => 'PatientZip'
			],
			$data,
			'Data not set correctly'
		);
	}

	/**
	 * @covers SunCoastConnection\ClaimsToOEMR\X12N837\Cache::processLoop2010()
	 */
	public function testProcessLoop2010WithSegmentDMGAndNoInsuranceType() {
		$mockObjects = $this->setupTestProcessLoop(
			Loop\Loop2010::class,
			[ 'NM1', 'N3', 'N4', 'DMG', 'REF' ],
			Segment\DMG::class
		);

		$mockObjects['segments'][0]->shouldReceive('element')
			->once()
			->with('DMG02')
			->andReturn('SubDOB');

		$mockObjects['segments'][0]->shouldReceive('element')
			->once()
			->with('DMG03')
			->andReturn('SubSex');

		$data = [];

		$this->expectOutputRegex('/^Missing: CurrentInsuranceType \[.+:\d+\]$/');

		$this->callProtectedMethod(
			$this->cache,
			'processLoop2010',
			[
				$mockObjects['loop'],
				&$data
			]
		);

		$this->assertEquals(
			[
				'SubDOB' => 'SubDOB',
				'SubSex' => 'SubSex'
			],
			$data,
			'Data not set correctly'
		);
	}

	/**
	 * @covers SunCoastConnection\ClaimsToOEMR\X12N837\Cache::processLoop2010()
	 */
	public function testProcessLoop2010WithSegmentDMGAndInsuranceType1() {
		$mockObjects = $this->setupTestProcessLoop(
			Loop\Loop2010::class,
			[ 'NM1', 'N3', 'N4', 'DMG', 'REF' ],
			Segment\DMG::class
		);

		$mockObjects['segments'][0]->shouldReceive('element')
			->times(3)
			->with('DMG02')
			->andReturn('SubDOB', 'PrimarySubscriberDOB', 'PatientSex');

		$mockObjects['segments'][0]->shouldReceive('element')
			->times(3)
			->with('DMG03')
			->andReturn('SubSex', 'PrimarySubscriberSex', 'PatientSex');

		$data = [
			'CurrentInsuranceType' => 1,
			'PrimarySubscriberRelation' => 'self'
		];

		$this->callProtectedMethod(
			$this->cache,
			'processLoop2010',
			[
				$mockObjects['loop'],
				&$data
			]
		);

		$this->assertEquals(
			[
				'CurrentInsuranceType' => 1,
				'PrimarySubscriberRelation' => 'self',
				'SubDOB' => 'SubDOB',
				'SubSex' => 'SubSex',
				'PrimarySubscriberDOB' => 'PrimarySubscriberDOB',
				'PrimarySubscriberSex' => 'PrimarySubscriberSex',
				'PatientDOB' => 'PatientSex',
				'PatientSex' => 'PatientSex'
			],
			$data,
			'Data not set correctly'
		);
	}

	/**
	 * @covers SunCoastConnection\ClaimsToOEMR\X12N837\Cache::processLoop2010()
	 */
	public function testProcessLoop2010WithSegmentDMGAndInsuranceType2() {
		$mockObjects = $this->setupTestProcessLoop(
			Loop\Loop2010::class,
			[ 'NM1', 'N3', 'N4', 'DMG', 'REF' ],
			Segment\DMG::class
		);

		$mockObjects['segments'][0]->shouldReceive('element')
			->times(3)
			->with('DMG02')
			->andReturn('SubDOB', 'SecondarySubscriberDOB', 'PatientSex');

		$mockObjects['segments'][0]->shouldReceive('element')
			->times(3)
			->with('DMG03')
			->andReturn('SubSex', 'SecondarySubscriberSex', 'PatientSex');

		$data = [
			'CurrentInsuranceType' => 2,
			'SecondarySubscriberRelation' => 'self'
		];

		$this->callProtectedMethod(
			$this->cache,
			'processLoop2010',
			[
				$mockObjects['loop'],
				&$data
			]
		);

		$this->assertEquals(
			[
				'CurrentInsuranceType' => 2,
				'SecondarySubscriberRelation' => 'self',
				'SubDOB' => 'SubDOB',
				'SubSex' => 'SubSex',
				'SecondarySubscriberDOB' => 'SecondarySubscriberDOB',
				'SecondarySubscriberSex' => 'SecondarySubscriberSex',
				'PatientDOB' => 'PatientSex',
				'PatientSex' => 'PatientSex'
			],
			$data,
			'Data not set correctly'
		);
	}

	/**
	 * @covers SunCoastConnection\ClaimsToOEMR\X12N837\Cache::processLoop2010()
	 */
	public function testProcessLoop2010WithSegmentDMGAndInsuranceType3() {
		$mockObjects = $this->setupTestProcessLoop(
			Loop\Loop2010::class,
			[ 'NM1', 'N3', 'N4', 'DMG', 'REF' ],
			Segment\DMG::class
		);

		$mockObjects['segments'][0]->shouldReceive('element')
			->times(3)
			->with('DMG02')
			->andReturn('SubDOB', 'TertiarySubscriberDOB', 'PatientSex');

		$mockObjects['segments'][0]->shouldReceive('element')
			->times(3)
			->with('DMG03')
			->andReturn('SubSex', 'TertiarySubscriberSex', 'PatientSex');

		$data = [
			'CurrentInsuranceType' => 3,
			'TertiarySubscriberRelation' => 'self'
		];

		$this->callProtectedMethod(
			$this->cache,
			'processLoop2010',
			[
				$mockObjects['loop'],
				&$data
			]
		);

		$this->assertEquals(
			[
				'CurrentInsuranceType' => 3,
				'TertiarySubscriberRelation' => 'self',
				'SubDOB' => 'SubDOB',
				'SubSex' => 'SubSex',
				'TertiarySubscriberDOB' => 'TertiarySubscriberDOB',
				'TertiarySubscriberSex' => 'TertiarySubscriberSex',
				'PatientDOB' => 'PatientSex',
				'PatientSex' => 'PatientSex'
			],
			$data,
			'Data not set correctly'
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

		$mockObjects['segments'][0]->shouldReceive('element')
			->once()
			->with('REF02')
			->andReturn('BillingProviderEIN');

		$data = [];

		$this->callProtectedMethod(
			$this->cache,
			'processLoop2010',
			[
				$mockObjects['loop'],
				&$data
			]
		);

		$this->assertEquals(
			[
				'BillingProviderEIN' => 'BillingProviderEIN'
			],
			$data,
			'Data not set correctly'
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

		$mockObjects['segments'][0]->shouldReceive('element')
			->once()
			->with('CLM01')
			->andReturn('ClaimId');

		$mockObjects['segments'][0]->shouldReceive('element')
			->once()
			->with('CLM02')
			->andReturn('ClaimAmount');

		$elementCLM05 = $this->getMockery(
			Element::class
		);

		$mockObjects['segments'][0]->shouldReceive('element')
			->times(3)
			->with('CLM05')
			->andReturn($elementCLM05);

		$elementCLM05->shouldReceive('subElement')
			->once()
			->with(0)
			->andReturn('FacilityCodeValue');

		$elementCLM05->shouldReceive('subElement')
			->once()
			->with(1)
			->andReturn('FacilityCodeQualifier');

		$elementCLM05->shouldReceive('subElement')
			->once()
			->with(2)
			->andReturn('FrequencyTypeCode');

		$mockObjects['segments'][0]->shouldReceive('element')
			->once()
			->with('CLM07')
			->andReturn('ProviderSignatureOnFile');

		$mockObjects['segments'][0]->shouldReceive('elementEquals')
			->once()
			->with('CLM08', 'A')
			->andReturn(true);

		$mockObjects['segments'][0]->shouldReceive('element')
			->once()
			->with('CLM09')
			->andReturn('BenefitIndicator');

		$mockObjects['segments'][0]->shouldReceive('element')
			->once()
			->with('CLM10')
			->andReturn('ReleaseOfInformation');

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

		$this->assertEquals(
			[
				'ClaimId' => 'ClaimId',
				'ClaimAmount' => 'ClaimAmount',
				'FacilityCodeValue' => 'FacilityCodeValue',
				'FacilityCodeQualifier' => 'FacilityCodeQualifier',
				'FrequencyTypeCode' => 'FrequencyTypeCode',
				'ProviderSignatureOnFile' => 'ProviderSignatureOnFile',
				'ProviderAcceptAssignmentCode' => 'true',
				'BenefitIndicator' => 'BenefitIndicator',
				'ReleaseOfInformation' => 'ReleaseOfInformation'
			],
			$data,
			'Data not set correctly'
		);
	}

	/**
	 * @covers SunCoastConnection\ClaimsToOEMR\X12N837\Cache::processLoop2300()
	 */
	public function testProcessLoop2300WithSegmentDTP() {
		$mockObjects = $this->setupTestProcessLoop(
			Loop\Loop2300::class,
			[ 'CLM', 'DTP', 'REF', 'NTE', 'HI' ],
			Segment\DTP::class
		);

		$mockObjects['segments'][0]->shouldReceive('elementEquals')
			->once()
			->with('DTP01', '431')
			->andReturn(true);

		$mockObjects['segments'][0]->shouldReceive('element')
			->once()
			->with('DTP03')
			->andReturn('Dos2');

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

		$this->assertEquals(
			[
				'Dos2' => 'Dos2'
			],
			$data,
			'Data not set correctly'
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

		$mockObjects['segments'][0]->shouldReceive('elementEquals')
			->once()
			->with('REF01', 'EA')
			->andReturn(true);

		$mockObjects['segments'][0]->shouldReceive('element')
			->once()
			->with('REF02')
			->andReturn('MedicalRecordNumber');

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

		$this->assertEquals(
			[
				'MedicalRecordNumber' => 'MedicalRecordNumber'
			],
			$data,
			'Data not set correctly'
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

		$mockObjects['segments'][0]->shouldReceive('elementEquals')
			->once()
			->with('NTE01', 'ADD')
			->andReturn(true);

		$mockObjects['segments'][0]->shouldReceive('element')
			->once()
			->with('NTE02')
			->andReturn('NoteDesc');

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

		$this->assertEquals(
			[
				'NoteDesc' => 'NoteDesc'
			],
			$data,
			'Data not set correctly'
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

		$mockObjects['segments'][0]->shouldReceive('elementExists')
			->twice()
			->with('HI01')
			->andReturn(true);

		$elementHI = [
			'01' => $this->getMockery(
				Element::class
			)
		];

		$mockObjects['segments'][0]->shouldReceive('element')
			->times(4)
			->with('HI01')
			->andReturn($elementHI['01']);

		$elementHI['01']->shouldReceive('subElementEquals')
			->once()
			->with(0, [ 'ABK', 'BK' ])
			->andReturn(true);

		$elementHI['01']->shouldReceive('subElementCount')
			->once()
			->andReturn(2);

		$elementHI['01']->shouldReceive('subElement')
			->once()
			->with(0)
			->andReturn('DxType01');

		$elementHI['01']->shouldReceive('subElement')
			->once()
			->with(1)
			->andReturn('Dx01');

		$elements = [ '02', '03', '04', '05', '06', '07', '08', '09', '10', '11', '12' ];

		$returnData = [
			'DxType' => [ 'DxType01', ],
			'Dx' => [ 'Dx01' ],
			'DxCount' => 1
		];

		foreach($elements as $element) {
			$mockObjects['segments'][0]->shouldReceive('elementExists')
				->once()
				->with('HI'.$element)
				->andReturn(true);

			$elementHI[$element] = $this->getMockery(
				Element::class
			);

			$mockObjects['segments'][0]->shouldReceive('element')
				->times(3)
				->with('HI'.$element)
				->andReturn($elementHI[$element]);

			$elementHI[$element]->shouldReceive('subElementCount')
				->once()
				->andReturn(2);

			$elementHI[$element]->shouldReceive('subElement')
				->once()
				->with(0)
				->andReturn('DxType'.$element);

			$returnData['DxType'][] = 'DxType'.$element;

			$elementHI[$element]->shouldReceive('subElement')
				->once()
				->with(1)
				->andReturn('Dx'.$element);

			$returnData['Dx'][] = 'Dx'.$element;

			++$returnData['DxCount'];
		}

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

		$this->assertEquals(
			$returnData,
			$data,
			'Data not set correctly'
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

		$mockObjects['segments'][0]->shouldReceive('element')
			->once()
			->with('NM102')
			->andReturn('ReferringType');

		$mockObjects['segments'][0]->shouldReceive('element')
			->once()
			->with('NM103')
			->andReturn('ReferringLastName');

		$mockObjects['segments'][0]->shouldReceive('element')
			->once()
			->with('NM104')
			->andReturn('ReferringFirstName');

		$mockObjects['segments'][0]->shouldReceive('element')
			->once()
			->with('NM105')
			->andReturn('ReferringMiddleName');

		$mockObjects['segments'][0]->shouldReceive('element')
			->once()
			->with('NM107')
			->andReturn('ReferringSuffix');

		$mockObjects['segments'][0]->shouldReceive('element')
			->once()
			->with('NM109')
			->andReturn('ReferringId');

		$data = [];

		$this->callProtectedMethod(
			$this->cache,
			'processLoop2310',
			[
				$mockObjects['loop'],
				&$data
			]
		);

		$this->assertEquals(
			[
				'LastNM1' => $mockObjects['segments'][0],
				'ReferringType' => 'ReferringType',
				'ReferringLastName' => 'ReferringLastName',
				'ReferringFirstName' => 'ReferringFirstName',
				'ReferringMiddleName' => 'ReferringMiddleName',
				'ReferringSuffix' => 'ReferringSuffix',
				'ReferringId' => 'ReferringId'
			],
			$data,
			'Data not set correctly'
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

		$mockObjects['segments'][0]->shouldReceive('element')
			->once()
			->with('NM102')
			->andReturn('RenderingType');

		$mockObjects['segments'][0]->shouldReceive('element')
			->once()
			->with('NM103')
			->andReturn('RenderingLastName');

		$mockObjects['segments'][0]->shouldReceive('element')
			->once()
			->with('NM104')
			->andReturn('RenderingFirstName');

		$mockObjects['segments'][0]->shouldReceive('element')
			->once()
			->with('NM105')
			->andReturn('RenderingMiddleName');

		$mockObjects['segments'][0]->shouldReceive('element')
			->once()
			->with('NM107')
			->andReturn('RenderingSuffix');

		$mockObjects['segments'][0]->shouldReceive('element')
			->once()
			->with('NM109')
			->andReturn('RenderingId');

		$data = [];

		$this->callProtectedMethod(
			$this->cache,
			'processLoop2310',
			[
				$mockObjects['loop'],
				&$data
			]
		);

		$this->assertEquals(
			[
				'LastNM1' => $mockObjects['segments'][0],
				'RenderingType' => 'RenderingType',
				'RenderingLastName' => 'RenderingLastName',
				'RenderingFirstName' => 'RenderingFirstName',
				'RenderingMiddleName' => 'RenderingMiddleName',
				'RenderingSuffix' => 'RenderingSuffix',
				'RenderingId' => 'RenderingId'
			],
			$data,
			'Data not set correctly'
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

		$mockObjects['segments'][0]->shouldReceive('element')
			->once()
			->with('NM103')
			->andReturn('ServiceFacilityName');

		$mockObjects['segments'][0]->shouldReceive('element')
			->once()
			->with('NM109')
			->andReturn('ServiceFacilityId');

		$data = [];

		$this->callProtectedMethod(
			$this->cache,
			'processLoop2310',
			[
				$mockObjects['loop'],
				&$data
			]
		);

		$this->assertEquals(
			[
				'LastNM1' => $mockObjects['segments'][0],
				'ServiceFacilityName' => 'ServiceFacilityName',
				'ServiceFacilityId' => 'ServiceFacilityId'
			],
			$data,
			'Data not set correctly'
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

		$mockObjects['segments'][0]->shouldReceive('element')
			->once()
			->with('NM102')
			->andReturn('SupervisingType');

		$mockObjects['segments'][0]->shouldReceive('element')
			->once()
			->with('NM103')
			->andReturn('SupervisingLastName');

		$mockObjects['segments'][0]->shouldReceive('element')
			->once()
			->with('NM104')
			->andReturn('SupervisingFirstName');

		$mockObjects['segments'][0]->shouldReceive('element')
			->once()
			->with('NM105')
			->andReturn('SupervisingMiddleName');

		$mockObjects['segments'][0]->shouldReceive('element')
			->once()
			->with('NM107')
			->andReturn('SupervisingSuffix');

		$mockObjects['segments'][0]->shouldReceive('element')
			->once()
			->with('NM109')
			->andReturn('SupervisingId');

		$data = [];

		$this->callProtectedMethod(
			$this->cache,
			'processLoop2310',
			[
				$mockObjects['loop'],
				&$data
			]
		);

		$this->assertEquals(
			[
				'LastNM1' => $mockObjects['segments'][0],
				'SupervisingType' => 'SupervisingType',
				'SupervisingLastName' => 'SupervisingLastName',
				'SupervisingFirstName' => 'SupervisingFirstName',
				'SupervisingMiddleName' => 'SupervisingMiddleName',
				'SupervisingSuffix' => 'SupervisingSuffix',
				'SupervisingId' => 'SupervisingId'
			],
			$data,
			'Data not set correctly'
		);
	}

	/**
	 * @covers SunCoastConnection\ClaimsToOEMR\X12N837\Cache::processLoop2310()
	 */
	public function testProcessLoop2310WithSegmentN3AndNM101_77() {
		$mockObjects = $this->setupTestProcessLoop(
			Loop\Loop2310::class,
			[ 'NM1', 'N3', 'N4', 'PRV' ],
			Segment\N3::class
		);

		$lastNM1 = $this->getMockery(
			Segment\NM1::class
		);

		$lastNM1->shouldReceive('elementEquals')
			->once()
			->with('NM101', '77')
			->andReturn(true);

		$mockObjects['segments'][0]->shouldReceive('element')
			->once()
			->with('N301')
			->andReturn('ServiceFacilityAddress1');

		$mockObjects['segments'][0]->shouldReceive('element')
			->once()
			->with('N302')
			->andReturn('ServiceFacilityAddress2');

		$data = [
			'LastNM1' => $lastNM1
		];

		$this->callProtectedMethod(
			$this->cache,
			'processLoop2310',
			[
				$mockObjects['loop'],
				&$data
			]
		);

		$this->assertEquals(
			[
				'LastNM1' => $lastNM1,
				'ServiceFacilityAddress1' => 'ServiceFacilityAddress1',
				'ServiceFacilityAddress2' => 'ServiceFacilityAddress2'
			],
			$data,
			'Data not set correctly'
		);
	}

	/**
	 * @covers SunCoastConnection\ClaimsToOEMR\X12N837\Cache::processLoop2310()
	 */
	public function testProcessLoop2310WithSegmentN4AndNM101_77() {
		$mockObjects = $this->setupTestProcessLoop(
			Loop\Loop2310::class,
			[ 'NM1', 'N3', 'N4', 'PRV' ],
			Segment\N4::class
		);

		$lastNM1 = $this->getMockery(
			Segment\NM1::class
		);

		$lastNM1->shouldReceive('elementEquals')
			->once()
			->with('NM101', '77')
			->andReturn(true);

		$mockObjects['segments'][0]->shouldReceive('element')
			->once()
			->with('N401')
			->andReturn('ServiceFacilityCity');

		$mockObjects['segments'][0]->shouldReceive('element')
			->once()
			->with('N402')
			->andReturn('ServiceFacilityState');

		$mockObjects['segments'][0]->shouldReceive('element')
			->once()
			->with('N403')
			->andReturn('ServiceFacilityZip');

		$data = [
			'LastNM1' => $lastNM1
		];

		$this->callProtectedMethod(
			$this->cache,
			'processLoop2310',
			[
				$mockObjects['loop'],
				&$data
			]
		);

		$this->assertEquals(
			[
				'LastNM1' => $lastNM1,
				'ServiceFacilityCity' => 'ServiceFacilityCity',
				'ServiceFacilityState' => 'ServiceFacilityState',
				'ServiceFacilityZip' => 'ServiceFacilityZip'
			],
			$data,
			'Data not set correctly'
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

		$mockObjects['segments'][0]->shouldReceive('elementEquals')
			->once()
			->with('PRV01', 'PE')
			->andReturn(true);

		$mockObjects['segments'][0]->shouldReceive('element')
			->once()
			->with('PRV03')
			->andReturn('RenderingTaxonomy');

		$data = [];

		$this->callProtectedMethod(
			$this->cache,
			'processLoop2310',
			[
				$mockObjects['loop'],
				&$data
			]
		);

		$this->assertEquals(
			[
				'RenderingTaxonomy' => 'RenderingTaxonomy'
			],
			$data,
			'Data not set correctly'
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
	public function testProcessLoop2320WithSegmentSBR01_P() {
		$mockObjects = $this->setupTestProcessLoop(
			Loop\Loop2320::class,
			[ 'SBR' ],
			Segment\SBR::class
		);

		$mockObjects['segments'][0]->shouldReceive('element')
			->once()
			->with('SBR01')
			->andReturn('P');

		$mockObjects['segments'][0]->shouldReceive('elementEquals')
			->once()
			->with('SBR02', '18')
			->andReturn(true);

		$mockObjects['segments'][0]->shouldReceive('element')
			->once()
			->with('SBR03')
			->andReturn('PrimaryPolicy');

		$mockObjects['segments'][0]->shouldReceive('element')
			->once()
			->with('SBR04')
			->andReturn('PrimaryPlanName');

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

		$this->assertEquals(
			[
				'CurrentInsuranceType' => 1,
				'PrimarySubscriberRelation' => 'self',
				'PrimaryPolicy' => 'PrimaryPolicy',
				'PrimaryPlanName' => 'PrimaryPlanName'
			],
			$data,
			'Data not set correctly'
		);
	}

	/**
	 * @covers SunCoastConnection\ClaimsToOEMR\X12N837\Cache::processLoop2320()
	 */
	public function testProcessLoop2320WithSegmentSBR01_S() {
		$mockObjects = $this->setupTestProcessLoop(
			Loop\Loop2320::class,
			[ 'SBR' ],
			Segment\SBR::class
		);

		$mockObjects['segments'][0]->shouldReceive('element')
			->once()
			->with('SBR01')
			->andReturn('S');

		$mockObjects['segments'][0]->shouldReceive('elementEquals')
			->once()
			->with('SBR02', '18')
			->andReturn(true);

		$mockObjects['segments'][0]->shouldReceive('element')
			->once()
			->with('SBR03')
			->andReturn('SecondaryPolicy');

		$mockObjects['segments'][0]->shouldReceive('element')
			->once()
			->with('SBR04')
			->andReturn('SecondaryPlanName');

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

		$this->assertEquals(
			[
				'CurrentInsuranceType' => 2,
				'SecondarySubscriberRelation' => 'self',
				'SecondaryPolicy' => 'SecondaryPolicy',
				'SecondaryPlanName' => 'SecondaryPlanName'
			],
			$data,
			'Data not set correctly'
		);
	}

	/**
	 * @covers SunCoastConnection\ClaimsToOEMR\X12N837\Cache::processLoop2320()
	 */
	public function testProcessLoop2320WithSegmentSBR01_T() {
		$mockObjects = $this->setupTestProcessLoop(
			Loop\Loop2320::class,
			[ 'SBR' ],
			Segment\SBR::class
		);

		$mockObjects['segments'][0]->shouldReceive('element')
			->once()
			->with('SBR01')
			->andReturn('T');

		$mockObjects['segments'][0]->shouldReceive('elementEquals')
			->once()
			->with('SBR02', '18')
			->andReturn(true);

		$mockObjects['segments'][0]->shouldReceive('element')
			->once()
			->with('SBR03')
			->andReturn('TertiaryPolicy');

		$mockObjects['segments'][0]->shouldReceive('element')
			->once()
			->with('SBR04')
			->andReturn('TertiaryPlanName');

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

		$this->assertEquals(
			[
				'CurrentInsuranceType' => 3,
				'TertiarySubscriberRelation' => 'self',
				'TertiaryPolicy' => 'TertiaryPolicy',
				'TertiaryPlanName' => 'TertiaryPlanName'
			],
			$data,
			'Data not set correctly'
		);
	}

	/**
	 * @covers SunCoastConnection\ClaimsToOEMR\X12N837\Cache::processLoop2330()
	 */
	public function testProcessLoop2330WithSegmentNM101_ILAndNoInsuranceType() {
		$mockObjects = $this->setupTestProcessLoop(
			Loop\Loop2330::class,
			[ 'NM1', 'N3', 'N4', 'REF' ],
			Segment\NM1::class
		);

		$mockObjects['segments'][0]->shouldReceive('element')
			->once()
			->with('NM101')
			->andReturn('IL');

		$data = [];

		$this->expectOutputRegex('/^Missing: CurrentInsuranceType \[.+:\d+\]$/');

		$this->callProtectedMethod(
			$this->cache,
			'processLoop2330',
			[
				$mockObjects['loop'],
				&$data
			]
		);

		$this->assertEquals(
			[
				'LastNM1' => $mockObjects['segments'][0],
			],
			$data,
			'Data not set correctly'
		);
	}

	/**
	 * @covers SunCoastConnection\ClaimsToOEMR\X12N837\Cache::processLoop2330()
	 */
	public function testProcessLoop2330WithSegmentNM101_ILAndInsuranceType1() {
		$mockObjects = $this->setupTestProcessLoop(
			Loop\Loop2330::class,
			[ 'NM1', 'N3', 'N4', 'REF' ],
			Segment\NM1::class
		);

		$mockObjects['segments'][0]->shouldReceive('element')
			->once()
			->with('NM101')
			->andReturn('IL');

		$mockObjects['segments'][0]->shouldReceive('element')
			->twice()
			->with('NM103')
			->andReturn('PrimarySubscriberLastName', 'PatientLastName');

		$mockObjects['segments'][0]->shouldReceive('element')
			->twice()
			->with('NM104')
			->andReturn('PrimarySubscriberFirstName', 'PatientFirstName');

		$mockObjects['segments'][0]->shouldReceive('element')
			->twice()
			->with('NM105')
			->andReturn('PrimarySubscriberMiddleName', 'PatientMiddleName');

		$mockObjects['segments'][0]->shouldReceive('element')
			->twice()
			->with('NM107')
			->andReturn('PrimarySubscriberSuffix', 'PatientSuffix');

		$mockObjects['segments'][0]->shouldReceive('element')
			->twice()
			->with('NM109')
			->andReturn('PrimarySubscriberId', 'PatientId');

		$data = [
			'CurrentInsuranceType' => 1,
			'PrimarySubscriberRelation' => 'self'
		];

		$this->callProtectedMethod(
			$this->cache,
			'processLoop2330',
			[
				$mockObjects['loop'],
				&$data
			]
		);

		$this->assertEquals(
			[
				'CurrentInsuranceType' => 1,
				'PrimarySubscriberRelation' => 'self',
				'LastNM1' => $mockObjects['segments'][0],
				'PrimarySubscriberLastName' => 'PrimarySubscriberLastName',
				'PrimarySubscriberFirstName' => 'PrimarySubscriberFirstName',
				'PrimarySubscriberMiddleName' => 'PrimarySubscriberMiddleName',
				'PrimarySubscriberSuffix' => 'PrimarySubscriberSuffix',
				'PrimarySubscriberId' => 'PrimarySubscriberId',
				'PatientLastName' => 'PatientLastName',
				'PatientFirstName' => 'PatientFirstName',
				'PatientMiddleName' => 'PatientMiddleName',
				'PatientSuffix' => 'PatientSuffix',
				'PatientId' => 'PatientId'
			],
			$data,
			'Data not set correctly'
		);
	}

	/**
	 * @covers SunCoastConnection\ClaimsToOEMR\X12N837\Cache::processLoop2330()
	 */
	public function testProcessLoop2330WithSegmentNM101_ILAndInsuranceType2() {
		$mockObjects = $this->setupTestProcessLoop(
			Loop\Loop2330::class,
			[ 'NM1', 'N3', 'N4', 'REF' ],
			Segment\NM1::class
		);

		$mockObjects['segments'][0]->shouldReceive('element')
			->once()
			->with('NM101')
			->andReturn('IL');

		$mockObjects['segments'][0]->shouldReceive('element')
			->twice()
			->with('NM103')
			->andReturn('SecondarySubscriberLastName', 'PatientLastName');

		$mockObjects['segments'][0]->shouldReceive('element')
			->twice()
			->with('NM104')
			->andReturn('SecondarySubscriberFirstName', 'PatientFirstName');

		$mockObjects['segments'][0]->shouldReceive('element')
			->twice()
			->with('NM105')
			->andReturn('SecondarySubscriberMiddleName', 'PatientMiddleName');

		$mockObjects['segments'][0]->shouldReceive('element')
			->twice()
			->with('NM107')
			->andReturn('SecondarySubscriberSuffix', 'PatientSuffix');

		$mockObjects['segments'][0]->shouldReceive('element')
			->twice()
			->with('NM109')
			->andReturn('SecondarySubscriberId', 'PatientId');

		$data = [
			'CurrentInsuranceType' => 2,
			'SecondarySubscriberRelation' => 'self'
		];

		$this->callProtectedMethod(
			$this->cache,
			'processLoop2330',
			[
				$mockObjects['loop'],
				&$data
			]
		);

		$this->assertEquals(
			[
				'CurrentInsuranceType' => 2,
				'SecondarySubscriberRelation' => 'self',
				'LastNM1' => $mockObjects['segments'][0],
				'SecondarySubscriberLastName' => 'SecondarySubscriberLastName',
				'SecondarySubscriberFirstName' => 'SecondarySubscriberFirstName',
				'SecondarySubscriberMiddleName' => 'SecondarySubscriberMiddleName',
				'SecondarySubscriberSuffix' => 'SecondarySubscriberSuffix',
				'SecondarySubscriberId' => 'SecondarySubscriberId',
				'PatientLastName' => 'PatientLastName',
				'PatientFirstName' => 'PatientFirstName',
				'PatientMiddleName' => 'PatientMiddleName',
				'PatientSuffix' => 'PatientSuffix',
				'PatientId' => 'PatientId'
			],
			$data,
			'Data not set correctly'
		);
	}

	/**
	 * @covers SunCoastConnection\ClaimsToOEMR\X12N837\Cache::processLoop2330()
	 */
	public function testProcessLoop2330WithSegmentNM101_ILAndInsuranceType3() {
		$mockObjects = $this->setupTestProcessLoop(
			Loop\Loop2330::class,
			[ 'NM1', 'N3', 'N4', 'REF' ],
			Segment\NM1::class
		);

		$mockObjects['segments'][0]->shouldReceive('element')
			->once()
			->with('NM101')
			->andReturn('IL');

		$mockObjects['segments'][0]->shouldReceive('element')
			->twice()
			->with('NM103')
			->andReturn('TertiarySubscriberLastName', 'PatientLastName');

		$mockObjects['segments'][0]->shouldReceive('element')
			->twice()
			->with('NM104')
			->andReturn('TertiarySubscriberFirstName', 'PatientFirstName');

		$mockObjects['segments'][0]->shouldReceive('element')
			->twice()
			->with('NM105')
			->andReturn('TertiarySubscriberMiddleName', 'PatientMiddleName');

		$mockObjects['segments'][0]->shouldReceive('element')
			->twice()
			->with('NM107')
			->andReturn('TertiarySubscriberSuffix', 'PatientSuffix');

		$mockObjects['segments'][0]->shouldReceive('element')
			->twice()
			->with('NM109')
			->andReturn('TertiarySubscriberId', 'PatientId');

		$data = [
			'CurrentInsuranceType' => 3,
			'TertiarySubscriberRelation' => 'self'
		];

		$this->callProtectedMethod(
			$this->cache,
			'processLoop2330',
			[
				$mockObjects['loop'],
				&$data
			]
		);

		$this->assertEquals(
			[
				'CurrentInsuranceType' => 3,
				'TertiarySubscriberRelation' => 'self',
				'LastNM1' => $mockObjects['segments'][0],
				'TertiarySubscriberLastName' => 'TertiarySubscriberLastName',
				'TertiarySubscriberFirstName' => 'TertiarySubscriberFirstName',
				'TertiarySubscriberMiddleName' => 'TertiarySubscriberMiddleName',
				'TertiarySubscriberSuffix' => 'TertiarySubscriberSuffix',
				'TertiarySubscriberId' => 'TertiarySubscriberId',
				'PatientLastName' => 'PatientLastName',
				'PatientFirstName' => 'PatientFirstName',
				'PatientMiddleName' => 'PatientMiddleName',
				'PatientSuffix' => 'PatientSuffix',
				'PatientId' => 'PatientId'
			],
			$data,
			'Data not set correctly'
		);
	}

	/**
	 * @covers SunCoastConnection\ClaimsToOEMR\X12N837\Cache::processLoop2330()
	 */
	public function testProcessLoop2330WithSegmentNM101_PRAndNoInsuranceType() {
		$mockObjects = $this->setupTestProcessLoop(
			Loop\Loop2330::class,
			[ 'NM1', 'N3', 'N4', 'REF' ],
			Segment\NM1::class
		);

		$mockObjects['segments'][0]->shouldReceive('element')
			->once()
			->with('NM101')
			->andReturn('PR');

		$data = [];

		$this->expectOutputRegex('/^Missing: CurrentInsuranceType \[.+:\d+\]$/');

		$this->callProtectedMethod(
			$this->cache,
			'processLoop2330',
			[
				$mockObjects['loop'],
				&$data
			]
		);

		$this->assertEquals(
			[
				'LastNM1' => $mockObjects['segments'][0],
			],
			$data,
			'Data not set correctly'
		);
	}

	/**
	 * @covers SunCoastConnection\ClaimsToOEMR\X12N837\Cache::processLoop2330()
	 */
	public function testProcessLoop2330WithSegmentNM101_PRAndInsuranceType1() {
		$mockObjects = $this->setupTestProcessLoop(
			Loop\Loop2330::class,
			[ 'NM1', 'N3', 'N4', 'REF' ],
			Segment\NM1::class
		);

		$mockObjects['segments'][0]->shouldReceive('element')
			->once()
			->with('NM101')
			->andReturn('PR');

		$mockObjects['segments'][0]->shouldReceive('element')
			->once()
			->with('NM103')
			->andReturn('PrimaryPayerName');

		$mockObjects['segments'][0]->shouldReceive('element')
			->once()
			->with('NM109')
			->andReturn('PrimaryPayerId');

		$data = [
			'CurrentInsuranceType' => 1
		];

		$this->callProtectedMethod(
			$this->cache,
			'processLoop2330',
			[
				$mockObjects['loop'],
				&$data
			]
		);

		$this->assertEquals(
			[
				'CurrentInsuranceType' => 1,
				'LastNM1' => $mockObjects['segments'][0],
				'PrimaryPayerName' => 'PrimaryPayerName',
				'PrimaryPayerId' => 'PrimaryPayerId'
			],
			$data,
			'Data not set correctly'
		);
	}

	/**
	 * @covers SunCoastConnection\ClaimsToOEMR\X12N837\Cache::processLoop2330()
	 */
	public function testProcessLoop2330WithSegmentNM101_PRAndInsuranceType2() {
		$mockObjects = $this->setupTestProcessLoop(
			Loop\Loop2330::class,
			[ 'NM1', 'N3', 'N4', 'REF' ],
			Segment\NM1::class
		);

		$mockObjects['segments'][0]->shouldReceive('element')
			->once()
			->with('NM101')
			->andReturn('PR');

		$mockObjects['segments'][0]->shouldReceive('element')
			->once()
			->with('NM103')
			->andReturn('SecondaryPayerName');

		$mockObjects['segments'][0]->shouldReceive('element')
			->once()
			->with('NM109')
			->andReturn('SecondaryPayerId');

		$data = [
			'CurrentInsuranceType' => 2
		];

		$this->callProtectedMethod(
			$this->cache,
			'processLoop2330',
			[
				$mockObjects['loop'],
				&$data
			]
		);

		$this->assertEquals(
			[
				'CurrentInsuranceType' => 2,
				'LastNM1' => $mockObjects['segments'][0],
				'SecondaryPayerName' => 'SecondaryPayerName',
				'SecondaryPayerId' => 'SecondaryPayerId'
			],
			$data,
			'Data not set correctly'
		);
	}

	/**
	 * @covers SunCoastConnection\ClaimsToOEMR\X12N837\Cache::processLoop2330()
	 */
	public function testProcessLoop2330WithSegmentNM101_PRAndInsuranceType3() {
		$mockObjects = $this->setupTestProcessLoop(
			Loop\Loop2330::class,
			[ 'NM1', 'N3', 'N4', 'REF' ],
			Segment\NM1::class
		);

		$mockObjects['segments'][0]->shouldReceive('element')
			->once()
			->with('NM101')
			->andReturn('PR');

		$mockObjects['segments'][0]->shouldReceive('element')
			->once()
			->with('NM103')
			->andReturn('TertiaryPayerName');

		$mockObjects['segments'][0]->shouldReceive('element')
			->once()
			->with('NM109')
			->andReturn('TertiaryPayerId');

		$data = [
			'CurrentInsuranceType' => 3
		];

		$this->callProtectedMethod(
			$this->cache,
			'processLoop2330',
			[
				$mockObjects['loop'],
				&$data
			]
		);

		$this->assertEquals(
			[
				'CurrentInsuranceType' => 3,
				'LastNM1' => $mockObjects['segments'][0],
				'TertiaryPayerName' => 'TertiaryPayerName',
				'TertiaryPayerId' => 'TertiaryPayerId'
			],
			$data,
			'Data not set correctly'
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

		$mockObjects['segments'][0]->shouldReceive('element')
			->once()
			->with('NM102')
			->andReturn('ReferringType');

		$mockObjects['segments'][0]->shouldReceive('element')
			->once()
			->with('NM103')
			->andReturn('ReferringLastName');

		$mockObjects['segments'][0]->shouldReceive('element')
			->once()
			->with('NM104')
			->andReturn('ReferringFirstName');

		$mockObjects['segments'][0]->shouldReceive('element')
			->once()
			->with('NM105')
			->andReturn('ReferringMiddleName');

		$mockObjects['segments'][0]->shouldReceive('element')
			->once()
			->with('NM107')
			->andReturn('ReferringSuffix');

		$mockObjects['segments'][0]->shouldReceive('element')
			->once()
			->with('NM109')
			->andReturn('ReferringId');

		$data = [];

		$this->callProtectedMethod(
			$this->cache,
			'processLoop2330',
			[
				$mockObjects['loop'],
				&$data
			]
		);

		$this->assertEquals(
			[
				'LastNM1' => $mockObjects['segments'][0],
				'ReferringType' => 'ReferringType',
				'ReferringLastName' => 'ReferringLastName',
				'ReferringFirstName' => 'ReferringFirstName',
				'ReferringMiddleName' => 'ReferringMiddleName',
				'ReferringSuffix' => 'ReferringSuffix',
				'ReferringId' => 'ReferringId'
			],
			$data,
			'Data not set correctly'
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

		$mockObjects['segments'][0]->shouldReceive('element')
			->once()
			->with('NM102')
			->andReturn('RenderingType');

		$mockObjects['segments'][0]->shouldReceive('element')
			->once()
			->with('NM103')
			->andReturn('RenderingLastName');

		$mockObjects['segments'][0]->shouldReceive('element')
			->once()
			->with('NM104')
			->andReturn('RenderingFirstName');

		$mockObjects['segments'][0]->shouldReceive('element')
			->once()
			->with('NM105')
			->andReturn('RenderingMiddleName');

		$mockObjects['segments'][0]->shouldReceive('element')
			->once()
			->with('NM107')
			->andReturn('RenderingSuffix');

		$mockObjects['segments'][0]->shouldReceive('element')
			->once()
			->with('NM109')
			->andReturn('RenderingId');

		$data = [];

		$this->callProtectedMethod(
			$this->cache,
			'processLoop2330',
			[
				$mockObjects['loop'],
				&$data
			]
		);

		$this->assertEquals(
			[
				'LastNM1' => $mockObjects['segments'][0],
				'RenderingType' => 'RenderingType',
				'RenderingLastName' => 'RenderingLastName',
				'RenderingFirstName' => 'RenderingFirstName',
				'RenderingMiddleName' => 'RenderingMiddleName',
				'RenderingSuffix' => 'RenderingSuffix',
				'RenderingId' => 'RenderingId'
			],
			$data,
			'Data not set correctly'
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

		$mockObjects['segments'][0]->shouldReceive('element')
			->once()
			->with('NM103')
			->andReturn('ServiceFacilityName');

		$mockObjects['segments'][0]->shouldReceive('element')
			->once()
			->with('NM109')
			->andReturn('ServiceFacilityId');

		$data = [];

		$this->callProtectedMethod(
			$this->cache,
			'processLoop2330',
			[
				$mockObjects['loop'],
				&$data
			]
		);

		$this->assertEquals(
			[
				'LastNM1' => $mockObjects['segments'][0],
				'ServiceFacilityName' => 'ServiceFacilityName',
				'ServiceFacilityId' => 'ServiceFacilityId'
			],
			$data,
			'Data not set correctly'
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

		$mockObjects['segments'][0]->shouldReceive('element')
			->once()
			->with('NM102')
			->andReturn('SupervisingType');

		$mockObjects['segments'][0]->shouldReceive('element')
			->once()
			->with('NM103')
			->andReturn('SupervisingLastName');

		$mockObjects['segments'][0]->shouldReceive('element')
			->once()
			->with('NM104')
			->andReturn('SupervisingFirstName');

		$mockObjects['segments'][0]->shouldReceive('element')
			->once()
			->with('NM105')
			->andReturn('SupervisingMiddleName');

		$mockObjects['segments'][0]->shouldReceive('element')
			->once()
			->with('NM107')
			->andReturn('SupervisingSuffix');

		$mockObjects['segments'][0]->shouldReceive('element')
			->once()
			->with('NM109')
			->andReturn('SupervisingId');

		$data = [];

		$this->callProtectedMethod(
			$this->cache,
			'processLoop2330',
			[
				$mockObjects['loop'],
				&$data
			]
		);

		$this->assertEquals(
			[
				'LastNM1' => $mockObjects['segments'][0],
				'SupervisingType' => 'SupervisingType',
				'SupervisingLastName' => 'SupervisingLastName',
				'SupervisingFirstName' => 'SupervisingFirstName',
				'SupervisingMiddleName' => 'SupervisingMiddleName',
				'SupervisingSuffix' => 'SupervisingSuffix',
				'SupervisingId' => 'SupervisingId'
			],
			$data,
			'Data not set correctly'
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

		$mockObjects['segments'][0]->shouldReceive('element')
			->once()
			->with('NM102')
			->andReturn('BillingType');

		$mockObjects['segments'][0]->shouldReceive('element')
			->once()
			->with('NM103')
			->andReturn('BillingProviderLastName');

		$mockObjects['segments'][0]->shouldReceive('element')
			->once()
			->with('NM104')
			->andReturn('BillingProviderFirstName');

		$mockObjects['segments'][0]->shouldReceive('element')
			->once()
			->with('NM105')
			->andReturn('BillingProviderMiddleName');

		$mockObjects['segments'][0]->shouldReceive('element')
			->once()
			->with('NM107')
			->andReturn('BillingProviderSuffix');

		$mockObjects['segments'][0]->shouldReceive('element')
			->once()
			->with('NM109')
			->andReturn('BillingProviderId');

		$data = [];

		$this->callProtectedMethod(
			$this->cache,
			'processLoop2330',
			[
				$mockObjects['loop'],
				&$data
			]
		);

		$this->assertEquals(
			[
				'LastNM1' => $mockObjects['segments'][0],
				'BillingType' => 'BillingType',
				'BillingProviderLastName' => 'BillingProviderLastName',
				'BillingProviderFirstName' => 'BillingProviderFirstName',
				'BillingProviderMiddleName' => 'BillingProviderMiddleName',
				'BillingProviderSuffix' => 'BillingProviderSuffix',
				'BillingProviderId' => 'BillingProviderId'
			],
			$data,
			'Data not set correctly'
		);
	}

	/**
	 * @covers SunCoastConnection\ClaimsToOEMR\X12N837\Cache::processLoop2330()
	 */
	public function testProcessLoop2330WithSegmentN3AndNM101_ILAndNoInsuranceType() {
		$mockObjects = $this->setupTestProcessLoop(
			Loop\Loop2330::class,
			[ 'NM1', 'N3', 'N4', 'REF' ],
			Segment\N3::class
		);

		$lastNM1 = $this->getMockery(
			Segment\NM1::class
		);

		$lastNM1->shouldReceive('element')
			->once()
			->with('NM101')
			->andReturn('IL');

		$data = [
			'LastNM1' => $lastNM1
		];

		$this->expectOutputRegex('/^Missing: CurrentInsuranceType \[.+:\d+\]$/');

		$this->callProtectedMethod(
			$this->cache,
			'processLoop2330',
			[
				$mockObjects['loop'],
				&$data
			]
		);

		$this->assertEquals(
			[
				'LastNM1' => $lastNM1,
			],
			$data,
			'Data not set correctly'
		);
	}

	/**
	 * @covers SunCoastConnection\ClaimsToOEMR\X12N837\Cache::processLoop2330()
	 */
	public function testProcessLoop2330WithSegmentN3AndNM101_ILAndInsuranceType2() {
		$mockObjects = $this->setupTestProcessLoop(
			Loop\Loop2330::class,
			[ 'NM1', 'N3', 'N4', 'REF' ],
			Segment\N3::class
		);

		$lastNM1 = $this->getMockery(
			Segment\NM1::class
		);

		$lastNM1->shouldReceive('element')
			->once()
			->with('NM101')
			->andReturn('IL');

		$mockObjects['segments'][0]->shouldReceive('element')
			->twice()
			->with('N301')
			->andReturn('SecondarySubscriberAddress1', 'PatientAddress1');

		$mockObjects['segments'][0]->shouldReceive('element')
			->twice()
			->with('N302')
			->andReturn('SecondarySubscriberAddress2', 'PatientAddress2');

		$data = [
			'LastNM1' => $lastNM1,
			'CurrentInsuranceType' => 2,
			'SecondarySubscriberRelation' => 'self'
		];

		$this->callProtectedMethod(
			$this->cache,
			'processLoop2330',
			[
				$mockObjects['loop'],
				&$data
			]
		);

		$this->assertEquals(
			[
				'LastNM1' => $lastNM1,
				'CurrentInsuranceType' => 2,
				'SecondarySubscriberRelation' => 'self',
				'SecondarySubscriberAddress1' => 'SecondarySubscriberAddress1',
				'SecondarySubscriberAddress2' => 'SecondarySubscriberAddress2',
				'PatientAddress1' => 'PatientAddress1',
				'PatientAddress2' => 'PatientAddress2'
			],
			$data,
			'Data not set correctly'
		);
	}

	/**
	 * @covers SunCoastConnection\ClaimsToOEMR\X12N837\Cache::processLoop2330()
	 */
	public function testProcessLoop2330WithSegmentN3AndNM101_ILAndInsuranceType3() {
		$mockObjects = $this->setupTestProcessLoop(
			Loop\Loop2330::class,
			[ 'NM1', 'N3', 'N4', 'REF' ],
			Segment\N3::class
		);

		$lastNM1 = $this->getMockery(
			Segment\NM1::class
		);

		$lastNM1->shouldReceive('element')
			->once()
			->with('NM101')
			->andReturn('IL');

		$mockObjects['segments'][0]->shouldReceive('element')
			->twice()
			->with('N301')
			->andReturn('TertiarySubscriberAddress1', 'PatientAddress1');

		$mockObjects['segments'][0]->shouldReceive('element')
			->twice()
			->with('N302')
			->andReturn('TertiarySubscriberAddress2', 'PatientAddress2');

		$data = [
			'LastNM1' => $lastNM1,
			'CurrentInsuranceType' => 3,
			'TertiarySubscriberRelation' => 'self'
		];

		$this->callProtectedMethod(
			$this->cache,
			'processLoop2330',
			[
				$mockObjects['loop'],
				&$data
			]
		);

		$this->assertEquals(
			[
				'LastNM1' => $lastNM1,
				'CurrentInsuranceType' => 3,
				'TertiarySubscriberRelation' => 'self',
				'TertiarySubscriberAddress1' => 'TertiarySubscriberAddress1',
				'TertiarySubscriberAddress2' => 'TertiarySubscriberAddress2',
				'PatientAddress1' => 'PatientAddress1',
				'PatientAddress2' => 'PatientAddress2'
			],
			$data,
			'Data not set correctly'
		);
	}

	/**
	 * @covers SunCoastConnection\ClaimsToOEMR\X12N837\Cache::processLoop2330()
	 */
	public function testProcessLoop2330WithSegmentN3AndNM101_PRAndNoInsuranceType() {
		$mockObjects = $this->setupTestProcessLoop(
			Loop\Loop2330::class,
			[ 'NM1', 'N3', 'N4', 'REF' ],
			Segment\N3::class
		);

		$lastNM1 = $this->getMockery(
			Segment\NM1::class
		);

		$lastNM1->shouldReceive('element')
			->once()
			->with('NM101')
			->andReturn('PR');

		$data = [
			'LastNM1' => $lastNM1
		];

		$this->expectOutputRegex('/^Missing: CurrentInsuranceType \[.+:\d+\]$/');

		$this->callProtectedMethod(
			$this->cache,
			'processLoop2330',
			[
				$mockObjects['loop'],
				&$data
			]
		);

		$this->assertEquals(
			[
				'LastNM1' => $lastNM1,
			],
			$data,
			'Data not set correctly'
		);
	}

	/**
	 * @covers SunCoastConnection\ClaimsToOEMR\X12N837\Cache::processLoop2330()
	 */
	public function testProcessLoop2330WithSegmentN3AndNM101_PRAndInsuranceType1() {
		$mockObjects = $this->setupTestProcessLoop(
			Loop\Loop2330::class,
			[ 'NM1', 'N3', 'N4', 'REF' ],
			Segment\N3::class
		);

		$lastNM1 = $this->getMockery(
			Segment\NM1::class
		);

		$lastNM1->shouldReceive('element')
			->once()
			->with('NM101')
			->andReturn('PR');

		$mockObjects['segments'][0]->shouldReceive('element')
			->once()
			->with('N301')
			->andReturn('PrimaryPayerAddress1');

		$mockObjects['segments'][0]->shouldReceive('element')
			->once()
			->with('N302')
			->andReturn('PrimaryPayerAddress2');

		$data = [
			'LastNM1' => $lastNM1,
			'CurrentInsuranceType' => 1,
		];

		$this->callProtectedMethod(
			$this->cache,
			'processLoop2330',
			[
				$mockObjects['loop'],
				&$data
			]
		);

		$this->assertEquals(
			[
				'LastNM1' => $lastNM1,
				'CurrentInsuranceType' => 1,
				'PrimaryPayerAddress1' => 'PrimaryPayerAddress1',
				'PrimaryPayerAddress2' => 'PrimaryPayerAddress2'
			],
			$data,
			'Data not set correctly'
		);
	}

	/**
	 * @covers SunCoastConnection\ClaimsToOEMR\X12N837\Cache::processLoop2330()
	 */
	public function testProcessLoop2330WithSegmentN3AndNM101_PRAndInsuranceType2() {
		$mockObjects = $this->setupTestProcessLoop(
			Loop\Loop2330::class,
			[ 'NM1', 'N3', 'N4', 'REF' ],
			Segment\N3::class
		);

		$lastNM1 = $this->getMockery(
			Segment\NM1::class
		);

		$lastNM1->shouldReceive('element')
			->once()
			->with('NM101')
			->andReturn('PR');

		$mockObjects['segments'][0]->shouldReceive('element')
			->once()
			->with('N301')
			->andReturn('SecondaryPayerAddress1');

		$mockObjects['segments'][0]->shouldReceive('element')
			->once()
			->with('N302')
			->andReturn('SecondaryPayerAddress2');

		$data = [
			'LastNM1' => $lastNM1,
			'CurrentInsuranceType' => 2,
		];

		$this->callProtectedMethod(
			$this->cache,
			'processLoop2330',
			[
				$mockObjects['loop'],
				&$data
			]
		);

		$this->assertEquals(
			[
				'LastNM1' => $lastNM1,
				'CurrentInsuranceType' => 2,
				'SecondaryPayerAddress1' => 'SecondaryPayerAddress1',
				'SecondaryPayerAddress2' => 'SecondaryPayerAddress2'
			],
			$data,
			'Data not set correctly'
		);
	}

	/**
	 * @covers SunCoastConnection\ClaimsToOEMR\X12N837\Cache::processLoop2330()
	 */
	public function testProcessLoop2330WithSegmentN3AndNM101_PRAndInsuranceType3() {
		$mockObjects = $this->setupTestProcessLoop(
			Loop\Loop2330::class,
			[ 'NM1', 'N3', 'N4', 'REF' ],
			Segment\N3::class
		);

		$lastNM1 = $this->getMockery(
			Segment\NM1::class
		);

		$lastNM1->shouldReceive('element')
			->once()
			->with('NM101')
			->andReturn('PR');

		$mockObjects['segments'][0]->shouldReceive('element')
			->once()
			->with('N301')
			->andReturn('TertiaryPayerAddress1');

		$mockObjects['segments'][0]->shouldReceive('element')
			->once()
			->with('N302')
			->andReturn('TertiaryPayerAddress2');

		$data = [
			'LastNM1' => $lastNM1,
			'CurrentInsuranceType' => 3,
		];

		$this->callProtectedMethod(
			$this->cache,
			'processLoop2330',
			[
				$mockObjects['loop'],
				&$data
			]
		);

		$this->assertEquals(
			[
				'LastNM1' => $lastNM1,
				'CurrentInsuranceType' => 3,
				'TertiaryPayerAddress1' => 'TertiaryPayerAddress1',
				'TertiaryPayerAddress2' => 'TertiaryPayerAddress2'
			],
			$data,
			'Data not set correctly'
		);
	}

	/**
	 * @covers SunCoastConnection\ClaimsToOEMR\X12N837\Cache::processLoop2330()
	 */
	public function testProcessLoop2330WithSegmentN3AndNM101_77() {
		$mockObjects = $this->setupTestProcessLoop(
			Loop\Loop2330::class,
			[ 'NM1', 'N3', 'N4', 'REF' ],
			Segment\N3::class
		);

		$lastNM1 = $this->getMockery(
			Segment\NM1::class
		);

		$lastNM1->shouldReceive('element')
			->once()
			->with('NM101')
			->andReturn('77');

		$mockObjects['segments'][0]->shouldReceive('element')
			->once()
			->with('N301')
			->andReturn('ServiceFacilityAddress1');

		$mockObjects['segments'][0]->shouldReceive('element')
			->once()
			->with('N302')
			->andReturn('ServiceFacilityAddress2');

		$data = [
			'LastNM1' => $lastNM1
		];

		$this->callProtectedMethod(
			$this->cache,
			'processLoop2330',
			[
				$mockObjects['loop'],
				&$data
			]
		);

		$this->assertEquals(
			[
				'LastNM1' => $lastNM1,
				'ServiceFacilityAddress1' => 'ServiceFacilityAddress1',
				'ServiceFacilityAddress2' => 'ServiceFacilityAddress2',
			],
			$data,
			'Data not set correctly'
		);
	}

	/**
	 * @covers SunCoastConnection\ClaimsToOEMR\X12N837\Cache::processLoop2330()
	 */
	public function testProcessLoop2330WithSegmentN3AndNM101_85() {
		$mockObjects = $this->setupTestProcessLoop(
			Loop\Loop2330::class,
			[ 'NM1', 'N3', 'N4', 'REF' ],
			Segment\N3::class
		);

		$lastNM1 = $this->getMockery(
			Segment\NM1::class
		);

		$lastNM1->shouldReceive('element')
			->once()
			->with('NM101')
			->andReturn('85');

		$mockObjects['segments'][0]->shouldReceive('element')
			->once()
			->with('N301')
			->andReturn('BillingProviderAddress1');

		$mockObjects['segments'][0]->shouldReceive('element')
			->once()
			->with('N302')
			->andReturn('BillingProviderAddress2');

		$data = [
			'LastNM1' => $lastNM1
		];

		$this->callProtectedMethod(
			$this->cache,
			'processLoop2330',
			[
				$mockObjects['loop'],
				&$data
			]
		);

		$this->assertEquals(
			[
				'LastNM1' => $lastNM1,
				'BillingProviderAddress1' => 'BillingProviderAddress1',
				'BillingProviderAddress2' => 'BillingProviderAddress2',
			],
			$data,
			'Data not set correctly'
		);
	}

	/**
	 * @covers SunCoastConnection\ClaimsToOEMR\X12N837\Cache::processLoop2330()
	 */
	public function testProcessLoop2330WithSegmentN4AndNM101_ILAndNoInsuranceType() {
		$mockObjects = $this->setupTestProcessLoop(
			Loop\Loop2330::class,
			[ 'NM1', 'N3', 'N4', 'REF' ],
			Segment\N4::class
		);

		$lastNM1 = $this->getMockery(
			Segment\NM1::class
		);

		$lastNM1->shouldReceive('element')
			->once()
			->with('NM101')
			->andReturn('IL');

		$data = [
			'LastNM1' => $lastNM1
		];

		$this->expectOutputRegex('/^Missing: CurrentInsuranceType \[.+:\d+\]$/');

		$this->callProtectedMethod(
			$this->cache,
			'processLoop2330',
			[
				$mockObjects['loop'],
				&$data
			]
		);

		$this->assertEquals(
			[
				'LastNM1' => $lastNM1,
			],
			$data,
			'Data not set correctly'
		);
	}

	/**
	 * @covers SunCoastConnection\ClaimsToOEMR\X12N837\Cache::processLoop2330()
	 */
	public function testProcessLoop2330WithSegmentN4AndNM101_ILAndInsuranceType1() {
		$mockObjects = $this->setupTestProcessLoop(
			Loop\Loop2330::class,
			[ 'NM1', 'N3', 'N4', 'REF' ],
			Segment\N4::class
		);

		$lastNM1 = $this->getMockery(
			Segment\NM1::class
		);

		$lastNM1->shouldReceive('element')
			->once()
			->with('NM101')
			->andReturn('IL');

		$mockObjects['segments'][0]->shouldReceive('element')
			->twice()
			->with('N401')
			->andReturn('PrimarySubscriberCity', 'PatientCity');

		$mockObjects['segments'][0]->shouldReceive('element')
			->twice()
			->with('N402')
			->andReturn('PrimarySubscriberState', 'PatientState');

		$mockObjects['segments'][0]->shouldReceive('element')
			->twice()
			->with('N403')
			->andReturn('PrimarySubscriberZip', 'PatientZip');

		$data = [
			'LastNM1' => $lastNM1,
			'CurrentInsuranceType' => 1,
			'PrimarySubscriberRelation' => 'self'
		];

		$this->callProtectedMethod(
			$this->cache,
			'processLoop2330',
			[
				$mockObjects['loop'],
				&$data
			]
		);

		$this->assertEquals(
			[
				'LastNM1' => $lastNM1,
				'CurrentInsuranceType' => 1,
				'PrimarySubscriberRelation' => 'self',
				'PrimarySubscriberCity' => 'PrimarySubscriberCity',
				'PrimarySubscriberState' => 'PrimarySubscriberState',
				'PrimarySubscriberZip' => 'PrimarySubscriberZip',
				'PatientCity' => 'PatientCity',
				'PatientState' => 'PatientState',
				'PatientZip' => 'PatientZip'
			],
			$data,
			'Data not set correctly'
		);
	}

	/**
	 * @covers SunCoastConnection\ClaimsToOEMR\X12N837\Cache::processLoop2330()
	 */
	public function testProcessLoop2330WithSegmentN4AndNM101_ILAndInsuranceType2() {
		$mockObjects = $this->setupTestProcessLoop(
			Loop\Loop2330::class,
			[ 'NM1', 'N3', 'N4', 'REF' ],
			Segment\N4::class
		);

		$lastNM1 = $this->getMockery(
			Segment\NM1::class
		);

		$lastNM1->shouldReceive('element')
			->once()
			->with('NM101')
			->andReturn('IL');

		$mockObjects['segments'][0]->shouldReceive('element')
			->twice()
			->with('N401')
			->andReturn('SecondarySubscriberCity', 'PatientCity');

		$mockObjects['segments'][0]->shouldReceive('element')
			->twice()
			->with('N402')
			->andReturn('SecondarySubscriberState', 'PatientState');

		$mockObjects['segments'][0]->shouldReceive('element')
			->twice()
			->with('N403')
			->andReturn('SecondarySubscriberZip', 'PatientZip');

		$data = [
			'LastNM1' => $lastNM1,
			'CurrentInsuranceType' => 2,
			'SecondarySubscriberRelation' => 'self'
		];

		$this->callProtectedMethod(
			$this->cache,
			'processLoop2330',
			[
				$mockObjects['loop'],
				&$data
			]
		);

		$this->assertEquals(
			[
				'LastNM1' => $lastNM1,
				'CurrentInsuranceType' => 2,
				'SecondarySubscriberRelation' => 'self',
				'SecondarySubscriberCity' => 'SecondarySubscriberCity',
				'SecondarySubscriberState' => 'SecondarySubscriberState',
				'SecondarySubscriberZip' => 'SecondarySubscriberZip',
				'PatientCity' => 'PatientCity',
				'PatientState' => 'PatientState',
				'PatientZip' => 'PatientZip'
			],
			$data,
			'Data not set correctly'
		);
	}

	/**
	 * @covers SunCoastConnection\ClaimsToOEMR\X12N837\Cache::processLoop2330()
	 */
	public function testProcessLoop2330WithSegmentN4AndNM101_ILAndInsuranceType3() {
		$mockObjects = $this->setupTestProcessLoop(
			Loop\Loop2330::class,
			[ 'NM1', 'N3', 'N4', 'REF' ],
			Segment\N4::class
		);

		$lastNM1 = $this->getMockery(
			Segment\NM1::class
		);

		$lastNM1->shouldReceive('element')
			->once()
			->with('NM101')
			->andReturn('IL');

		$mockObjects['segments'][0]->shouldReceive('element')
			->twice()
			->with('N401')
			->andReturn('TertiarySubscriberCity', 'PatientCity');

		$mockObjects['segments'][0]->shouldReceive('element')
			->twice()
			->with('N402')
			->andReturn('TertiarySubscriberState', 'PatientState');

		$mockObjects['segments'][0]->shouldReceive('element')
			->twice()
			->with('N403')
			->andReturn('TertiarySubscriberZip', 'PatientZip');

		$data = [
			'LastNM1' => $lastNM1,
			'CurrentInsuranceType' => 3,
			'TertiarySubscriberRelation' => 'self'
		];

		$this->callProtectedMethod(
			$this->cache,
			'processLoop2330',
			[
				$mockObjects['loop'],
				&$data
			]
		);

		$this->assertEquals(
			[
				'LastNM1' => $lastNM1,
				'CurrentInsuranceType' => 3,
				'TertiarySubscriberRelation' => 'self',
				'TertiarySubscriberCity' => 'TertiarySubscriberCity',
				'TertiarySubscriberState' => 'TertiarySubscriberState',
				'TertiarySubscriberZip' => 'TertiarySubscriberZip',
				'PatientCity' => 'PatientCity',
				'PatientState' => 'PatientState',
				'PatientZip' => 'PatientZip'
			],
			$data,
			'Data not set correctly'
		);
	}

	/**
	 * @covers SunCoastConnection\ClaimsToOEMR\X12N837\Cache::processLoop2330()
	 */
	public function testProcessLoop2330WithSegmentN4AndNM101_PRAndNoInsuranceType() {
		$mockObjects = $this->setupTestProcessLoop(
			Loop\Loop2330::class,
			[ 'NM1', 'N3', 'N4', 'REF' ],
			Segment\N4::class
		);

		$lastNM1 = $this->getMockery(
			Segment\NM1::class
		);

		$lastNM1->shouldReceive('element')
			->once()
			->with('NM101')
			->andReturn('PR');

		$data = [
			'LastNM1' => $lastNM1
		];

		$this->expectOutputRegex('/^Missing: CurrentInsuranceType \[.+:\d+\]$/');

		$this->callProtectedMethod(
			$this->cache,
			'processLoop2330',
			[
				$mockObjects['loop'],
				&$data
			]
		);

		$this->assertEquals(
			[
				'LastNM1' => $lastNM1,
			],
			$data,
			'Data not set correctly'
		);
	}

	/**
	 * @covers SunCoastConnection\ClaimsToOEMR\X12N837\Cache::processLoop2330()
	 */
	public function testProcessLoop2330WithSegmentN4AndNM101_PRAndInsuranceType1() {
		$mockObjects = $this->setupTestProcessLoop(
			Loop\Loop2330::class,
			[ 'NM1', 'N3', 'N4', 'REF' ],
			Segment\N4::class
		);

		$lastNM1 = $this->getMockery(
			Segment\NM1::class
		);

		$lastNM1->shouldReceive('element')
			->once()
			->with('NM101')
			->andReturn('PR');

		$mockObjects['segments'][0]->shouldReceive('element')
			->once()
			->with('N401')
			->andReturn('PrimaryPayerCity');

		$mockObjects['segments'][0]->shouldReceive('element')
			->once()
			->with('N402')
			->andReturn('PrimaryPayerState');

		$mockObjects['segments'][0]->shouldReceive('element')
			->once()
			->with('N403')
			->andReturn('PrimaryPayerZip');

		$data = [
			'LastNM1' => $lastNM1,
			'CurrentInsuranceType' => 1,
		];

		$this->callProtectedMethod(
			$this->cache,
			'processLoop2330',
			[
				$mockObjects['loop'],
				&$data
			]
		);

		$this->assertEquals(
			[
				'LastNM1' => $lastNM1,
				'CurrentInsuranceType' => 1,
				'PrimaryPayerCity' => 'PrimaryPayerCity',
				'PrimaryPayerState' => 'PrimaryPayerState',
				'PrimaryPayerZip' => 'PrimaryPayerZip'
			],
			$data,
			'Data not set correctly'
		);
	}

	/**
	 * @covers SunCoastConnection\ClaimsToOEMR\X12N837\Cache::processLoop2330()
	 */
	public function testProcessLoop2330WithSegmentN4AndNM101_PRAndInsuranceType2() {
		$mockObjects = $this->setupTestProcessLoop(
			Loop\Loop2330::class,
			[ 'NM1', 'N3', 'N4', 'REF' ],
			Segment\N4::class
		);

		$lastNM1 = $this->getMockery(
			Segment\NM1::class
		);

		$lastNM1->shouldReceive('element')
			->once()
			->with('NM101')
			->andReturn('PR');

		$mockObjects['segments'][0]->shouldReceive('element')
			->once()
			->with('N401')
			->andReturn('SecondaryPayerCity');

		$mockObjects['segments'][0]->shouldReceive('element')
			->once()
			->with('N402')
			->andReturn('SecondaryPayerState');

		$mockObjects['segments'][0]->shouldReceive('element')
			->once()
			->with('N403')
			->andReturn('SecondaryPayerZip');

		$data = [
			'LastNM1' => $lastNM1,
			'CurrentInsuranceType' => 2,
		];

		$this->callProtectedMethod(
			$this->cache,
			'processLoop2330',
			[
				$mockObjects['loop'],
				&$data
			]
		);

		$this->assertEquals(
			[
				'LastNM1' => $lastNM1,
				'CurrentInsuranceType' => 2,
				'SecondaryPayerCity' => 'SecondaryPayerCity',
				'SecondaryPayerState' => 'SecondaryPayerState',
				'SecondaryPayerZip' => 'SecondaryPayerZip'
			],
			$data,
			'Data not set correctly'
		);
	}

	/**
	 * @covers SunCoastConnection\ClaimsToOEMR\X12N837\Cache::processLoop2330()
	 */
	public function testProcessLoop2330WithSegmentN4AndNM101_PRAndInsuranceType3() {
		$mockObjects = $this->setupTestProcessLoop(
			Loop\Loop2330::class,
			[ 'NM1', 'N3', 'N4', 'REF' ],
			Segment\N4::class
		);

		$lastNM1 = $this->getMockery(
			Segment\NM1::class
		);

		$lastNM1->shouldReceive('element')
			->once()
			->with('NM101')
			->andReturn('PR');

		$mockObjects['segments'][0]->shouldReceive('element')
			->once()
			->with('N401')
			->andReturn('TertiaryPayerCity');

		$mockObjects['segments'][0]->shouldReceive('element')
			->once()
			->with('N402')
			->andReturn('TertiaryPayerState');

		$mockObjects['segments'][0]->shouldReceive('element')
			->once()
			->with('N403')
			->andReturn('TertiaryPayerZip');

		$data = [
			'LastNM1' => $lastNM1,
			'CurrentInsuranceType' => 3,
		];

		$this->callProtectedMethod(
			$this->cache,
			'processLoop2330',
			[
				$mockObjects['loop'],
				&$data
			]
		);

		$this->assertEquals(
			[
				'LastNM1' => $lastNM1,
				'CurrentInsuranceType' => 3,
				'TertiaryPayerCity' => 'TertiaryPayerCity',
				'TertiaryPayerState' => 'TertiaryPayerState',
				'TertiaryPayerZip' => 'TertiaryPayerZip'
			],
			$data,
			'Data not set correctly'
		);
	}

	/**
	 * @covers SunCoastConnection\ClaimsToOEMR\X12N837\Cache::processLoop2330()
	 */
	public function testProcessLoop2330WithSegmentN4AndNM101_77() {
		$mockObjects = $this->setupTestProcessLoop(
			Loop\Loop2330::class,
			[ 'NM1', 'N3', 'N4', 'REF' ],
			Segment\N4::class
		);

		$lastNM1 = $this->getMockery(
			Segment\NM1::class
		);

		$lastNM1->shouldReceive('element')
			->once()
			->with('NM101')
			->andReturn('77');

		$mockObjects['segments'][0]->shouldReceive('element')
			->once()
			->with('N401')
			->andReturn('ServiceFacilityCity');

		$mockObjects['segments'][0]->shouldReceive('element')
			->once()
			->with('N402')
			->andReturn('ServiceFacilityState');

		$mockObjects['segments'][0]->shouldReceive('element')
			->once()
			->with('N403')
			->andReturn('ServiceFacilityZip');

		$data = [
			'LastNM1' => $lastNM1
		];

		$this->callProtectedMethod(
			$this->cache,
			'processLoop2330',
			[
				$mockObjects['loop'],
				&$data
			]
		);

		$this->assertEquals(
			[
				'LastNM1' => $lastNM1,
				'ServiceFacilityCity' => 'ServiceFacilityCity',
				'ServiceFacilityState' => 'ServiceFacilityState',
				'ServiceFacilityZip' => 'ServiceFacilityZip'
			],
			$data,
			'Data not set correctly'
		);
	}

	/**
	 * @covers SunCoastConnection\ClaimsToOEMR\X12N837\Cache::processLoop2330()
	 */
	public function testProcessLoop2330WithSegmentN4AndNM101_85() {
		$mockObjects = $this->setupTestProcessLoop(
			Loop\Loop2330::class,
			[ 'NM1', 'N3', 'N4', 'REF' ],
			Segment\N4::class
		);

		$lastNM1 = $this->getMockery(
			Segment\NM1::class
		);

		$lastNM1->shouldReceive('element')
			->once()
			->with('NM101')
			->andReturn('85');

		$mockObjects['segments'][0]->shouldReceive('element')
			->once()
			->with('N401')
			->andReturn('BillingProviderCity');

		$mockObjects['segments'][0]->shouldReceive('element')
			->once()
			->with('N402')
			->andReturn('BillingProviderState');

		$mockObjects['segments'][0]->shouldReceive('element')
			->once()
			->with('N403')
			->andReturn('BillingProviderZip');

		$data = [
			'LastNM1' => $lastNM1
		];

		$this->callProtectedMethod(
			$this->cache,
			'processLoop2330',
			[
				$mockObjects['loop'],
				&$data
			]
		);

		$this->assertEquals(
			[
				'LastNM1' => $lastNM1,
				'BillingProviderCity' => 'BillingProviderCity',
				'BillingProviderState' => 'BillingProviderState',
				'BillingProviderZip' => 'BillingProviderZip'
			],
			$data,
			'Data not set correctly'
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

		$mockObjects['segments'][0]->shouldReceive('elementEquals')
			->once()
			->with('REF01', 'EI')
			->andReturn(true);

		$mockObjects['segments'][0]->shouldReceive('element')
			->once()
			->with('REF02')
			->andReturn('BillingProviderEIN');

		$data = [];

		$this->callProtectedMethod(
			$this->cache,
			'processLoop2330',
			[
				$mockObjects['loop'],
				&$data
			]
		);

		$this->assertEquals(
			[
				'BillingProviderEIN' => 'BillingProviderEIN'
			],
			$data,
			'Data not set correctly'
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

		$mockObjects['segments'][0]->shouldReceive('elementExists')
			->once()
			->with('SV101')
			->andReturn(true);

		$elementSV101 = $this->getMockery(
			Element::class
		);

		$mockObjects['segments'][0]->shouldReceive('element')
			->times(4)
			->with('SV101')
			->andReturn($elementSV101);

		$elementSV101->shouldReceive('subElementEquals')
			->once()
			->with(0, [ 'HC', 'WK' ])
			->andReturn(true);

		$elementSV101->shouldReceive('subElementExists')
			->once()
			->with(2)
			->andReturn(true);

		$elementSV101->shouldReceive('subElement')
			->once()
			->with(1)
			->andReturn('Tx01');

		$elementSV101->shouldReceive('subElement')
			->once()
			->with(2)
			->andReturn('TxMod01');

		$mockObjects['segments'][0]->shouldReceive('element')
			->once()
			->with('SV102')
			->andReturn('TxAmount01');

		$mockObjects['segments'][0]->shouldReceive('element')
			->once()
			->with('SV104')
			->andReturn('TxUnits01');

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

		$this->assertEquals(
			[
				'Tx' => [ 'Tx01' ],
				'TxMod' => [ 'TxMod01' ],
				'TxAmount' => [ 'TxAmount01' ],
				'TxUnits' => [ 'TxUnits01' ],
				'TxCount' => 1,
			],
			$data,
			'Data not set correctly'
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

		$mockObjects['segments'][0]->shouldReceive('element')
			->once()
			->with('DTP02')
			->andReturn('Dos1');

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

		$this->assertRegExp(
			'/Dos1[1-2][0-9][1-5][0-9][1-5][0-9]/',
			$data['Dos1'],
			'Data not set correctly'
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

		$mockObjects['segments'][0]->shouldReceive('elementEquals')
			->once()
			->with('NTE01', 'ADD')
			->andReturn(true);

		$mockObjects['segments'][0]->shouldReceive('element')
			->once()
			->with('NTE02')
			->andReturn('NoteDesc');

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

		$this->assertEquals(
			[
				'NoteDesc' => 'NoteDesc'
			],
			$data,
			'Data not set correctly'
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

		$mockObjects['segments'][0]->shouldReceive('element')
			->once()
			->with('NM102')
			->andReturn('RenderingType');

		$mockObjects['segments'][0]->shouldReceive('element')
			->once()
			->with('NM103')
			->andReturn('RenderingLastName');

		$mockObjects['segments'][0]->shouldReceive('element')
			->once()
			->with('NM104')
			->andReturn('RenderingFirstName');

		$mockObjects['segments'][0]->shouldReceive('element')
			->once()
			->with('NM105')
			->andReturn('RenderingMiddleName');

		$mockObjects['segments'][0]->shouldReceive('element')
			->once()
			->with('NM107')
			->andReturn('RenderingSuffix');

		$mockObjects['segments'][0]->shouldReceive('element')
			->once()
			->with('NM109')
			->andReturn('RenderingId');

		$data = [];

		$this->callProtectedMethod(
			$this->cache,
			'processLoop2420',
			[
				$mockObjects['loop'],
				&$data
			]
		);

		$this->assertEquals(
			[
				'LastNM1' => $mockObjects['segments'][0],
				'RenderingType' => 'RenderingType',
				'RenderingLastName' => 'RenderingLastName',
				'RenderingFirstName' => 'RenderingFirstName',
				'RenderingMiddleName' => 'RenderingMiddleName',
				'RenderingSuffix' => 'RenderingSuffix',
				'RenderingId' => 'RenderingId'
			],
			$data,
			'Data not set correctly'
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

		$mockObjects['segments'][0]->shouldReceive('element')
			->once()
			->with('NM103')
			->andReturn('ServiceFacilityName');

		$mockObjects['segments'][0]->shouldReceive('element')
			->once()
			->with('NM109')
			->andReturn('ServiceFacilityId');

		$data = [];

		$this->callProtectedMethod(
			$this->cache,
			'processLoop2420',
			[
				$mockObjects['loop'],
				&$data
			]
		);

		$this->assertEquals(
			[
				'LastNM1' => $mockObjects['segments'][0],
				'ServiceFacilityName' => 'ServiceFacilityName',
				'ServiceFacilityId' => 'ServiceFacilityId'
			],
			$data,
			'Data not set correctly'
		);
	}

	/**
	 * @covers SunCoastConnection\ClaimsToOEMR\X12N837\Cache::processLoop2420()
	 */
	public function testProcessLoop2420WithSegmentNM101_DQ() {
		$mockObjects = $this->setupTestProcessLoop(
			Loop\Loop2420::class,
			[ 'NM1', 'N3', 'N4', 'PRV' ],
			Segment\NM1::class
		);

		$mockObjects['segments'][0]->shouldReceive('element')
			->once()
			->with('NM101')
			->andReturn('DQ');

		$mockObjects['segments'][0]->shouldReceive('element')
			->once()
			->with('NM102')
			->andReturn('SupervisingType');

		$mockObjects['segments'][0]->shouldReceive('element')
			->once()
			->with('NM103')
			->andReturn('SupervisingLastName');

		$mockObjects['segments'][0]->shouldReceive('element')
			->once()
			->with('NM104')
			->andReturn('SupervisingFirstName');

		$mockObjects['segments'][0]->shouldReceive('element')
			->once()
			->with('NM105')
			->andReturn('SupervisingMiddleName');

		$mockObjects['segments'][0]->shouldReceive('element')
			->once()
			->with('NM107')
			->andReturn('SupervisingSuffix');

		$mockObjects['segments'][0]->shouldReceive('element')
			->once()
			->with('NM109')
			->andReturn('SupervisingId');

		$data = [];

		$this->callProtectedMethod(
			$this->cache,
			'processLoop2420',
			[
				$mockObjects['loop'],
				&$data
			]
		);

		$this->assertEquals(
			[
				'LastNM1' => $mockObjects['segments'][0],
				'SupervisingType' => 'SupervisingType',
				'SupervisingLastName' => 'SupervisingLastName',
				'SupervisingFirstName' => 'SupervisingFirstName',
				'SupervisingMiddleName' => 'SupervisingMiddleName',
				'SupervisingSuffix' => 'SupervisingSuffix',
				'SupervisingId' => 'SupervisingId'
			],
			$data,
			'Data not set correctly'
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

		$mockObjects['segments'][0]->shouldReceive('element')
			->once()
			->with('NM102')
			->andReturn('OrderingType');

		$mockObjects['segments'][0]->shouldReceive('element')
			->once()
			->with('NM103')
			->andReturn('OrderingLastName');

		$mockObjects['segments'][0]->shouldReceive('element')
			->once()
			->with('NM104')
			->andReturn('OrderingFirstName');

		$mockObjects['segments'][0]->shouldReceive('element')
			->once()
			->with('NM105')
			->andReturn('OrderingMiddleName');

		$mockObjects['segments'][0]->shouldReceive('element')
			->once()
			->with('NM107')
			->andReturn('OrderingSuffix');

		$mockObjects['segments'][0]->shouldReceive('element')
			->once()
			->with('NM109')
			->andReturn('OrderingId');

		$data = [];

		$this->callProtectedMethod(
			$this->cache,
			'processLoop2420',
			[
				$mockObjects['loop'],
				&$data
			]
		);

		$this->assertEquals(
			[
				'LastNM1' => $mockObjects['segments'][0],
				'OrderingType' => 'OrderingType',
				'OrderingLastName' => 'OrderingLastName',
				'OrderingFirstName' => 'OrderingFirstName',
				'OrderingMiddleName' => 'OrderingMiddleName',
				'OrderingSuffix' => 'OrderingSuffix',
				'OrderingId' => 'OrderingId'
			],
			$data,
			'Data not set correctly'
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

		$mockObjects['segments'][0]->shouldReceive('element')
			->once()
			->with('NM102')
			->andReturn('ReferringType');

		$mockObjects['segments'][0]->shouldReceive('element')
			->once()
			->with('NM103')
			->andReturn('ReferringLastName');

		$mockObjects['segments'][0]->shouldReceive('element')
			->once()
			->with('NM104')
			->andReturn('ReferringFirstName');

		$mockObjects['segments'][0]->shouldReceive('element')
			->once()
			->with('NM105')
			->andReturn('ReferringMiddleName');

		$mockObjects['segments'][0]->shouldReceive('element')
			->once()
			->with('NM107')
			->andReturn('ReferringSuffix');

		$mockObjects['segments'][0]->shouldReceive('element')
			->once()
			->with('NM109')
			->andReturn('ReferringId');

		$data = [];

		$this->callProtectedMethod(
			$this->cache,
			'processLoop2420',
			[
				$mockObjects['loop'],
				&$data
			]
		);

		$this->assertEquals(
			[
				'LastNM1' => $mockObjects['segments'][0],
				'ReferringType' => 'ReferringType',
				'ReferringLastName' => 'ReferringLastName',
				'ReferringFirstName' => 'ReferringFirstName',
				'ReferringMiddleName' => 'ReferringMiddleName',
				'ReferringSuffix' => 'ReferringSuffix',
				'ReferringId' => 'ReferringId'
			],
			$data,
			'Data not set correctly'
		);
	}

	/**
	 * @covers SunCoastConnection\ClaimsToOEMR\X12N837\Cache::processLoop2420()
	 */
	public function testProcessLoop2420WithSegmentN3AndNM101_77() {
		$mockObjects = $this->setupTestProcessLoop(
			Loop\Loop2420::class,
			[ 'NM1', 'N3', 'N4', 'PRV' ],
			Segment\N3::class
		);

		$lastNM1 = $this->getMockery(
			Segment\NM1::class
		);

		$lastNM1->shouldReceive('element')
			->once()
			->with('NM101')
			->andReturn('77');

		$mockObjects['segments'][0]->shouldReceive('element')
			->once()
			->with('N301')
			->andReturn('ServiceFacilityAddress1');

		$mockObjects['segments'][0]->shouldReceive('element')
			->once()
			->with('N302')
			->andReturn('ServiceFacilityAddress2');

		$data = [
			'LastNM1' => $lastNM1
		];

		$this->callProtectedMethod(
			$this->cache,
			'processLoop2420',
			[
				$mockObjects['loop'],
				&$data
			]
		);

		$this->assertEquals(
			[
				'LastNM1' => $lastNM1,
				'ServiceFacilityAddress1' => 'ServiceFacilityAddress1',
				'ServiceFacilityAddress2' => 'ServiceFacilityAddress2'
			],
			$data,
			'Data not set correctly'
		);
	}

	/**
	 * @covers SunCoastConnection\ClaimsToOEMR\X12N837\Cache::processLoop2420()
	 */
	public function testProcessLoop2420WithSegmentN3AndNM101_DK() {
		$mockObjects = $this->setupTestProcessLoop(
			Loop\Loop2420::class,
			[ 'NM1', 'N3', 'N4', 'PRV' ],
			Segment\N3::class
		);

		$lastNM1 = $this->getMockery(
			Segment\NM1::class
		);

		$lastNM1->shouldReceive('element')
			->once()
			->with('NM101')
			->andReturn('DK');

		$mockObjects['segments'][0]->shouldReceive('element')
			->once()
			->with('N301')
			->andReturn('OrderingAddress1');

		$mockObjects['segments'][0]->shouldReceive('element')
			->once()
			->with('N302')
			->andReturn('OrderingAddress2');

		$data = [
			'LastNM1' => $lastNM1
		];

		$this->callProtectedMethod(
			$this->cache,
			'processLoop2420',
			[
				$mockObjects['loop'],
				&$data
			]
		);

		$this->assertEquals(
			[
				'LastNM1' => $lastNM1,
				'OrderingAddress1' => 'OrderingAddress1',
				'OrderingAddress2' => 'OrderingAddress2'
			],
			$data,
			'Data not set correctly'
		);
	}

	/**
	 * @covers SunCoastConnection\ClaimsToOEMR\X12N837\Cache::processLoop2420()
	 */
	public function testProcessLoop2420WithSegmentN4AndNM101_77() {
		$mockObjects = $this->setupTestProcessLoop(
			Loop\Loop2420::class,
			[ 'NM1', 'N3', 'N4', 'PRV' ],
			Segment\N4::class
		);

		$lastNM1 = $this->getMockery(
			Segment\NM1::class
		);

		$lastNM1->shouldReceive('element')
			->once()
			->with('NM101')
			->andReturn('77');

		$mockObjects['segments'][0]->shouldReceive('element')
			->once()
			->with('N401')
			->andReturn('ServiceFacilityCity');

		$mockObjects['segments'][0]->shouldReceive('element')
			->once()
			->with('N402')
			->andReturn('ServiceFacilityState');

		$mockObjects['segments'][0]->shouldReceive('element')
			->once()
			->with('N403')
			->andReturn('ServiceFacilityZip');

		$data = [
			'LastNM1' => $lastNM1
		];

		$this->callProtectedMethod(
			$this->cache,
			'processLoop2420',
			[
				$mockObjects['loop'],
				&$data
			]
		);

		$this->assertEquals(
			[
				'LastNM1' => $lastNM1,
				'ServiceFacilityCity' => 'ServiceFacilityCity',
				'ServiceFacilityState' => 'ServiceFacilityState',
				'ServiceFacilityZip' => 'ServiceFacilityZip'
			],
			$data,
			'Data not set correctly'
		);
	}

	/**
	 * @covers SunCoastConnection\ClaimsToOEMR\X12N837\Cache::processLoop2420()
	 */
	public function testProcessLoop2420WithSegmentN4AndNM101_DK() {
		$mockObjects = $this->setupTestProcessLoop(
			Loop\Loop2420::class,
			[ 'NM1', 'N3', 'N4', 'PRV' ],
			Segment\N4::class
		);

		$lastNM1 = $this->getMockery(
			Segment\NM1::class
		);

		$lastNM1->shouldReceive('element')
			->once()
			->with('NM101')
			->andReturn('DK');

		$mockObjects['segments'][0]->shouldReceive('element')
			->once()
			->with('N401')
			->andReturn('OrderingCity');

		$mockObjects['segments'][0]->shouldReceive('element')
			->once()
			->with('N402')
			->andReturn('OrderingState');

		$mockObjects['segments'][0]->shouldReceive('element')
			->once()
			->with('N403')
			->andReturn('OrderingZip');

		$data = [
			'LastNM1' => $lastNM1
		];

		$this->callProtectedMethod(
			$this->cache,
			'processLoop2420',
			[
				$mockObjects['loop'],
				&$data
			]
		);

		$this->assertEquals(
			[
				'LastNM1' => $lastNM1,
				'OrderingCity' => 'OrderingCity',
				'OrderingState' => 'OrderingState',
				'OrderingZip' => 'OrderingZip'
			],
			$data,
			'Data not set correctly'
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

		$mockObjects['segments'][0]->shouldReceive('elementEquals')
			->once()
			->with('PRV01', 'PE')
			->andReturn(true);

		$mockObjects['segments'][0]->shouldReceive('elementEquals')
			->once()
			->with('PRV02', 'PXC')
			->andReturn(true);

		$mockObjects['segments'][0]->shouldReceive('element')
			->once()
			->with('PRV03')
			->andReturn('RenderingTaxonomy');

		$data = [];

		$this->callProtectedMethod(
			$this->cache,
			'processLoop2420',
			[
				$mockObjects['loop'],
				&$data
			]
		);

		$this->assertEquals(
			[
				'RenderingTaxonomy' => 'RenderingTaxonomy'
			],
			$data,
			'Data not set correctly'
		);
	}

}