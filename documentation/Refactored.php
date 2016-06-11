<?php

use \Symfony\Component\Finder\Finder;

class X12N837 {

	protected $workingDirectory = '';	// Path to working directory
	protected $singleMode = false;

	protected $database;

	protected $currentFile = [];		// File names

	protected $data;

	protected $segmentIndex = 0;		// Lines index
	protected $segment = [];

	public function __construct($path, $singleMode = true) {
		$this->workingDirectory = $path;

		$this->singleMode = $singleMode;

		$this->loadFileNames();
	}

	protected function loadFileNames() {
		$finder = new Finder;

		$finder->files()
			->in($this->workingDirectory)
			->name('*');

		foreach($finder as $file) {
			$this->currentFile[] = $file->getFilename();
		}
	}

	public function setDatabase($database) {
		$this->database = $database;
	}

	public function processFiles() {
		foreach($this->currentFile as $fileName) {

			echo "Filename:\t".$fileName.PHP_EOL;

			$this->resetData();

			$this->readFile($fileName);

			$this->processHeader();

			$this->data->badFile || $this->processLoop1000();

			if(!$this->data->badFile) {

				$this->storeX12();

				$this->processSegments();
			}

			// echo "Data:\t\t".var_export($this->data, true).PHP_EOL;
			// echo "database:\t".var_export($this->database, true).PHP_EOL;

			// exit;
		}
	}

	protected function resetData() {
		$this->data = new X12N837Data;
	}

	protected function readFile($fileName) {
		$string = file_get_contents($this->workingDirectory.'/'.$fileName);

		if($this->singleMode) {
			$string = str_replace('HL*1**20*1', '@@@@@@@@', $string);
			$string = str_replace('**20*1~', '~SE*28*0003~ST*837*0004*005010X222A1~', $string);
			$string = str_replace('@@@@@@@@', 'HL*1**20*1', $string);
			// $string = str_replace('\'', '\\\'', $string);
		}

		$this->segment = array_filter(explode('~', $string));
	}

	protected function parseSegment($index, $delimiter = '*') {
		return explode(
			$delimiter,
			$this->index($this->segment, $index)
		);
	}

	protected function indexExists(&$array, $index) {
		return is_array($array) &&
			array_key_exists($index, $array);
	}

	protected function index(&$array, $index) {
		if($this->indexExists($array, $index)) {
			return $array[$index];
		}
	}

	protected function indexEquals(&$array, $index, $value) {
		if(!is_array($value)) {
			$value = [$value];
		}

		return $this->indexExists($array, $index) &&
			in_array($this->index($array, $index), $value);
	}

	protected function processHeader() {

		// echo "Function:\t".__FUNCTION__.PHP_EOL;

		$this->data->badFile = false;

		$this->segmentIndex = 0;

		$this->processISA();

		if(!$this->data->badFile) {
			$this->processGS();
		}
	}

	protected function processISA() {

		// echo "Function:\t".__FUNCTION__.PHP_EOL;

		$segment = $this->parseSegment($this->segmentIndex);

		$designator = array_shift($segment);

		// echo "Segment Index:\t".$this->segmentIndex.PHP_EOL;
		// echo "Designator:\t".$designator.PHP_EOL;

		if($designator == 'ISA') {
			$this->data->isa = $segment;

			// echo "ISA\t\t".var_export($this->data->isa, true).PHP_EOL;

		} else {
			$this->data->badFile = true;
		}

		$this->segmentIndex += 1;
	}

	protected function processGS() {

		// echo "Function:\t".__FUNCTION__.PHP_EOL;

		$segment = $this->parseSegment($this->segmentIndex);

		$designator = array_shift($segment);

		// echo "Segment Index:\t".$this->segmentIndex.PHP_EOL;
		// echo "Designator:\t".$designator.PHP_EOL;

		if($designator == 'GS') {
			$this->data->gs = $segment;

			// echo "GS\t\t".var_export($this->data->gs, true).PHP_EOL;

		} else {
			$this->data->badFile = true;
		}

		$this->segmentIndex += 1;
	}

	protected function processLoop1000() {

		// echo "Function:\t".__FUNCTION__.PHP_EOL;

		$segmentFound = false;

		while(!$this->data->badFile && !$segmentFound) {
			if(array_key_exists($this->segmentIndex, $this->segment)) {
				$segment = $this->parseSegment($this->segmentIndex);

				// echo "Segment Index:\t".$this->segmentIndex.PHP_EOL;
				// echo "Designator:\t".$this->index($segment, 0).PHP_EOL;

				if($this->indexEquals($segment, 0, 'HL')) {
					$this->data->badFile = true;
				} elseif(
					$this->indexEquals($segment, 0, 'NM1') &&
					$this->indexEquals($segment, 1, '40') &&
					$this->indexEquals($segment, 2, '2')
				) {
					// 40*2*GATEWAY EDI*****46*43142076400000~
					array_shift($segment);
					$this->data->nm1_40_2 = $segment;

					$segmentFound = true;
				}

				$this->segmentIndex += 1;
			} else {
				$this->data->badFile = true;
			}
		}
	}

	protected function storeX12() {

		// echo "Function:\t".__FUNCTION__.PHP_EOL;

		$this->data->currentX12Partner = $this->database->storeX12Partner([
			'name' => $this->data->nm1_40_2[2],
			'id_number' => $this->data->nm1_40_2[8],
			'x12_sender_id' => $this->data->isa[5],
			'x12_receiver_id' => $this->data->isa[7],
			'x12_version' => $this->data->gs[7],	// '005010X098A1'
			'x12_isa01' => $this->data->isa[0],
			'x12_isa02' => $this->data->isa[1],
			'x12_isa03' => $this->data->isa[2],
			'x12_isa04' => $this->data->isa[3],
			'x12_isa05' => $this->data->isa[4],
			'x12_isa07' => $this->data->isa[6],
			'x12_isa14' => $this->data->isa[13],
			'x12_isa15' => $this->data->isa[14],
			'x12_gs02' => $this->data->gs[1],
			'x12_gs03' => $this->data->gs[2],
		]);
	}

	protected function processSegments() {

		// echo "Function:\t".__FUNCTION__.PHP_EOL;

		$this->segmentIndex = 2;

		while(!$this->data->badFile && $this->segmentIndex < count($this->segment)) {
			$this->resetData();

			$segment = $this->parseSegment($this->segmentIndex);
			$designator = array_shift($segment);

			// echo "Segment Index:\t".$this->segmentIndex.PHP_EOL;
			// echo "Designator:\t".$designator.PHP_EOL;
			// echo "Elements:\t".var_export($segment, true).PHP_EOL;

			if($designator == 'GE' || $designator == 'IEA') {
				$this->segmentIndex = count($this->segment);
			} elseif($designator == 'ST') {
				if(!$this->indexEquals($segment, 0, '837')) {
					$this->data->badFile = true;
				} else {
					$this->segmentIndex += 1;

					$endLoop = false;

					while(!$endLoop) {
						$segment = $this->parseSegment($this->segmentIndex);
						$designator = array_shift($segment);

						// echo "Segment Index:\t".$this->segmentIndex.PHP_EOL;
						// echo "Designator:\t".$designator.PHP_EOL;
						// echo "Elements:\t".var_export($segment, true).PHP_EOL;

						switch($designator) {
							case 'BHT':	// Header
								// We don't want to parse a reporting transactions (loops with BHT06 = RP)
								$endLoop = $this->indexEquals($segment, 5, 'RP'); break;
							case 'CLM':	// Loop: 2300
								$this->designatorCLM($segment); break;
							case 'DMG':	// Loop: 2010, 2320
								$this->designatorDMG($segment); break;
							case 'DTP':	// Loop: 2000, 2300, 2330, 2400, 2430
								$endLoop = (bool) $this->designatorDTP($segment); break;
							case 'HI':	// Loop: 2300, 2400
								$this->designatorHI($segment); break;
							case 'N3':	// Loop: 1000, 2010, 2310, 2330, 2420
								$this->designatorN3($segment); break;
							case 'N4':	// Loop: 1000, 2010, 2310, 2330, 2420
								$this->designatorN4($segment); break;
							case 'NM1':	// Loop: 1000, 2010, 2310, 2330, 2420
								$this->designatorNM1($segment); break;
							case 'NTE':	// Loop: 2300, 2400
								$this->designatorNTE($segment); break;
							case 'PAT':	// Loop: 2000
								$this->designatorPAT($segment); break;
							case 'PRV':	// Loop: 2000, 2310, 2420
								$this->designatorPRV($segment); break;
							case 'REF':	// Loop: 1000, 2010, 2300, 2310, 2330, 2400, 2410, 2420
								$this->designatorREF($segment); break;
							case 'SBR':	// Loop: 2000, 2320
								$this->designatorSBR($segment); break;
							case 'SV1':	// Loop: 2400
								$this->designatorSV1($segment); break;
							case 'SE':	// Transaction Set Trailer
								$endLoop = true; break;
							default:
								$endLoop = !$this->indexExists($this->segment, $this->segmentIndex); break;
						}

						$this->segmentIndex += 1;
					}
				}

				$this->storeRecords();
			} else {
				$this->segmentIndex += 1;
			}

			// exit;

		}
	}

