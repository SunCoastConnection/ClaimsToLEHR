<?php

namespace SunCoastConnection\ClaimsToOEMR\Tests\Document;

use \Illuminate\Config\Repository;
use \SunCoastConnection\ClaimsToOEMR\Tests\BaseTestCase;
use \SunCoastConnection\ClaimsToOEMR\Document\Options;

class OptionsTest extends BaseTestCase {

	/**
	 * @covers SunCoastConnection\ClaimsToOEMR\Document\Options::getInstance()
	 */
	public function testGetInstance() {
		$options = Options::getInstance([]);

		$this->assertInstanceOf(
			Options::class,
			$options,
			'Expected new instance of '.Options::class.'.'
		);

		$this->assertInstanceOf(
			Repository::class,
			$options,
			'Expected instance to extend '.Repository::class.'.'
		);
	}

	/**
	 * @covers SunCoastConnection\ClaimsToOEMR\Document\Options::getSubset()
	 */
	public function testGetSubset() {
		$testValue = [
			'a' => 1,
			'2' => 'b',
			'set' => [
				'c' => 3,
				'4' => 'd'
			]
		];

		$options = Options::getInstance($testValue);

		$this->assertEquals(
			$testValue['set'],
			$options->getSubset('set')->all(),
			'Subset not returned correctly'
		);
	}
}