<?php
/***************************************************************
* Copyright notice
*
* (c) 2008 Oliver Klee (typo3-coding@oliverklee.de)
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
 * Testcase for the registration manager class in the 'seminars' extensions.
 *
 * @package		TYPO3
 * @subpackage	tx_seminars
 *
 * @author		Oliver Klee <typo3-coding@oliverklee.de>
 */

require_once(t3lib_extMgm::extPath('seminars') . 'class.tx_seminars_registrationmanager.php');
require_once(t3lib_extMgm::extPath('seminars') . 'class.tx_seminars_seminar.php');
require_once(t3lib_extMgm::extPath('seminars') . 'pi1/class.tx_seminars_pi1.php');

require_once(t3lib_extMgm::extPath('oelib') . 'class.tx_oelib_testingFramework.php');

class tx_seminars_registrationmanager_testcase extends tx_phpunit_testcase {
	private $fixture;
	private $testingFramework;

	/** a seminar to which the fixture relates */
	private $seminar = null;

	/** the UID of a fake front-end user */
	private $frontEndUserUid = 0;

	/** UID of a fake login page */
	private $loginPageUid = 0;

	/** UID of a fake registration page */
	private $registrationPageUid = 0;

	/** an instance of the Seminar Manager pi1 class */
	private $pi1 = null;

	protected function setUp() {
		$this->testingFramework
			= new tx_oelib_testingFramework('tx_seminars');
		$this->testingFramework->createFakeFrontEnd();

		$this->seminar = new tx_seminars_seminar(
			$this->testingFramework->createRecord(
				SEMINARS_TABLE_SEMINARS,
				array(
					'title' => 'test event',
					'begin_date' => mktime() + 1000,
					'end_date' => mktime() + 2000,
					'attendees_max' => 10,
				)
			)
		);

		$this->fixture = new tx_seminars_registrationmanager();
	}

	protected function tearDown() {
		$this->testingFramework->cleanUp();

		if ($this->pi1) {
			$this->pi1->__destruct();
		}
		$this->seminar->__destruct();
		$this->fixture->__destruct();
		unset(
			$this->seminar, $this->pi1, $this->fixture, $this->testingFramework
		);
	}


	//////////////////////
	// Utility functions
	//////////////////////

	/**
	 * Creates a dummy login page and registration page and stores their UIDs
	 * in $this->loginPageUid and $this->registrationPageUid.
	 *
	 * In addition, it provides the fixture's configuration with the UIDs.
	 */
	private function createFrontEndPages() {
		$this->loginPageUid = $this->testingFramework->createFrontEndPage();
		$this->registrationPageUid
			= $this->testingFramework->createFrontEndPage();

		$this->pi1 = new tx_seminars_pi1();

		$this->pi1->init(
			array(
				'isStaticTemplateLoaded' => 1,
				'loginPID' => $this->loginPageUid,
				'registerPID' => $this->registrationPageUid,
			)
		);
	}

	/**
	 * Creates a FE user, stores it UID in $this->frontEndUserUid and logs it
	 * in.
	 */
	private function createAndLogInFrontEndUser() {
		$this->frontEndUserUid = $this->testingFramework->createFrontEndUser(
			$this->testingFramework->createFrontEndUserGroup()
		);
		$this->testingFramework->loginFrontEndUser($this->frontEndUserUid);
	}


	////////////////////////////////////
	// Tests for the utility functions
	////////////////////////////////////

	public function testCreateFrontEndPagesCreatesNonZeroLoginPageUid() {
		$this->createFrontEndPages();

		$this->assertGreaterThan(
			0,
			$this->loginPageUid
		);
	}

	public function testCreateFrontEndPagesCreatesNonZeroRegistrationPageUid() {
		$this->createFrontEndPages();

		$this->assertGreaterThan(
			0,
			$this->registrationPageUid
		);
	}

	public function testCreateFrontEndPagesCreatesPi1() {
		$this->createFrontEndPages();

		$this->assertNotNull(
			$this->pi1
		);
		$this->assertTrue(
			$this->pi1 instanceof tx_seminars_pi1
		);
	}

	public function testCreateAndLogInFrontEndUserCreatesNonZeroUserUid() {
		$this->createAndLogInFrontEndUser();

		$this->assertGreaterThan(
			0,
			$this->frontEndUserUid
		);
	}

	public function testCreateAndLogInFrontEndUserLogsInFrontEndUser() {
		$this->createAndLogInFrontEndUser();
		$this->assertTrue(
			$this->testingFramework->isLoggedIn()
		);
	}


	/////////////////////////////////////////////////
	// Tests for the link to the registration page
	/////////////////////////////////////////////////

	public function testGetLinkToRegistrationOrLoginPageWithLoggedOutUserCreatesLinkTag() {
		$this->testingFramework->logoutFrontEndUser();
		$this->createFrontEndPages();

		$this->assertContains(
			'<a ',
			$this->fixture->getLinkToRegistrationOrLoginPage(
				$this->pi1, $this->seminar
			)
		);
	}

	public function testGetLinkToRegistrationOrLoginPageWithLoggedOutUserCreatesLinkToLoginPage() {
		$this->testingFramework->logoutFrontEndUser();
		$this->createFrontEndPages();

		$this->assertContains(
			'?id=' . $this->loginPageUid,
			$this->fixture->getLinkToRegistrationOrLoginPage(
				$this->pi1, $this->seminar
			)
		);
	}

	public function testGetLinkToRegistrationOrLoginPageWithLoggedOutUserContainsRedirectWithEventUid() {
		$this->testingFramework->logoutFrontEndUser();
		$this->createFrontEndPages();

		$this->assertContains(
			'redirect_url',
			$this->fixture->getLinkToRegistrationOrLoginPage(
				$this->pi1, $this->seminar
			)
		);
		$this->assertContains(
			'%255Bseminar%255D%3D' . $this->seminar->getUid(),
			$this->fixture->getLinkToRegistrationOrLoginPage(
				$this->pi1, $this->seminar
			)
		);
	}

	public function testGetLinkToRegistrationOrLoginPageWithLoggedInUserCreatesLinkTag() {
		$this->createFrontEndPages();
		$this->createAndLogInFrontEndUser();

		$this->assertContains(
			'<a ',
			$this->fixture->getLinkToRegistrationOrLoginPage(
				$this->pi1, $this->seminar
			)
		);
	}

	public function testGetLinkToRegistrationOrLoginPageWithLoggedInUserCreatesLinkToRegistrationPageWithEventUid() {
		$this->createFrontEndPages();
		$this->createAndLogInFrontEndUser();

		$this->assertContains(
			'?id=' . $this->registrationPageUid,
			$this->fixture->getLinkToRegistrationOrLoginPage(
				$this->pi1, $this->seminar
			)
		);
		$this->assertContains(
			'[seminar]=' . $this->seminar->getUid(),
			$this->fixture->getLinkToRegistrationOrLoginPage(
				$this->pi1, $this->seminar
			)
		);
	}

	public function testGetLinkToRegistrationOrLoginPageWithLoggedInUserDoesNotContainRedirect() {
		$this->createFrontEndPages();
		$this->createAndLogInFrontEndUser();

		$this->assertNotContains(
			'redirect_url',
			$this->fixture->getLinkToRegistrationOrLoginPage(
				$this->pi1, $this->seminar
			)
		);
	}

}
?>