	protected function designatorCLM(&$segment) {

		// echo "Function:\t".__FUNCTION__.PHP_EOL;

		$this->data->claimid = $this->index($segment, 0);
		$this->data->claimamt = $this->index($segment, 1);

		$element5 = explode(':', $this->index($segment, 4));

		$this->data->facilitycodevalue = $this->index($element5, 0);
		$this->data->facilitycodequalifier = $this->index($element5, 1);
		$this->data->frequencytypecode = $this->index($element5, 2);

		$this->data->provsignatureonfile = $this->index($segment, 6);
		$this->data->provacceptassignmentcode = ($this->indexEquals($segment, 7, 'A') ?
			'true' :
			''
		);
		$this->data->benefitindicator = $this->index($segment, 8);
		$this->data->releaseofinformation = $this->index($segment, 9);

		// echo "Data:\t\t".var_export([
		// 	'claimid' => $this->data->claimid,
		// 	'claimamt' => $this->data->claimamt,
		// 	'facilitycodevalue' => $this->data->facilitycodevalue,
		// 	'facilitycodequalifier' => $this->data->facilitycodequalifier,
		// 	'frequencytypecode' => $this->data->frequencytypecode,
		// 	'provsignatureonfile' => $this->data->provsignatureonfile,
		// 	'provacceptassignmentcode' => $this->data->provacceptassignmentcode,
		// 	'benefitindicator' => $this->data->benefitindicator,
		// 	'releaseofinformation' => $this->data->releaseofinformation,
		// ], true).PHP_EOL;
	}

	protected function designatorDMG(&$segment) {

		// echo "Function:\t".__FUNCTION__.PHP_EOL;

		$this->data->subdob = $this->index($segment, 1);
		$this->data->subsex = $this->index($segment, 2);

		// $data = [
		// 	'subdob' => $this->data->subdob,
		// 	'subsex' => $this->data->subsex,
		// 	'currentInsuranceType' => $this->data->currentInsuranceType,
		// ];

		if($this->data->currentInsuranceType == 1) {
			$this->data->primarysubdob = $this->data->subdob;
			$this->data->primarysubsex = $this->data->subsex;

			// $data['primarysubdob'] = $this->data->primarysubdob;
 			// $data['primarysubsex'] = $this->data->primarysubsex;
			// $data['primarysubrel'] = $this->data->primarysubrel;

			if($this->data->primarysubrel == 'self') {
				$this->data->patdob = $this->data->subdob;
				$this->data->patsex = $this->data->subsex;

				// $data['patdob'] = $this->data->patdob;
				// $data['patsex'] = $this->data->patsex;
			}
		}

		if($this->data->currentInsuranceType == 2) {
			$this->data->secondarysubdob = $this->data->subdob;
			$this->data->secondarysubsex = $this->data->subsex;

			// $data['secondarysubdob'] = $this->data->secondarysubdob;
			// $data['secondarysubsex'] = $this->data->secondarysubsex;
			// $data['secondarysubrel'] = $this->data->secondarysubrel;

			if($this->data->secondarysubrel == 'self') {
				$this->data->patdob = $this->data->subdob;
				$this->data->patsex = $this->data->subsex;

				// $data['patdob'] = $this->data->patdob;
				// $data['patsex'] = $this->data->patsex;
			}
		}

		if($this->data->currentInsuranceType == 3) {
			$this->data->tertiarysubdob = $this->data->subdob;
			$this->data->tertiarysubsex = $this->data->subsex;

			// $data['tertiarysubdob'] = $this->data->tertiarysubdob;
			// $data['tertiarysubsex'] = $this->data->tertiarysubsex;
			// $data['tertiarysubrel'] = $this->data->tertiarysubrel;

			if($this->data->tertiarysubrel == 'self') {
				$this->data->patdob = $this->data->subdob;
				$this->data->patsex = $this->data->subsex;

				// $data['patdob'] = $this->data->patdob;
				// $data['patsex'] = $this->data->patsex;
			}
		}

		// echo "Data:\t\t".var_export($data, true).PHP_EOL;
	}

	protected function designatorDTP(&$segment) {

		// echo "Function:\t".__FUNCTION__.PHP_EOL;

		switch($this->index($segment, 0)) {
			case '431':		// DTP*431*D8*20160121~ 431  Onset of current
				$this->data->dos2 = $this->index($segment, 2);

				// echo "Data:\t\t".var_export([
				// 	'dos2' => $this->data->dos2,
				// ], true).PHP_EOL;

				break;
			case '472':		// SIT DTP date of service
				$this->data->dos1 = $this->index($segment, 2).rand(10, 20).rand(10, 59).rand(10, 59);

				// echo "Data:\t\t".var_export([
				// 	'dos1' => $this->data->dos1,
				// ], true).PHP_EOL;

				break;
			default:
				return true;
		}
	}

	protected function designatorHI(&$segment) {

		// echo "Function:\t".__FUNCTION__.PHP_EOL;

		$element1 = explode(':', $this->index($segment, 0));

		if($this->indexEquals($element1, 0, ['ABK', 'BK'])) {
			foreach($segment as $subElement) {
				$subElement = explode(':', $subElement);

				if(count($subElement) < 2) {
					break;
				}

				$this->data->dxType[] = $this->index($subElement, 0);
				$this->data->dx[] = $this->index($subElement, 1);
			}

			$this->data->dxCount = count($this->data->dxType);

			// echo "Data:\t\t".var_export([
			// 	'dxType' => $this->data->dxType,
			// 	'dx' => $this->data->dx,
			// 	'dxCount' => $this->data->dxCount,
			// ], true).PHP_EOL;
		}
	}

