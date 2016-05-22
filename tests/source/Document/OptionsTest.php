<?php

namespace SunCoastConnection\ClaimsToOEMR\Tests\Document;

use \Illuminate\Config\Repository,
	\SunCoastConnection\ClaimsToOEMR\Tests\BaseTestCase,
	\SunCoastConnection\ClaimsToOEMR\Document\Options;

class OptionsTest extends BaseTestCase {

	/**
	 * @covers SunCoastConnection\ClaimsToOEMR\Document\Options::getNew()
	 */
	public function testGetNew() {
		$options = Options::getNew([]);

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