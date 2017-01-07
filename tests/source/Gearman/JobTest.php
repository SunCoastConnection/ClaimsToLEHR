<?php

namespace SunCoastConnection\ClaimsToEMR\Tests;

use \SunCoastConnection\ClaimsToEMR\Document\Options;
use \SunCoastConnection\ClaimsToEMR\Gearman\Job;
use \SunCoastConnection\ClaimsToEMR\Tests\BaseTestCase;
use \SunCoastConnection\ClaimsToEMR\Tests\Gearman\JobMock;

class JobTest extends BaseTestCase {

	protected $job;

	public function setUp() {
		parent::setUp();

		$this->job = $this->getMockery(
			Job::class
		)->makePartial();
	}

	/**
	 * @covers SunCoastConnection\ClaimsToEMR\Gearman\Job::getInstance()
	 */
	public function testGetInstance() {
		$options = $this->getMockery(
			Options::class
		);

		$job = JobMock::getInstance($options);

		$this->assertInstanceOf(
			Job::class,
			$job,
			'Expected new instance of '.Job::class.'.'
		);
	}

	/**
	 * @covers SunCoastConnection\ClaimsToEMR\Gearman\Job::__construct()
	 */
	public function testConstructWith() {
		$options = $this->getMockery(
			Options::class
		);

		$this->job->shouldAllowMockingProtectedMethods();

		$this->job->shouldReceive('options')
			->once()
			->with($options);

		$this->job->__construct($options);
	}

	/**
	 * @covers SunCoastConnection\ClaimsToEMR\Gearman\Job::options()
	 */
	public function testOptions() {
		$this->assertNull(
			$this->job->options(),
			'Options should return null when empty.'
		);

		$options = $this->getMockery(
			Options::class
		);

		$this->assertSame(
			$options,
			$this->job->options($options),
			'Options should return set option object when setting value.'
		);

		$this->assertSame(
			$options,
			$this->job->options(),
			'Options should return set option object after setting value.'
		);
	}

}