	protected function designatorN3(&$segment) {

		// echo "Function:\t".__FUNCTION__.PHP_EOL;

		// $data = [
		// 	'lastNM101' => $this->data->lastNM101,
		// ];

		switch($this->data->lastNM101) {
			case '41':
				$this->data->submitteradd1 = $this->index($segment, 0);
				$this->data->submitteradd2 = $this->index($segment, 1);

				// $data['submitteradd1'] = $this->data->submitteradd1;
				// $data['submitteradd2'] = $this->data->submitteradd2;

				break;
			case 'IL':

				// $data['currentInsuranceType'] = $this->data->currentInsuranceType;

				switch($this->data->currentInsuranceType) {
					case 1:
						$this->data->primarysubadd1 = $this->index($segment, 0);
						$this->data->primarysubadd2 = $this->index($segment, 1);

						// $data['primarysubadd1'] = $this->data->primarysubadd1;
						// $data['primarysubadd2'] = $this->data->primarysubadd2;
						// $data['primarysubrel'] = $this->data->primarysubrel;

						if($this->data->primarysubrel == 'self') {
							$this->data->patadd1 = $this->index($segment, 0);
							$this->data->patadd2 = $this->index($segment, 1);

							// $data['patadd1'] = $this->data->patadd1;
							// $data['patadd2'] = $this->data->patadd2;

						}
						break;
					case 2:
						$this->data->secondarysubadd1 = $this->index($segment, 0);
						$this->data->secondarysubadd2 = $this->index($segment, 1);

						// $data['secondarysubadd1'] = $this->data->secondarysubadd1;
						// $data['secondarysubadd2'] = $this->data->secondarysubadd2;
						// $data['secondarysubrel'] = $this->data->secondarysubrel;
						// $data['patadd1'] = $this->data->patadd1;

						if($this->data->secondarysubrel == 'self' && $this->data->patadd1 == '') {
							$this->data->patadd1 = $this->index($segment, 0);
							$this->data->patadd2 = $this->index($segment, 1);

							// $data['patadd1'] = $this->data->patadd1;
							// $data['patadd2'] = $this->data->patadd2;

						}
						break;
					case 3:
						$this->data->tertiarysubadd1 = $this->index($segment, 0);
						$this->data->tertiarysubadd2 = $this->index($segment, 1);

						// $data['tertiarysubadd1'] = $this->data->tertiarysubadd1;
						// $data['tertiarysubadd2'] = $this->data->tertiarysubadd2;
						// $data['tertiarysubrel'] = $this->data->tertiarysubrel;
						// $data['patadd1'] = $this->data->patadd1;

						if($this->data->tertiarysubrel == 'self' && $this->data->patadd1 == '') {
							$this->data->patadd1 = $this->index($segment, 0);
							$this->data->patadd2 = $this->index($segment, 1);

							// $data['patadd1'] = $this->data->patadd1;
							// $data['patadd2'] = $this->data->patadd2;

						}
						break;
				}
				break;
			case 'PR':

				// $data['currentInsuranceType'] = $this->data->currentInsuranceType;

				switch($this->data->currentInsuranceType) {
					case 1:
						$this->data->primarypayeradd1 = $this->index($segment, 0);
						$this->data->primarypayeradd2 = $this->index($segment, 1);

						// $data['primarypayeradd1'] = $this->data->primarypayeradd1;
						// $data['primarypayeradd2'] = $this->data->primarypayeradd2;

						break;
					case 2:
						$this->data->secondarypayeradd1 = $this->index($segment, 0);
						$this->data->secondarypayeradd2 = $this->index($segment, 1);

						// $data['secondarypayeradd1'] = $this->data->secondarypayeradd1;
						// $data['secondarypayeradd2'] = $this->data->secondarypayeradd2;

						break;
					case 3:
						$this->data->tertiarypayeradd1 = $this->index($segment, 0);
						$this->data->tertiarypayeradd2 = $this->index($segment, 1);

						// $data['tertiarypayeradd1'] = $this->data->tertiarypayeradd1;
						// $data['tertiarypayeradd2'] = $this->data->tertiarypayeradd2;

						break;
				}
				break;
			case 'QC':
				$this->data->patadd1 = $this->index($segment, 0);
				$this->data->patadd2 = $this->index($segment, 1);

				// $data['patadd1'] = $this->data->patadd1;
				// $data['patadd2'] = $this->data->patadd2;

				break;
			case '77':
				$this->data->servicefacilityadd1 = $this->index($segment, 0);
				$this->data->servicefacilityadd2 = $this->index($segment, 1);

				// $data['servicefacilityadd1'] = $this->data->servicefacilityadd1;
				// $data['servicefacilityadd2'] = $this->data->servicefacilityadd2;

				break;
			case '85':
				$this->data->billingprovideradd1 = $this->index($segment, 0);
				$this->data->billingprovideradd2 = $this->index($segment, 1);

				// $data['billingprovideradd1'] = $this->data->billingprovideradd1;
				// $data['billingprovideradd2'] = $this->data->billingprovideradd2;

				break;
			case 'DK':
				$this->data->orderingadd1 = $this->index($segment, 0);
				$this->data->orderingadd2 = $this->index($segment, 1);

				// $data['orderingadd1'] = $this->data->orderingadd1;
				// $data['orderingadd2'] = $this->data->orderingadd2;

				break;
			case '87':
				$this->data->paytoprovideradd1 = $this->index($segment, 0);
				$this->data->paytoprovideradd2 = $this->index($segment, 1);

				// $data['paytoprovideradd1'] = $this->data->paytoprovideradd1;
				// $data['paytoprovideradd2'] = $this->data->paytoprovideradd2;

				break;
		}

		// echo "Data:\t\t".var_export($data, true).PHP_EOL;
	}

	protected function designatorN4(&$segment) {

		// echo "Function:\t".__FUNCTION__.PHP_EOL;

		// $data = [
		// 	'lastNM101' => $this->data->lastNM101,
		// ];

		switch($this->data->lastNM101) {
			case '41':
				$this->data->submittercity = $this->index($segment, 0);
				$this->data->submitterstate = $this->index($segment, 1);
				$this->data->submitterzip = $this->index($segment, 2);

				// $data['submittercity'] = $this->data->submittercity;
				// $data['submitterstate'] = $this->data->submitterstate;
				// $data['submitterzip'] = $this->data->submitterzip;

				break;
			case 'IL':

				// $data['currentInsuranceType'] = $this->data->currentInsuranceType;

				switch($this->data->currentInsuranceType) {
					case 1:
						$this->data->primarysubcity = $this->index($segment, 0);
						$this->data->primarysubstate = $this->index($segment, 1);
						$this->data->primarysubzip = $this->index($segment, 2);

						// $data['primarysubcity'] = $this->data->primarysubcity;
						// $data['primarysubstate'] = $this->data->primarysubstate;
						// $data['primarysubzip'] = $this->data->primarysubzip;
						// $data['primarysubrel'] = $this->data->primarysubrel;

						if($this->data->primarysubrel == 'self') {
							$this->data->patcity = $this->index($segment, 0);
							$this->data->patstate = $this->index($segment, 1);
							$this->data->patzip = $this->index($segment, 2);

							// $data['patcity'] = $this->data->patcity;
							// $data['patstate'] = $this->data->patstate;
							// $data['patzip'] = $this->data->patzip;

						}
						break;
					case 2:
						$this->data->secondarysubcity = $this->index($segment, 0);
						$this->data->secondarysubstate = $this->index($segment, 1);
						$this->data->secondarysubzip = $this->index($segment, 2);

						// $data['secondarysubcity'] = $this->data->secondarysubcity;
						// $data['secondarysubstate'] = $this->data->secondarysubstate;
						// $data['secondarysubzip'] = $this->data->secondarysubzip;
						// $data['secondarysubrel'] = $this->data->secondarysubrel;
						// $data['patcity'] = $this->data->patcity;

						if($this->data->secondarysubrel == 'self' && $this->data->patcity == '') {
							$this->data->patcity = $this->index($segment, 0);
							$this->data->patstate = $this->index($segment, 1);
							$this->data->patzip = $this->index($segment, 2);

							// $data['patcity'] = $this->data->patcity;
							// $data['patstate'] = $this->data->patstate;
							// $data['patzip'] = $this->data->patzip;

						}
						break;
					case 3:
						$this->data->tertiarysubcity = $this->index($segment, 0);
						$this->data->tertiarysubstate = $this->index($segment, 1);
						$this->data->tertiarysubzip = $this->index($segment, 2);

						// $data['tertiarysubcity'] = $this->data->tertiarysubcity;
						// $data['tertiarysubstate'] = $this->data->tertiarysubstate;
						// $data['tertiarysubzip'] = $this->data->tertiarysubzip;
						// $data['tertiarysubrel'] = $this->data->tertiarysubrel;
						// $data['patcity'] = $this->data->patcity;

						if($this->data->tertiarysubrel == 'self' && $this->data->patcity == '') {
							$this->data->patcity = $this->index($segment, 0);
							$this->data->patstate = $this->index($segment, 1);
							$this->data->patzip = $this->index($segment, 2);

							// $data['patcity'] = $this->data->patcity;
							// $data['patstate'] = $this->data->patstate;
							// $data['patzip'] = $this->data->patzip;

						}
						break;
				}
				break;
			case 'PR':

				// $data['currentInsuranceType'] = $this->data->currentInsuranceType;

				switch($this->data->currentInsuranceType) {
					case 1:
						$this->data->primarypayercity = $this->index($segment, 0);
						$this->data->primarypayerstate = $this->index($segment, 1);
						$this->data->primarypayerzip = $this->index($segment, 2);

						// $data['primarypayercity'] = $this->data->primarypayercity;
						// $data['primarypayerstate'] = $this->data->primarypayerstate;
						// $data['primarypayerzip'] = $this->data->primarypayerzip;

						break;
					case 2:
						$this->data->secondarypayercity = $this->index($segment, 0);
						$this->data->secondarypayerstate = $this->index($segment, 1);
						$this->data->secondarypayerzip = $this->index($segment, 2);

						// $data['secondarypayercity'] = $this->data->secondarypayercity;
						// $data['secondarypayerstate'] = $this->data->secondarypayerstate;
						// $data['secondarypayerzip'] = $this->data->secondarypayerzip;

						break;
					case 3:
						$this->data->tertiarypayercity = $this->index($segment, 0);
						$this->data->tertiarypayerstate = $this->index($segment, 1);
						$this->data->tertiarypayerzip = $this->index($segment, 2);

						// $data['tertiarypayercity'] = $this->data->tertiarypayercity;
						// $data['tertiarypayerstate'] = $this->data->tertiarypayerstate;
						// $data['tertiarypayerzip'] = $this->data->tertiarypayerzip;

						break;
				}
				break;
			case 'QC':
				$this->data->patcity = $this->index($segment, 0);
				$this->data->patstate = $this->index($segment, 1);
				$this->data->patzip = $this->index($segment, 2);

				// $data['patcity'] = $this->data->patcity;
				// $data['patstate'] = $this->data->patstate;
				// $data['patzip'] = $this->data->patzip;

				break;
			case '77':
				$this->data->servicefacilitycity = $this->index($segment, 0);
				$this->data->servicefacilitystate = $this->index($segment, 1);
				$this->data->servicefacilityzip = $this->index($segment, 2);

				// $data['servicefacilitycity'] = $this->data->servicefacilitycity;
				// $data['servicefacilitystate'] = $this->data->servicefacilitystate;
				// $data['servicefacilityzip'] = $this->data->servicefacilityzip;

				break;
			case '85':
				$this->data->billingprovidercity = $this->index($segment, 0);
				$this->data->billingproviderstate = $this->index($segment, 1);
				$this->data->billingproviderzip = $this->index($segment, 2);

				// $data['billingprovidercity'] = $this->data->billingprovidercity;
				// $data['billingproviderstate'] = $this->data->billingproviderstate;
				// $data['billingproviderzip'] = $this->data->billingproviderzip;

				break;
			case 'DK':
				$this->data->orderingcity = $this->index($segment, 0);
				$this->data->orderingstate = $this->index($segment, 1);
				$this->data->orderingzip = $this->index($segment, 2);

				// $data['orderingcity'] = $this->data->orderingcity;
				// $data['orderingstate'] = $this->data->orderingstate;
				// $data['orderingzip'] = $this->data->orderingzip;

				break;
			case '87':
				$this->data->paytoprovidercity = $this->index($segment, 0);
				$this->data->paytoproviderstate = $this->index($segment, 1);
				$this->data->paytoproviderzip = $this->index($segment, 2);

				// $data['paytoprovidercity'] = $this->data->paytoprovidercity;
				// $data['paytoproviderstate'] = $this->data->paytoproviderstate;
				// $data['paytoproviderzip'] = $this->data->paytoproviderzip;

				break;
		}

		// echo "Data:\t\t".var_export($data, true).PHP_EOL;
	}

