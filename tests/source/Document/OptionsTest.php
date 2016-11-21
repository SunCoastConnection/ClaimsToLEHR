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

}