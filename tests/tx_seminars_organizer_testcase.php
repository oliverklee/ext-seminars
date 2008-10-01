<?php
/***************************************************************
* Copyright notice
*
* (c) 2007-2008 Oliver Klee (typo3-coding@oliverklee.de)
* All rights reserved
*
* This script is part of the TYPO3 project. The TYPO3 project is
* free software; you can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation; either version 2 of the License, or
* (at your option) any later version.
*
* The GNU General Public License can be found at
* http://www.gnu.org/copyleft/gpl.html.
*
* This script is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
* GNU General Public License for more details.
*
* This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/

/**
 * Testcase for the organizer class in the 'seminars' extensions.
 *
 * @package		TYPO3
 * @subpackage	tx_seminars
 *
 * @author		Oliver Klee <typo3-coding@oliverklee.de>
 */

require_once(t3lib_extMgm::extPath('seminars').'lib/tx_seminars_constants.php');
require_once(t3lib_extMgm::extPath('seminars').'class.tx_seminars_organizer.php');

require_once(t3lib_extMgm::extPath('oelib').'class.tx_oelib_testingFramework.php');

class tx_seminars_organizer_testcase extends tx_phpunit_testcase {
	private $fixture;
	private $testingFramework;

	/** a maximal filled organizer */
	private $maximalFixture;

	public function setUp() {
		$this->testingFramework = new tx_oelib_testingFramework('tx_seminars');
		$fixtureUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_ORGANIZERS,
			array(
				'title' => 'Test organizer',
				'email' => 'foo@test.com'
			)
		);
		$this->fixture = new tx_seminars_organizer($fixtureUid);

		$maximalFixtureUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_ORGANIZERS,
			array(
				'title' => 'Test organizer',
				'homepage' => 'http://www.test.com/',
				'email' => 'maximal-foo@test.com',
				'email_footer' => 'line 1'.LF.'line 2',
				'attendances_pid' => 99
			)
		);
		$this->maximalFixture = new tx_seminars_organizer($maximalFixtureUid);
	}

	public function tearDown() {
		$this->testingFramework->cleanUp();

		$this->fixture->__destruct();
		unset($this->fixture, $this->testingFramework);
	}


	//////////////////////////////////////////
	// Tests for creating organizer objects.
	//////////////////////////////////////////

	public function testCreateFromUid() {
		$this->assertTrue(
			$this->fixture->isOk()
		);
	}


	////////////////////////////////////////////////
	// Tests for getting the organizer attributes.
	////////////////////////////////////////////////

	public function testHasHomepageWithEmptyHomepageReturnsFalse() {
		$this->assertFalse(
			$this->fixture->hasHomepage()
		);
	}

	public function testHasHomepageWithHomepageReturnsTrue() {
		$this->assertTrue(
			$this->maximalFixture->hasHomepage()
		);
	}

	public function testGetHomepage() {
		$this->assertEquals(
			'',
			$this->fixture->getHomepage()
		);
		$this->assertEquals(
			'http://www.test.com/',
			$this->maximalFixture->getHomepage()
		);
	}

	public function testGetEmail() {
		$this->assertEquals(
			'foo@test.com',
			$this->fixture->getEmail()
		);
		$this->assertEquals(
			'maximal-foo@test.com',
			$this->maximalFixture->getEmail()
		);
	}

	public function testGetEmailFooter() {
		$this->assertEquals(
			'',
			$this->fixture->getEmailFooter()
		);
		$this->assertEquals(
			'line 1'.LF.'line 2',
			$this->maximalFixture->getEmailFooter()
		);
	}

	public function testGetAttendancesPidWithNoAttendancesPidReturnsZero() {
		$this->assertEquals(
			0,
			$this->fixture->getAttendancesPid()
		);
	}

	public function testGetAttendancesPidWithAttendancesPidReturnsAttendancesPid() {
		$this->assertEquals(
			99,
			$this->maximalFixture->getAttendancesPid()
		);
	}
}
?>