	protected function designatorNM1(&$segment) {

		// echo "Function:\t".__FUNCTION__.PHP_EOL;

		if($this->indexExists($segment, 8)) {
			$this->data->lastNM101 = $this->index($segment, 0);

			// $data = [
			// 	'lastNM101' => $this->data->lastNM101,
			// ];

			switch($this->data->lastNM101) {
				case 'IL':

					// $data['currentInsuranceType'] = $this->data->currentInsuranceType;

					switch($this->data->currentInsuranceType) {
						case 1:
							$this->data->primarysublname = $this->index($segment, 2);
							$this->data->primarysubfname = $this->index($segment, 3);
							$this->data->primarysubmname = $this->index($segment, 4);
							$this->data->primarysubsuffix = $this->index($segment, 6);
							$this->data->primarysubid = $this->index($segment, 8);

							// $data['primarysublname'] = $this->data->primarysublname;
							// $data['primarysubfname'] = $this->data->primarysubfname;
							// $data['primarysubmname'] = $this->data->primarysubmname;
							// $data['primarysubsuffix'] = $this->data->primarysubsuffix;
							// $data['primarysubid'] = $this->data->primarysubid;
							// $data['primarysubrel'] = $this->data->primarysubrel;

							if($this->data->primarysubrel == 'self') {
								$this->data->patlname = $this->index($segment, 2);
								$this->data->patfname = $this->index($segment, 3);
								$this->data->patmname = $this->index($segment, 4);
								$this->data->patsuffix = $this->index($segment, 6);
								$this->data->patid = $this->index($segment, 8);

								// $data['patlname'] = $this->data->patlname;
								// $data['patfname'] = $this->data->patfname;
								// $data['patmname'] = $this->data->patmname;
								// $data['patsuffix'] = $this->data->patsuffix;
								// $data['patid'] = $this->data->patid;

							}
							break;
						case 2:
							$this->data->secondarysublname = $this->index($segment, 2);
							$this->data->secondarysubfname = $this->index($segment, 3);
							$this->data->secondarysubmname = $this->index($segment, 4);
							$this->data->secondarysubsuffix = $this->index($segment, 6);
							$this->data->secondarysubid = $this->index($segment, 8);

							// $data['secondarysublname'] = $this->data->secondarysublname;
							// $data['secondarysubfname'] = $this->data->secondarysubfname;
							// $data['secondarysubmname'] = $this->data->secondarysubmname;
							// $data['secondarysubsuffix'] = $this->data->secondarysubsuffix;
							// $data['secondarysubid'] = $this->data->secondarysubid;
							// $data['secondarysubrel'] = $this->data->secondarysubrel;
							// $data['patlname'] = $this->data->patlname;

							if($this->data->secondarysubrel == 'self' && $this->data->patlname == '') {
								$this->data->patlname = $this->index($segment, 2);
								$this->data->patfname = $this->index($segment, 3);
								$this->data->patmname = $this->index($segment, 4);
								$this->data->patsuffix = $this->index($segment, 6);
								$this->data->patid = $this->index($segment, 8);

								// $data['patlname'] = $this->data->patlname;
								// $data['patfname'] = $this->data->patfname;
								// $data['patmname'] = $this->data->patmname;
								// $data['patsuffix'] = $this->data->patsuffix;
								// $data['patid'] = $this->data->patid;

							}
							break;
						case 3:
							$this->data->tertiarysublname = $this->index($segment, 2);
							$this->data->tertiarysubfname = $this->index($segment, 3);
							$this->data->tertiarysubmname = $this->index($segment, 4);
							$this->data->tertiarysubsuffix = $this->index($segment, 6);
							$this->data->tertiarysubid = $this->index($segment, 8);

							// $data['tertiarysublname'] = $this->data->tertiarysublname;
							// $data['tertiarysubfname'] = $this->data->tertiarysubfname;
							// $data['tertiarysubmname'] = $this->data->tertiarysubmname;
							// $data['tertiarysubsuffix'] = $this->data->tertiarysubsuffix;
							// $data['tertiarysubid'] = $this->data->tertiarysubid;
							// $data['tertiarysubrel'] = $this->data->tertiarysubrel;
							// $data['patlname'] = $this->data->patlname;

							if($this->data->tertiarysubrel == 'self' && $this->data->patlname == '') {
								$this->data->patlname = $this->index($segment, 2);
								$this->data->patfname = $this->index($segment, 3);
								$this->data->patmname = $this->index($segment, 4);
								$this->data->patsuffix = $this->index($segment, 6);
								$this->data->patid = $this->index($segment, 8);

								// $data['patlname'] = $this->data->patlname;
								// $data['patfname'] = $this->data->patfname;
								// $data['patmname'] = $this->data->patmname;
								// $data['patsuffix'] = $this->data->patsuffix;
								// $data['patid'] = $this->data->patid;

							}
							break;
					}
					break;
				case 'PR':

					// $data['currentInsuranceType'] = $this->data->currentInsuranceType;

					switch($this->data->currentInsuranceType) {
						case 1:
							$this->data->primarypayername = $this->index($segment, 2);
							$this->data->primarypayerid = $this->index($segment, 8);

							// $data['primarypayername'] = $this->data->primarypayername;
							// $data['primarypayerid'] = $this->data->primarypayerid;

							break;
						case 2:
							$this->data->secondarypayername = $this->index($segment, 2);
							$this->data->secondarypayerid = $this->index($segment, 8);

							// $data['secondarypayername'] = $this->data->secondarypayername;
							// $data['secondarypayerid'] = $this->data->secondarypayerid;

							break;
						case 3:
							$this->data->tertiarypayername = $this->index($segment, 2);
							$this->data->tertiarypayerid = $this->index($segment, 8);

							// $data['tertiarypayername'] = $this->data->tertiarypayername;
							// $data['tertiarypayerid'] = $this->data->tertiarypayerid;

							break;
					}
					break;
				case 'QC':
					$this->data->patlname = $this->index($segment, 2);
					$this->data->patfname = $this->index($segment, 3);
					$this->data->patmname = $this->index($segment, 4);
					$this->data->patsuffix = $this->index($segment, 6);
					$this->data->patid = $this->index($segment, 8);

					// $data['patlname'] = $this->data->patlname;
					// $data['patfname'] = $this->data->patfname;
					// $data['patmname'] = $this->data->patmname;
					// $data['patsuffix'] = $this->data->patsuffix;
					// $data['patid'] = $this->data->patid;

					break;
				case 'DN':
					$this->data->referringtype = $this->index($segment, 1);
					$this->data->referringlname = $this->index($segment, 2);
					$this->data->referringfname = $this->index($segment, 3);
					$this->data->referringmname = $this->index($segment, 4);
					$this->data->referringsuffix = $this->index($segment, 6);
					$this->data->referringid = $this->index($segment, 8);

					// $data['referringtype'] = $this->data->referringtype;
					// $data['referringlname'] = $this->data->referringlname;
					// $data['referringfname'] = $this->data->referringfname;
					// $data['referringmname'] = $this->data->referringmname;
					// $data['referringsuffix'] = $this->data->referringsuffix;
					// $data['referringid'] = $this->data->referringid;

					break;
				case 'DK':
					$this->data->orderingtype = $this->index($segment, 1);
					$this->data->orderinglname = $this->index($segment, 2);
					$this->data->orderingfname = $this->index($segment, 3);
					$this->data->orderingmname = $this->index($segment, 4);
					$this->data->orderingsuffix = $this->index($segment, 6);
					$this->data->orderingid = $this->index($segment, 8);

					// $data['orderingtype'] = $this->data->orderingtype;
					// $data['orderinglname'] = $this->data->orderinglname;
					// $data['orderingfname'] = $this->data->orderingfname;
					// $data['orderingmname'] = $this->data->orderingmname;
					// $data['orderingsuffix'] = $this->data->orderingsuffix;
					// $data['orderingid'] = $this->data->orderingid;

					break;
				case 'DQ':
					$this->data->supervisingtype = $this->index($segment, 1);
					$this->data->supervisinglname = $this->index($segment, 2);
					$this->data->supervisingfname = $this->index($segment, 3);
					$this->data->supervisingmname = $this->index($segment, 4);
					$this->data->supervisingsuffix = $this->index($segment, 6);
					$this->data->supervisingid = $this->index($segment, 8);

					// $data['supervisingtype'] = $this->data->supervisingtype;
					// $data['supervisinglname'] = $this->data->supervisinglname;
					// $data['supervisingfname'] = $this->data->supervisingfname;
					// $data['supervisingmname'] = $this->data->supervisingmname;
					// $data['supervisingsuffix'] = $this->data->supervisingsuffix;
					// $data['supervisingid'] = $this->data->supervisingid;

					break;
				case '82':
					$this->data->renderingtype = $this->index($segment, 1);
					$this->data->renderinglname = $this->index($segment, 2);
					$this->data->renderingfname = $this->index($segment, 3);
					$this->data->renderingmname = $this->index($segment, 4);
					$this->data->renderingsuffix = $this->index($segment, 6);
					$this->data->renderingid = $this->index($segment, 8);

					// $data['renderingtype'] = $this->data->renderingtype;
					// $data['renderinglname'] = $this->data->renderinglname;
					// $data['renderingfname'] = $this->data->renderingfname;
					// $data['renderingmname'] = $this->data->renderingmname;
					// $data['renderingsuffix'] = $this->data->renderingsuffix;
					// $data['renderingid'] = $this->data->renderingid;

					break;
				case '77':
					if($this->indexEquals($segment, 1, '2')) {
						////////////// Service facility only //////////////////
						$this->data->servicefacilityname = $this->index($segment, 2);
						$this->data->servicefacilityid = $this->index($segment, 8);	// npi

						// $data['servicefacilityname'] = $this->data->servicefacilityname;
						// $data['servicefacilityid'] = $this->data->servicefacilityid;

					}
					break;
				case '85':
					//////////////// Billing and service facility ////
					$this->data->billingtype = $this->index($segment, 1);
					$this->data->billingproviderlname = $this->index($segment, 2);
					$this->data->billingproviderfname = $this->index($segment, 3);
					$this->data->billingprovidermname = $this->index($segment, 4);
					$this->data->billingprovidersuffix = $this->index($segment, 6);
					$this->data->billingproviderid = $this->index($segment, 8);

					// $data['billingtype'] = $this->data->billingtype;
					// $data['billingproviderlname'] = $this->data->billingproviderlname;
					// $data['billingproviderfname'] = $this->data->billingproviderfname;
					// $data['billingprovidermname'] = $this->data->billingprovidermname;
					// $data['billingprovidersuffix'] = $this->data->billingprovidersuffix;
					// $data['billingproviderid'] = $this->data->billingproviderid;

					break;
				case '41':
					$this->data->submittername = $this->index($segment, 2);
					$this->data->submitterid = $this->index($segment, 8);
					//////////////// END Billing and or service facility ////

					// $data['submittername'] = $this->data->submittername;
					// $data['submitterid'] = $this->data->submitterid;

					break;
				case '87':
					$this->data->paytotype = $this->index($segment, 1);
					$this->data->paytoprovidername = $this->index($segment, 2);
					$this->data->paytoproviderfname = $this->index($segment, 3);
					$this->data->paytoprovidermname = $this->index($segment, 4);
					$this->data->paytoprovidersuffix = $this->index($segment, 6);
					$this->data->paytoproviderid = $this->index($segment, 8);

					// $data['paytotype'] = $this->data->paytotype;
					// $data['paytoprovidername'] = $this->data->paytoprovidername;
					// $data['paytoproviderfname'] = $this->data->paytoproviderfname;
					// $data['paytoprovidermname'] = $this->data->paytoprovidermname;
					// $data['paytoprovidersuffix'] = $this->data->paytoprovidersuffix;
					// $data['paytoproviderid'] = $this->data->paytoproviderid;

					break;
			}

			// echo "Data:\t\t".var_export($data, true).PHP_EOL;
		}
	}

