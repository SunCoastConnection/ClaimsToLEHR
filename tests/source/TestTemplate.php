<?php

namespace SunCoastConnection\ClaimsToOEMR\Tests;

use \SunCoastConnection\ClaimsToOEMR\Template;
use \SunCoastConnection\ClaimsToOEMR\Tests\BaseTestCase;

class TemplateTest extends BaseTestCase {

	protected $Template;

	public function setUp() {
		parent::setUp();

		$this->Template = $this->getMockery(
			Template::class
		)->makePartial();

	}

	public function tearDown() {
		parent::tearDown();

	}

	/**
	 * @covers SunCoastConnection\ClaimsToOEMR\Template::()
	 */
	public function test() {
		$this->markTestIncomplete('Not yet implemented');
	}

}