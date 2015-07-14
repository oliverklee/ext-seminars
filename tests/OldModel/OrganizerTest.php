<?php
/*
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

/**
 * Test case.
 *
 * @package TYPO3
 * @subpackage tx_seminars
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class tx_seminars_OldModel_OrganizerTest extends tx_phpunit_testcase {
	/**
	 * @var tx_seminars_OldModel_Organizer
	 */
	private $fixture;
	/**
	 * @var tx_oelib_testingFramework
	 */
	private $testingFramework;
	/**
	 * a maximal filled organizer
	 *
	 * @var tx_seminars_OldModel_Organizer
	 */
	private $maximalFixture;

	protected function setUp() {
		$this->testingFramework = new tx_oelib_testingFramework('tx_seminars');
		$fixtureUid = $this->testingFramework->createRecord(
			'tx_seminars_organizers',
			array(
				'title' => 'Test organizer',
				'email' => 'foo@test.com'
			)
		);
		$this->fixture = new tx_seminars_OldModel_Organizer($fixtureUid);

		$maximalFixtureUid = $this->testingFramework->createRecord(
			'tx_seminars_organizers',
			array(
				'title' => 'Test organizer',
				'homepage' => 'http://www.test.com/',
				'email' => 'maximal-foo@test.com',
				'email_footer' => 'line 1'.LF.'line 2',
				'attendances_pid' => 99,
				'description' => 'foo',
			)
		);
		$this->maximalFixture = new tx_seminars_OldModel_Organizer($maximalFixtureUid);
	}

	protected function tearDown() {
		$this->testingFramework->cleanUp();
	}


	//////////////////////////////////////////
	// Tests for creating organizer objects.
	//////////////////////////////////////////

	public function testCreateFromUid() {
		self::assertTrue(
			$this->fixture->isOk()
		);
	}


	////////////////////////////////////////////////
	// Tests for getting the organizer attributes.
	////////////////////////////////////////////////

	/**
	 * @test
	 */
	public function getNameWithNameReturnsName() {
		self::assertEquals(
			'Test organizer',
			$this->fixture->getName()
		);
	}

	public function testHasHomepageWithEmptyHomepageReturnsFalse() {
		self::assertFalse(
			$this->fixture->hasHomepage()
		);
	}

	public function testHasHomepageWithHomepageReturnsTrue() {
		self::assertTrue(
			$this->maximalFixture->hasHomepage()
		);
	}

	public function testGetHomepage() {
		self::assertEquals(
			'',
			$this->fixture->getHomepage()
		);
		self::assertEquals(
			'http://www.test.com/',
			$this->maximalFixture->getHomepage()
		);
	}

	/**
	 * @test
	 */
	public function getEmailFooterForEmptyFooterReturnsEmptyString() {
		self::assertEquals(
			'',
			$this->fixture->getEmailFooter()
		);
	}

	/**
	 * @test
	 */
	public function getEmailFooterForNonEmptyFooterReturnsThisFooter() {
		self::assertEquals(
			'line 1'.LF.'line 2',
			$this->maximalFixture->getEmailFooter()
		);
	}

	/**
	 * @test
	 */
	public function getEMailAddressWithEMailAddressReturnsEMailAddress() {
		self::assertEquals(
			'foo@test.com',
			$this->fixture->getEMailAddress()
		);
	}

	public function testGetAttendancesPidWithNoAttendancesPidReturnsZero() {
		self::assertEquals(
			0,
			$this->fixture->getAttendancesPid()
		);
	}

	public function testGetAttendancesPidWithAttendancesPidReturnsAttendancesPid() {
		self::assertEquals(
			99,
			$this->maximalFixture->getAttendancesPid()
		);
	}


	/////////////////////////////////////
	// Tests concerning the description
	/////////////////////////////////////

	/**
	 * @test
	 */
	public function hasDescriptionForOrganizerWithoutDescriptionReturnsFalse() {
		self::assertFalse(
			$this->fixture->hasDescription()
		);
	}

	/**
	 * @test
	 */
	public function hasDescriptionForOrganizerWithDescriptionReturnsTrue() {
		self::assertTrue(
			$this->maximalFixture->hasDescription()
		);
	}

	/**
	 * @test
	 */
	public function getDescriptionForOrganizerWithoutDescriptionReturnsEmptyString() {
		self::assertEquals(
			'',
			$this->fixture->getDescription()
		);
	}

	/**
	 * @test
	 */
	public function getDescriptionForOrganizerWithDescriptionReturnsDescription() {
		self::assertEquals(
			'foo',
			$this->maximalFixture->getDescription()
		);
	}
}