	protected function designatorNTE(&$segment) {

		// echo "Function:\t".__FUNCTION__.PHP_EOL;

		if($this->indexEquals($segment, 0, 'ADD')) {
			$this->data->notedesc = $this->index($segment, 1);

			// echo "Data:\t\t".var_export([
			// 	'notedesc' => $this->data->notedesc,
			// ], true).PHP_EOL;

		}
	}

	protected function designatorPAT(&$segment) {

		// echo "Function:\t".__FUNCTION__.PHP_EOL;

		// $data = [
		// 	'currentInsuranceType' => $this->data->currentInsuranceType,
		// 	'primarysubrel' => $this->data->primarysubrel,
		// 	'secondarysubrel' => $this->data->secondarysubrel,
		// 	'tertiarysubrel' => $this->data->tertiarysubrel,
		// ];

		if($this->data->currentInsuranceType == 1 && $this->data->primarysubrel != 'self') {
			$this->data->primarypatrel = $this->index($segment, 0);

			// $data['primarypatrel'] = $this->data->primarypatrel;

		}

		if($this->data->currentInsuranceType == 2 && $this->data->secondarysubrel != 'self') {
			$this->data->secondarypatrel = $this->index($segment, 0);

			// $data['secondarypatrel'] = $this->data->secondarypatrel;

		}

		if($this->data->currentInsuranceType == 3 && $this->data->tertiarysubrel != 'self') {
			$this->data->tertiarypatrel = $this->index($segment, 0);

			// $data['tertiarypatrel'] = $this->data->tertiarypatrel;

		}

		// echo "Data:\t\t".var_export($data, true).PHP_EOL;
	}

	protected function designatorPRV(&$segment) {

		// echo "Function:\t".__FUNCTION__.PHP_EOL;

		if($this->index($segment, 1) == 'PXC') {
			switch($this->index($segment, 0)) {
				case 'BI':
					$this->data->billingprovidertaxonomy = $this->index($segment, 2);

					// echo "Data:\t\t".var_export([
					// 	'billingprovidertaxonomy' => $this->data->billingprovidertaxonomy,
					// ], true).PHP_EOL;

					break;
				case 'PE':
					$this->data->renderingtaxonomy = $this->index($segment, 2);

					// echo "Data:\t\t".var_export([
					// 	'renderingtaxonomy' => $this->data->renderingtaxonomy,
					// ], true).PHP_EOL;

					break;
			}
		}
	}

	protected function designatorREF(&$segment) {

		// echo "Function:\t".__FUNCTION__.PHP_EOL;

		switch($this->index($segment, 0)) {
			case 'EI':
				$this->data->billproEIN = $this->index($segment, 1);

				// echo "Data:\t\t".var_export([
				// 	'billproEIN' => $this->data->billproEIN,
				// ], true).PHP_EOL;

				break;
			case 'EA':
				$this->data->medicalrecordnumber = $this->index($segment, 1);

				// echo "Data:\t\t".var_export([
				// 	'medicalrecordnumber' => $this->data->medicalrecordnumber,
				// ], true).PHP_EOL;

				break;
		}
	}

	protected function designatorSBR(&$segment) {

		// echo "Function:\t".__FUNCTION__.PHP_EOL;

		switch($this->index($segment, 0)) {
			case 'P':
				$this->data->currentInsuranceType = 1;
				$this->data->primarysubrel = ($this->index($segment, 1) == '18' ?
					'self' :
					''
				);
				$this->data->primarypolicy = $this->index($segment, 2);
				$this->data->primaryplanname = $this->index($segment, 3);

				// echo "Data:\t\t".var_export([
				// 	'currentInsuranceType' => $this->data->currentInsuranceType,
				// 	'primarysubrel' => $this->data->primarysubrel,
				// 	'primarypolicy' => $this->data->primarypolicy,
				// 	'primaryplanname' => $this->data->primaryplanname,
				// ], true).PHP_EOL;

				break;
			case 'S':
				$this->data->currentInsuranceType = 2;
				$this->data->secondarysubrel = ($this->index($segment, 1) == '18' ?
					'self' :
					''
				);
				$this->data->secondarypolicy = $this->index($segment, 2);
				$this->data->secondaryplanname = $this->index($segment, 3);

				// echo "Data:\t\t".var_export([
				// 	'currentInsuranceType' => $this->data->currentInsuranceType,
				// 	'secondarysubrel' => $this->data->secondarysubrel,
				// 	'secondarypolicy' => $this->data->secondarypolicy,
				// 	'secondaryplanname' => $this->data->secondaryplanname,
				// ], true).PHP_EOL;

				break;
			case 'T':
				$this->data->currentInsuranceType = 3;
				$this->data->tertiarysubrel = ($this->index($segment, 1) == '18' ?
					'self' :
					''
				);
				$this->data->tertiarypolicy = $this->index($segment, 2);
				$this->data->tertiaryplanname = $this->index($segment, 3);

				// echo "Data:\t\t".var_export([
				// 	'currentInsuranceType' => $this->data->currentInsuranceType,
				// 	'tertiarysubrel' => $this->data->tertiarysubrel,
				// 	'tertiarypolicy' => $this->data->tertiarypolicy,
				// 	'tertiaryplanname' => $this->data->tertiaryplanname,
				// ], true).PHP_EOL;

				break;
		}
	}

	protected function designatorSV1(&$segment) {

		// echo "Function:\t".__FUNCTION__.PHP_EOL;

		$element1 = explode(':', $this->index($segment, 0));	// See if is HC or WK

		if($this->indexEquals($element1, 0, ['HC', 'WK'])) {
			$this->data->tx[] = $this->index($element1, 1);

			// $data = [
			// 	'tx' => $this->data->tx,
			// ];

			if($this->indexExists($element1, 2)) {
				$this->data->txMod[] = $this->index($element1, 2);

				// $data['txMod'] = $this->data->txMod;

			}

			$this->data->txAmount[] = $this->index($segment, 1);

			$this->data->txUnits[] = $this->index($segment, 3);

			$this->data->txCount = count($this->data->tx);

			// $data['txAmount'] = $this->data->txAmount;
			// $data['txUnits'] = $this->data->txUnits;
			// $data['txCount'] = $this->data->txCount;
			// echo "Data:\t\t".var_export($data, true).PHP_EOL;

		}
	}

	protected function storeRecords() {

		// echo "Function:\t".__FUNCTION__.PHP_EOL;

		if(!$this->data->badFile && $this->segmentIndex < count($this->segment)) {
			$this->storeInsurance();

			$this->storeFacilities();

			$this->storeUsers();

			$this->storePatients();

			$this->storeEncounters();
		}
	}

	protected function storeInsurance() {

		// echo "Function:\t".__FUNCTION__.PHP_EOL;

		$insuranceCompanyFields = [
			'attn' => 'Claims',
			'x12_receiver_id' => $this->data->currentX12Partner,
			'x12_default_partner_id' => $this->data->currentX12Partner,
		];

		$addressFields = [
			'country' => null,
			'foreign_id' => null,
		];

		$phoneNumberFields = [
			'country_code' => '+1',
			'area_code' => null,
			'prefix' => null,
			'number' => null,
			'type' => '2',
		];

		if($this->data->primarypayerid != '') {
			$this->data->currentInsurance = $this->database->storeInsuranceCompany(array_merge($insuranceCompanyFields, [
				'name' => $this->data->primarypayername,
				'cms_id' => $this->data->primarypayerid,
			]));

			$this->database->storeAddress(array_merge($addressFields, [
				'line1' => $this->data->primarypayeradd1,
				'line2' => $this->data->primarypayeradd2,
				'city' => $this->data->primarypayercity,
				'state' => $this->data->primarypayerstate,
				'zip' => substr($this->data->primarypayerzip, 0, 5),
				'plus_four' => substr($this->data->primarypayerzip, 5),
				'foreign_id' => $this->data->currentInsurance,
			]));

			$this->database->storePhoneNumber(array_merge($phoneNumberFields, [
				'foreign_id' => $this->data->currentInsurance,
			]));
		}

		if($this->data->secondarypayerid != '') {
			$this->data->currentInsurance = $this->database->storeInsuranceCompany(array_merge($insuranceCompanyFields, [
				'name' => $this->data->secondarypayername,
				'cms_id' => $this->data->secondarypayerid,
			]));

			$this->database->storeAddress(array_merge($addressFields, [
				'line1' => $this->data->secondarypayeradd1,
				'line2' => $this->data->secondarypayeradd2,
				'city' => $this->data->secondarypayercity,
				'state' => $this->data->secondarypayerstate,
				'zip' => substr($this->data->secondarypayerzip, 0, 5),
				'plus_four' => substr($this->data->secondarypayerzip, 5),
				'foreign_id' => $this->data->currentInsurance,
			]));

			$this->database->storePhoneNumber(array_merge($phoneNumberFields, [
				'foreign_id' => $this->data->currentInsurance,
			]));
		}

		if($this->data->tertiarypayerid != '') {
			$this->data->currentInsurance = $this->database->storeInsuranceCompany(array_merge($insuranceCompanyFields, [
				'name' => $this->data->tertiarypayername,
				'cms_id' => $this->data->tertiarypayerid,
			]));

			$this->database->storeAddress(array_merge($addressFields, [
				'line1' => $this->data->tertiarypayeradd1,
				'line2' => $this->data->tertiarypayeradd2,
				'city' => $this->data->tertiarypayercity,
				'state' => $this->data->tertiarypayerstate,
				'zip' => substr($this->data->tertiarypayerzip, 0, 5),
				'plus_four' => substr($this->data->tertiarypayerzip, 5),
				'foreign_id' => $this->data->currentInsurance,
			]));

			$this->database->storePhoneNumber(array_merge($phoneNumberFields, [
				'foreign_id' => $this->data->currentInsurance,
			]));
		}
	}

	protected function storeFacilities() {

		// echo "Function:\t".__FUNCTION__.PHP_EOL;

		$facilityFields = [
			'country_code' => 'USA',
			'service_location' => '1',
			'billing_location' => '0',
			'accepts_assignment' => '1',
			'pos_code' => $this->data->facilitycodevalue,
			'attn' => 'Billing',
			'tax_id_type' => 'EI',
			'color' => '#FFCC99',
			'primary_business_entity' => '0',
		];

		if($this->data->servicefacilityid != '') {
			$this->data->currentFacility = $this->database->storeFacility(array_merge($facilityFields, [
				'name' => $this->data->servicefacilityname,
				'street' => $this->data->servicefacilityadd1.' '.$this->data->servicefacilityadd2,
				'city' => $this->data->servicefacilitycity,
				'state' => $this->data->servicefacilitystate,
				'postal_code' => $this->data->servicefacilityzip,
				'federal_ein' => '',
				'domain_identifier' => $this->data->servicefacilityid,
			]));
		}

		if($this->data->billingproviderid != '') {
			$this->data->currentFacility = $this->database->storeFacility(array_merge($facilityFields, [
				'name' => $this->data->billingproviderlname,
				'street' => $this->data->billingprovideradd1.' '.$this->data->billingprovideradd2,
				'city' => $this->data->billingprovidercity,
				'state' => $this->data->billingproviderstate,
				'postal_code' => $this->data->billingproviderzip,
				'federal_ein' => $this->data->billproEIN,
				'domain_identifier' => $this->data->billingproviderid,
			]));
		}
	}

	protected function storeUsers() {

		// echo "Function:\t".__FUNCTION__.PHP_EOL;

		$userFields = [
			'password' => '70702b9402107c11ef9d18d9daad4ff1',
			'authorized' => '1',
			'federaltaxid' => '',
			'federaldrugid' => '',
			'see_auth' => '3',
			'active' => '1',
			'cal_ui' => '3',
			'taxonomy' => '',
			'calendar' => '1',
			'abook_type' => 'miscellaneous',
			'state_license_number' => '',
			'facility_id' => $this->data->currentFacility,
		];

		$groupFields = [
			'name' => 'Default',
		];

		if($this->data->referringtype == '1') {
			$this->data->currentUser = $this->database->storeUser(array_merge($userFields, [
				'username' => $this->data->referringlname.$this->data->referringfname,
				'fname' => $this->data->referringfname,
				'mname' => $this->data->referringmname,
				'lname' => $this->data->referringlname,
				'facility' => $this->data->servicefacilityname,
				'npi' => $this->data->referringid,
			]));

			$this->database->storeGroup(array_merge($groupFields, [
				'user' => $this->data->referringlname.$this->data->referringfname,
			]));
		}

		if($this->data->paytotype == '1') {
			$this->data->currentUser = $this->database->storeUser(array_merge($userFields, [
				'username' => $this->data->paytoproviderlname.$this->data->paytoproviderfname,
				'fname' => $this->data->paytoproviderfname,
				'mname' => $this->data->paytoprovidermname,
				'lname' => $this->data->paytoproviderlname,
				'facility' => $this->data->servicefacilityname,
				'npi' => $this->data->paytoproviderid,
			]));

			$this->database->storeGroup(array_merge($groupFields, [
				'user' => $this->data->paytoproviderlname.$this->data->paytoproviderfname,
			]));
		}

		if($this->data->supervisingtype == '1') {
			$this->data->currentUser = $this->database->storeUser(array_merge($userFields, [
				'username' => $this->data->supervisinglname.$this->data->supervisingfname,
				'fname' => $this->data->supervisingfname,
				'mname' => $this->data->supervisingmname,
				'lname' => $this->data->supervisinglname,
				'facility' => $this->data->servicefacilityname,
				'npi' => $this->data->supervisingid,
			]));

			$this->database->storeGroup(array_merge($groupFields, [
				'user' => $this->data->supervisinglname.$this->data->supervisingfname,
			]));
		}

		if($this->data->orderingtype == '1') {
			$this->data->currentUser = $this->database->storeUser(array_merge($userFields, [
				'username' => $this->data->orderinglname.$this->data->orderingfname,
				'fname' => $this->data->orderingfname,
				'mname' => $this->data->orderingmname,
				'lname' => $this->data->orderinglname,
				'facility' => $this->data->servicefacilityname,
				'facility_id' => $this->data->currentFacility,
				'npi' => $this->data->orderingid,
			]));

			$this->database->storeGroup(array_merge($groupFields, [
				'user' => $this->data->orderinglname.$this->data->orderingfname,
			]));
		}

		if($this->data->billingtype == '1') {
			$this->data->currentUser = $this->database->storeUser(array_merge($userFields, [
				'username' => $this->data->billingproviderlname.$this->data->billingproviderfname,
				'fname' => $this->data->billingproviderfname,
				'mname' => $this->data->billingprovidermname,
				'lname' => $this->data->billingproviderlname,
				'facility' => $this->data->servicefacilityname,
				'facility_id' => $this->data->currentFacility,
				'npi' => $this->data->billingproviderid,
				'taxonomy' => $this->data->billingprovidertaxonomy,
			]));

			$this->database->storeGroup(array_merge($groupFields, [
				'user' => $this->data->billingproviderlname.$this->data->billingproviderfname,
			]));
		}

		if($this->data->renderingtype == '1') {
			$this->data->currentUser = $this->database->storeUser(array_merge($userFields, [
				'username' => $this->data->renderinglname.$this->data->renderingfname,
				'fname' => $this->data->renderingfname,
				'mname' => $this->data->renderingmname,
				'lname' => $this->data->renderinglname,
				'facility' => $this->data->servicefacilityname,
				'facility_id' => $this->data->currentFacility,
				'npi' => $this->data->renderingid,
			]));

			$this->database->storeGroup(array_merge($groupFields, [
				'user' => $this->data->renderinglname.$this->data->renderingfname,
			]));
		}
	}

	protected function storePatients() {

		// echo "Function:\t".__FUNCTION__.PHP_EOL;

		$this->data->currentPatient = $this->database->storePatientData([
			'language' => 'English',
			'fname' => $this->data->patfname,
			'lname' => $this->data->patlname,
			'mname' => $this->data->patmname,
			'DOB' => $this->data->patdob,
			'street' => $this->data->patadd1.' '.$this->data->patadd2,
			'postal_code' => $this->data->patzip,
			'city' => $this->data->patcity,
			'state' => $this->data->patstate,
			'date' => $this->data->dos1,
			'sex' => $this->data->patsex,
			'providerID' => $this->data->currentUser,
			'pubpid' => $this->data->currentPatient,
			'pid' => $this->data->currentPatient,
		]);

		$insuranceDataFields = [
			'provider' => $this->data->currentInsuranceData,
			'subscriber_country' => 'USA',
			'date' => $this->data->dos1,
			'pid' => $this->data->currentPatient,
			'accept_assignment' => $this->data->provacceptassignmentcode,
		];

		if($this->data->primarypayerid != '') {
			$this->data->currentInsuranceData = $this->database->storeInsuranceData(array_merge($insuranceDataFields, [
				'type' => 'primary',
				'plan_name' => $this->data->primaryplanname,
				'policy_number' => $this->data->primarysubid,
				'group_number' => $this->data->primarypolicy,
				'subscriber_lname' => $this->data->primarysublname,
				'subscriber_mname' => $this->data->primarysubmname,
				'subscriber_fname' => $this->data->primarysubfname,
				'subscriber_relationship' => $this->data->primarysubrel,
				'subscriber_DOB' => $this->data->primarysubdob,
				'subscriber_street' => $this->data->primarysubadd1.' '.$this->data->primarysubadd2,
				'subscriber_postal_code' => $this->data->primarysubzip,
				'subscriber_city' => $this->data->primarysubcity,
				'subscriber_state' => $this->data->primarysubstate,
				'subscriber_sex' => $this->data->primarysubsex,
			]));
		}

		if($this->data->secondarypayerid != '') {
			$this->data->currentInsuranceData = $this->database->storeInsuranceData(array_merge($insuranceDataFields, [
				'type' => 'secondary',
				'plan_name' => $this->data->secondaryplanname,
				'policy_number' => $this->data->secondarysubid,
				'group_number' => $this->data->secondarypolicy,
				'subscriber_lname' => $this->data->secondarysublname,
				'subscriber_mname' => $this->data->secondarysubmname,
				'subscriber_fname' => $this->data->secondarysubfname,
				'subscriber_relationship' => $this->data->secondarysubrel,
				'subscriber_DOB' => $this->data->secondarysubdob,
				'subscriber_street' => $this->data->secondarysubadd1.' '.$this->data->secondarysubadd2,
				'subscriber_postal_code' => $this->data->secondarysubzip,
				'subscriber_city' => $this->data->secondarysubcity,
				'subscriber_state' => $this->data->secondarysubstate,
				'subscriber_sex' => $this->data->secondarysubsex,
			]));
		}

		if($this->data->tertiarypayerid != '') {
			$this->data->currentInsuranceData = $this->database->storeInsuranceData(array_merge($insuranceDataFields, [
				'type' => 'tertiary',
				'plan_name' => $this->data->tertiaryplanname,
				'policy_number' => $this->data->tertiarysubid,
				'group_number' => $this->data->tertiarypolicy,
				'subscriber_lname' => $this->data->tertiarysublname,
				'subscriber_mname' => $this->data->tertiarysubmname,
				'subscriber_fname' => $this->data->tertiarysubfname,
				'subscriber_relationship' => $this->data->tertiarysubrel,
				'subscriber_DOB' => $this->data->tertiarysubdob,
				'subscriber_street' => $this->data->tertiarysubadd1.' '.$this->data->tertiarysubadd2,
				'subscriber_postal_code' => $this->data->tertiarysubzip,
				'subscriber_city' => $this->data->tertiarysubcity,
				'subscriber_state' => $this->data->tertiarysubstate,
				'subscriber_sex' => $this->data->tertiarysubsex,
			]));
		}
	}

	protected function storeEncounters() {

		// echo "Function:\t".__FUNCTION__.PHP_EOL;

		$this->data->currentFormEncounter = $this->database->storeFormEncounter([
			'date' => $this->data->dos1,
			'reason' => 'Imported Encounter',
			'facility' => $this->data->billingproviderlname,
			'facility_id' => $this->data->currentFacility,
			'pid' => $this->data->currentPatient,
			'encounter' => $this->data->claimid,
			'onset_date' => '0000-00-00 00:00:00',
			'sensitivity' => 'normal',
			'billing_note' => 'NULL',
			'pc_catid' => '1',
			'last_level_billed' => '0',
			'last_level_closed' => '0',
			'last_stmt_date' => 'NULL',
			'stmt_count' => '0',
			'provider_id' => $this->data->currentUser,
			'supervisor_id' => '0',
			'invoice_refno' => '',
			'referral_source' => '',
			'billing_facility' => $this->data->currentFacility,
		]);

		$this->data->currentForm = $this->database->storeForm([
			'date' => $this->data->dos1,
			'encounter' => $this->data->claimid,
			'form_name' => 'New Patient Encounter',
			'form_id' => $this->data->currentFormEncounter,
			'pid' => $this->data->currentPatient,
			'user' => $this->data->currentUser,
			'groupname' => 'Default',
			'authorized' => '1',
			'deleted' => '0',
			'formdir' => 'newpatient',
		]);

		$billingFields = [
			'date' => $this->data->dos1,
			'pid' => $this->data->currentPatient,
			'provider_id' => $this->data->currentUser,
			'user' => $this->data->currentUser,
			'groupname' => 'Default',
			'authorized' => '1',
			'encounter' => $this->data->claimid,
			'code_text' => '',
			'billed' => '0',
			'activity' => '1',
			'payer_id' => $this->data->currentInsurance,
			'bill_process' => '0',
			'bill_date' => 'NULL',
			'process_date' => 'NULL',
			'process_file' => 'NULL',
		];

		foreach($this->data->tx as $index => $tx) {
			if(array_key_exists(0, $this->data->dx)) {
				$dx = $this->data->dx[0];
			} else {
				$dx = '';
			}

			$this->database->storeBilling(array_merge($billingFields, [
				'code_type' => 'CPT4',
				'code' => $tx,
				'modifier' => $this->data->txmod[$index],
				'units' => $this->data->txunits[$index],
				'fee' => $this->data->txamount[$index],
				'justify' => $dx,
			]));
		}

		foreach($this->data->dx as $index => $dx) {
			$this->database->storeBilling(array_merge($billingFields, [
				'code_type' => ($this->data->dxType[$index] == 'ABK' ? 'ICD10' : 'ICD9'),
				'code' => $dx,
				'modifier' => '',
				'units' => '1',
				'fee' => '0',
				'justify' => '',
			]));
		}
	}
}

class X12N837Database {

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

		// echo "Function:\t".__FUNCTION__.PHP_EOL;

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

		// echo "Function:\t".__FUNCTION__.PHP_EOL;

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

		// echo "Function:\t".__FUNCTION__.PHP_EOL;

		$this->formEncounter[] = array_merge($this::$formEncounterFields, $data);

		return count($this->formEncounter) - 1;
	}

	public function storeForm(array $data) {

		// echo "Function:\t".__FUNCTION__.PHP_EOL;

		$this->form[] = array_merge($this::$formFields, $data);

		return count($this->form) - 1;
	}

	public function storeGroup(array $data) {

		// echo "Function:\t".__FUNCTION__.PHP_EOL;

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

		// echo "Function:\t".__FUNCTION__.PHP_EOL;

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

		// echo "Function:\t".__FUNCTION__.PHP_EOL;

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

		// echo "Function:\t".__FUNCTION__.PHP_EOL;

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

		// echo "Function:\t".__FUNCTION__.PHP_EOL;

		$this->phoneNumber[] = array_merge($this::$phoneNumberFields, $data);

		return count($this->phoneNumber) - 1;
	}

	public function storeUser(array $data) {

		// echo "Function:\t".__FUNCTION__.PHP_EOL;

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

		// echo "Function:\t".__FUNCTION__.PHP_EOL;

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

class X12N837Data {
	public $badFile;
	public $isa;
	public $gs;
	public $nm1_40_2;
	public $currentX12Partner;
	public $claimid;
	public $claimamt;
	public $facilitycodevalue;
	public $facilitycodequalifier;
	public $frequencytypecode;
	public $provsignatureonfile;
	public $provacceptassignmentcode;
	public $benefitindicator;
	public $releaseofinformation;
	public $subdob;
	public $subsex;
	public $currentInsuranceType;
	public $primarysubdob;
	public $primarysubsex;
	public $primarysubrel;
	public $patdob;
	public $patsex;
	public $secondarysubdob;
	public $secondarysubsex;
	public $secondarysubrel;
	public $tertiarysubdob;
	public $tertiarysubsex;
	public $tertiarysubrel;
	public $dos2;
	public $dos1;
	public $dxType;
	public $dx = [];
	public $dxCount;
	public $lastNM101;
	public $submitteradd1;
	public $submitteradd2;
	public $primarysubadd1;
	public $primarysubadd2;
	public $patadd1;
	public $patadd2;
	public $secondarysubadd1;
	public $secondarysubadd2;
	public $tertiarysubadd1;
	public $tertiarysubadd2;
	public $primarypayeradd1;
	public $primarypayeradd2;
	public $secondarypayeradd1;
	public $secondarypayeradd2;
	public $tertiarypayeradd1;
	public $tertiarypayeradd2;
	public $servicefacilityadd1;
	public $servicefacilityadd2;
	public $billingprovideradd1;
	public $billingprovideradd2;
	public $orderingadd1;
	public $orderingadd2;
	public $paytoprovideradd1;
	public $paytoprovideradd2;
	public $submittercity;
	public $submitterstate;
	public $submitterzip;
	public $patcity;
	public $patstate;
	public $patzip;
	public $primarysubcity;
	public $primarysubstate;
	public $primarysubzip;
	public $secondarysubcity;
	public $secondarysubstate;
	public $secondarysubzip;
	public $tertiarysubcity;
	public $tertiarysubstate;
	public $tertiarysubzip;
	public $primarypayercity;
	public $primarypayerstate;
	public $primarypayerzip;
	public $secondarypayercity;
	public $secondarypayerstate;
	public $secondarypayerzip;
	public $tertiarypayercity;
	public $tertiarypayerstate;
	public $tertiarypayerzip;
	public $servicefacilitycity;
	public $servicefacilitystate;
	public $servicefacilityzip;
	public $billingprovidercity;
	public $billingproviderstate;
	public $billingproviderzip;
	public $orderingcity;
	public $orderingstate;
	public $orderingzip;
	public $paytoprovidercity;
	public $paytoproviderstate;
	public $paytoproviderzip;
	public $patlname;
	public $patfname;
	public $patmname;
	public $patsuffix;
	public $patid;
	public $primarysublname;
	public $primarysubfname;
	public $primarysubmname;
	public $primarysubsuffix;
	public $primarysubid;
	public $secondarysublname;
	public $secondarysubfname;
	public $secondarysubmname;
	public $secondarysubsuffix;
	public $secondarysubid;
	public $tertiarysublname;
	public $tertiarysubfname;
	public $tertiarysubmname;
	public $tertiarysubsuffix;
	public $tertiarysubid;
	public $primarypayername;
	public $primarypayerid;
	public $secondarypayername;
	public $secondarypayerid;
	public $tertiarypayername;
	public $tertiarypayerid;
	public $referringtype;
	public $referringlname;
	public $referringfname;
	public $referringmname;
	public $referringsuffix;
	public $referringid;
	public $orderingtype;
	public $orderinglname;
	public $orderingfname;
	public $orderingmname;
	public $orderingsuffix;
	public $orderingid;
	public $supervisingtype;
	public $supervisinglname;
	public $supervisingfname;
	public $supervisingmname;
	public $supervisingsuffix;
	public $supervisingid;
	public $renderingtype;
	public $renderinglname;
	public $renderingfname;
	public $renderingmname;
	public $renderingsuffix;
	public $renderingid;
	public $servicefacilityname;
	public $servicefacilityid;
	public $billingtype;
	public $billingproviderlname;
	public $billingproviderfname;
	public $billingprovidermname;
	public $billingprovidersuffix;
	public $billingproviderid;
	public $submittername;
	public $submitterid;
	public $paytotype;
	public $paytoprovidername;
	public $paytoproviderfname;
	public $paytoprovidermname;
	public $paytoprovidersuffix;
	public $paytoproviderid;
	public $notedesc;
	public $primarypatrel;
	public $secondarypatrel;
	public $tertiarypatrel;
	public $billingprovidertaxonomy;
	public $renderingtaxonomy;
	public $billproEIN;
	public $medicalrecordnumber;
	public $primarypolicy;
	public $primaryplanname;
	public $secondarypolicy;
	public $secondaryplanname;
	public $tertiarypolicy;
	public $tertiaryplanname;
	public $tx = [];
	public $txMod;
	public $txAmount;
	public $txUnits;
	public $txCount;
	public $currentInsurance;
	public $currentFacility;
	public $currentUser;
	public $paytoproviderlname;
	public $currentPatient;
	public $currentInsuranceData;
	public $currentFormEncounter;
	public $currentForm;
	public $txmod;
	public $txunits;
	public $txamount;
}

include('vendor/autoload.php');

$parser = new X12N837(__DIR__.'/INBOX');

$database = new X12N837Database;
$parser->setDatabase($database);

$parser->processFiles();

$database->printRecordCount();