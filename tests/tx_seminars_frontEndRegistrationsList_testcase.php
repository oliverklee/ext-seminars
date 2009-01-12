<?php
/***************************************************************
* Copyright notice
*
* (c) 2008-2009 Niels Pardon (mail@niels-pardon.de)
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

require_once(t3lib_extMgm::extPath('oelib') . 'class.tx_oelib_Autoloader.php');

/**
 * Testcase for the front end registrations list class in the 'seminars' extension.
 *
 * @package TYPO3
 * @subpackage tx_seminars
 *
 * @author Niels Pardon <mail@niels-pardon.de>
 */
class tx_seminars_frontEndRegistrationsList_testcase extends tx_phpunit_testcase {
	/**
	 * @var tx_seminars_pi1_frontEndRegistrationsList
	 */
	private $fixture;
	/**
	 * @var tx_oelib_testingFramework
	 */
	private $testingFramework;

	/**
	 * @var integer the UID of a seminar to which the fixture relates
	 */
	private $seminarUid;

	/**
	 * @var integer the UID of a front end user for testing purposes
	 */
	private $feUserUid = 0;

	/**
	 * @var integer the UID of a registration for testing purposes
	 */
	private $registrationUid = 0;

	public function setUp() {
		tx_oelib_headerProxyFactory::getInstance()->enableTestMode();

		$this->testingFramework = new tx_oelib_testingFramework('tx_seminars');
		$this->testingFramework->createFakeFrontEnd();

		$this->seminarUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'object_type' => SEMINARS_RECORD_TYPE_COMPLETE,
				'title' => 'Test event',
				'attendees_max' => 10,
			)
		);

		$this->fixture = new tx_seminars_pi1_frontEndRegistrationsList(
			array(
				'templateFile' => 'EXT:seminars/pi1/seminars_pi1.tmpl',
				'enableRegistration' => 1,
			),
			'list_registrations',
			$this->seminarUid,
			$GLOBALS['TSFE']->cObj
		);
	}

	public function tearDown() {
		tx_oelib_headerProxyFactory::discardInstance();
		$this->testingFramework->cleanUp();
		$this->fixture->__destruct();

		unset($this->fixture, $this->testingFramework);
	}


	///////////////////////
	// Utility functions.
	///////////////////////

	/**
	 * Creates an FE user, registers them to the seminar with the UID in
	 * $this->seminarUid and logs them in.
	 *
	 * Note: This creates a registration record.
	 */
	private function createLogInAndRegisterFrontEndUser() {
		$this->feUserUid = $this->testingFramework->createAndLogInFrontEndUser();
		$this->registrationUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_ATTENDANCES,
			array(
				'seminar' => $this->seminarUid,
				'user' => $this->feUserUid,
			)
		);
	}


	/////////////////////////////////////
	// Tests for the utility functions.
	/////////////////////////////////////

	public function testCreateLogInAndRegisterFrontEndUserLogsInFrontEndUser() {
		$this->createLogInAndRegisterFrontEndUser();

		$this->assertTrue(
			$this->testingFramework->isLoggedIn()
		);
	}

	public function testCreateLogInAndRegisterFrontEndUserCreatesRegistrationRecord() {
		$this->createLogInAndRegisterFrontEndUser();

		$this->assertEquals(
			1,
			$this->testingFramework->countRecords(SEMINARS_TABLE_ATTENDANCES)
		);
	}


	////////////////////////////////////
	// Tests for creating the fixture.
	////////////////////////////////////

	public function testCreateFixtureWithInvalidWhatToDisplayThrowsException() {
		$this->setExpectedException(
			'Exception',
			'The value "foo" of the first parameter $whatToDisplay is not valid.'
		);

		new tx_seminars_pi1_frontEndRegistrationsList(
			array('templateFile' => 'EXT:seminars/pi1/seminars_pi1.tmpl'),
			'foo', 0, $GLOBALS['TSFE']->cObj
			);

	}

	public function testCreateFixtureWithListRegistrationsAsWhatToDisplayDoesNotThrowException() {
		$fixture = new tx_seminars_pi1_frontEndRegistrationsList(
			array('templateFile' => 'EXT:seminars/pi1/seminars_pi1.tmpl'),
			'list_registrations', 0, $GLOBALS['TSFE']->cObj
		);
		$fixture->__destruct();
	}

	public function testCreateFixtureWithListVipRegistrationsAsWhatToDisplayDoesNotThrowException() {
		$fixture = new tx_seminars_pi1_frontEndRegistrationsList(
			array('templateFile' => 'EXT:seminars/pi1/seminars_pi1.tmpl'),
			'list_vip_registrations', 0, $GLOBALS['TSFE']->cObj
		);
		$fixture->__destruct();
	}


	///////////////////////
	// Tests for render()
	///////////////////////

	public function testRenderContainsEventTitle() {
		$this->assertContains(
			'Test event',
			$this->fixture->render()
		);
	}

	public function testRenderWithNegativeSeminarUidReturnsHeader404() {
		$fixture = new tx_seminars_pi1_frontEndRegistrationsList(
			array('templateFile' => 'EXT:seminars/pi1/seminars_pi1.tmpl'),
			'list_registrations', -1, $GLOBALS['TSFE']->cObj
		);
		$fixture->render();
		$fixture->__destruct();

		$this->assertEquals(
			'Status: 404 Not Found',
			tx_oelib_headerProxyFactory::getInstance()->getHeaderProxy()->getLastAddedHeader()
		);
	}

	public function testRenderWithZeroSeminarUidReturnsHeader404() {
		$fixture = new tx_seminars_pi1_frontEndRegistrationsList(
			array('templateFile' => 'EXT:seminars/pi1/seminars_pi1.tmpl'),
			'list_registrations', 0, $GLOBALS['TSFE']->cObj
		);
		$fixture->render();
		$fixture->__destruct();

		$this->assertEquals(
			'Status: 404 Not Found',
			tx_oelib_headerProxyFactory::getInstance()->getHeaderProxy()->getLastAddedHeader()
		);
	}

	public function testRenderWithoutLoggedInFrontEndUserReturnsHeader403() {
		$this->fixture->render();

		$this->assertEquals(
			'Status: 403 Forbidden',
			tx_oelib_headerProxyFactory::getInstance()->getHeaderProxy()->getLastAddedHeader()
		);
	}

	public function testRenderWithLoggedInAndNotRegisteredFrontEndUserReturnsHeader403() {
		$this->testingFramework->createAndLogInFrontEndUser();
		$this->fixture->render();

		$this->assertEquals(
			'Status: 403 Forbidden',
			tx_oelib_headerProxyFactory::getInstance()->getHeaderProxy()->getLastAddedHeader()
		);
	}

	public function testRenderWithLoggedInAndRegisteredFrontEndUserDoesNotReturnHeader403() {
		$this->createLogInAndRegisterFrontEndUser();
		$this->fixture->render();

		$this->assertNotContains(
			'403',
			tx_oelib_headerProxyFactory::getInstance()->getHeaderProxy()->getLastAddedHeader()
		);
	}

	public function testRenderWithLoggedInAndRegisteredFrontEndUserCanContainHeaderForTheFrontEndUserUid() {
		$this->fixture->setConfigurationValue(
			'showFeUserFieldsInRegistrationsList', 'uid'
		);
		$this->createLogInAndRegisterFrontEndUser();

		$this->assertContains(
			'<th scope="col">Number</th>',
			$this->fixture->render()
		);
	}

	public function testRenderWithLoggedInAndRegisteredFrontEndUserCanContainTheFrontEndUserUid() {
		$this->fixture->setConfigurationValue(
			'showFeUserFieldsInRegistrationsList', 'uid'
		);
		$this->createLogInAndRegisterFrontEndUser();

		$this->assertContains(
			'<td>' . $this->feUserUid . '</td>',
			$this->fixture->render()
		);
	}

	public function testRenderWithLoggedInAndRegisteredFrontEndUserCanContainHeaderForTheFrontEndUserName() {
		$this->fixture->setConfigurationValue(
			'showFeUserFieldsInRegistrationsList', 'name'
		);
		$this->createLogInAndRegisterFrontEndUser();

		$this->assertContains(
			'<th scope="col">Name:</th>',
			$this->fixture->render()
		);
	}

	public function testRenderWithLoggedInAndRegisteredFrontEndUserCanContainTheFrontEndUserName() {
		$this->fixture->setConfigurationValue(
			'showFeUserFieldsInRegistrationsList', 'name'
		);
		$this->createLogInAndRegisterFrontEndUser();
		$this->testingFramework->changeRecord(
			'fe_users',
			$this->feUserUid,
			array('name' => 'John Doe')
		);

		$this->assertContains(
			'<td>John Doe</td>',
			$this->fixture->render()
		);
	}

	public function testRenderWithLoggedInAndRegisteredFrontEndUserCanContainHeaderForTheFrontEndUserUidAndName() {
		$this->fixture->setConfigurationValue(
			'showFeUserFieldsInRegistrationsList', 'uid,name'
		);
		$this->createLogInAndRegisterFrontEndUser();
		$result = $this->fixture->render();

		$this->assertContains(
			'<th scope="col">Number</th>',
			$result
		);
		$this->assertContains(
			'<th scope="col">Name:</th>',
			$result
		);
	}

	public function testRenderWithLoggedInAndRegisteredFrontEndUserCanContainTheFrontEndUserUidAndName() {
		$this->fixture->setConfigurationValue(
			'showFeUserFieldsInRegistrationsList', 'uid,name'
		);
		$this->createLogInAndRegisterFrontEndUser();
		$this->testingFramework->changeRecord(
			'fe_users',
			$this->feUserUid,
			array('name' => 'John Doe')
		);
		$result = $this->fixture->render();

		$this->assertContains(
			'<td>' . $this->feUserUid . '</td>',
			$result
		);
		$this->assertContains(
			'<td>John Doe</td>',
			$result
		);
	}

	public function testRenderWithLoggedInAndRegisteredFrontEndUserCanContainHeaderForTheRegistrationUid() {
		$this->fixture->setConfigurationValue(
			'showRegistrationFieldsInRegistrationList', 'uid'
		);
		$this->createLogInAndRegisterFrontEndUser();

		$this->assertContains(
			'<th scope="col">Ticket ID</th>',
			$this->fixture->render()
		);
	}

	public function testRenderWithLoggedInAndRegisteredFrontEndUserCanContainTheRegistrationUid() {
		$this->fixture->setConfigurationValue(
			'showRegistrationFieldsInRegistrationList', 'uid'
		);
		$this->createLogInAndRegisterFrontEndUser();

		$this->assertContains(
			'<td>' . $this->registrationUid . '</td>',
			$this->fixture->render()
		);
	}

	public function testRenderWithLoggedInAndRegisteredFrontEndUserCanContainHeaderForTheRegistrationSeats() {
		$this->fixture->setConfigurationValue(
			'showRegistrationFieldsInRegistrationList', 'seats'
		);
		$this->createLogInAndRegisterFrontEndUser();

		$this->assertContains(
			'<th scope="col">Seats</th>',
			$this->fixture->render()
		);
	}

	public function testRenderWithLoggedInAndRegisteredFrontEndUserCanContainTheRegistrationSeats() {
		$this->fixture->setConfigurationValue(
			'showRegistrationFieldsInRegistrationList', 'seats'
		);
		$this->createLogInAndRegisterFrontEndUser();
		$this->testingFramework->changeRecord(
			SEMINARS_TABLE_ATTENDANCES,
			$this->registrationUid,
			array('seats' => 42)
		);

		$this->assertContains(
			'<td>42</td>',
			$this->fixture->render()
		);
	}

	public function testRenderWithLoggedInAndRegisteredFrontEndUserCanContainHeaderForTheRegistrationUidAndSeats() {
		$this->fixture->setConfigurationValue(
			'showRegistrationFieldsInRegistrationList', 'uid,seats'
		);
		$this->createLogInAndRegisterFrontEndUser();

		$this->assertContains(
			'<th scope="col">Ticket ID</th>',
			$this->fixture->render()
		);
		$this->assertContains(
			'<th scope="col">Seats</th>',
			$this->fixture->render()
		);
	}

	public function testRenderWithLoggedInAndRegisteredFrontEndUserCanContainTheRegistrationUidAndSeats() {
		$this->fixture->setConfigurationValue(
			'showRegistrationFieldsInRegistrationList', 'uid,seats'
		);
		$this->createLogInAndRegisterFrontEndUser();
		$this->testingFramework->changeRecord(
			SEMINARS_TABLE_ATTENDANCES,
			$this->registrationUid,
			array('seats' => 42)
		);

		$this->assertContains(
			'<td>' . $this->registrationUid . '</td>',
			$this->fixture->render()
		);
		$this->assertContains(
			'<td>42</td>',
			$this->fixture->render()
		);
	}

	public function testRenderWithEmptyShowFeUserFieldsInRegistrationsListDoesNotContainUnresolvedLabel() {
		$this->createLogInAndRegisterFrontEndUser();
		$this->fixture->setConfigurationValue(
			'showFeUserFieldsInRegistrationsList', ''
		);

		$this->assertNotContains(
			'label_',
			$this->fixture->render()
		);
	}

	public function testRenderWithEmptyShowRegistrationFieldsInRegistrationListDoesNotContainUnresolvedLabel() {
		$this->createLogInAndRegisterFrontEndUser();
		$this->fixture->setConfigurationValue(
			'showRegistrationFieldsInRegistrationList', ''
		);

		$this->assertNotContains(
			'label_',
			$this->fixture->render()
		);
	}

	public function testRenderWithDeletedUserForRegistrationHidesUsersRegistration() {
		$this->fixture->setConfigurationValue(
			'showRegistrationFieldsInRegistrationList', 'uid'
		);

		$this->createLogInAndRegisterFrontEndUser();

		$this->testingFramework->changeRecord(
			'fe_users', $this->feUserUid, array('deleted' => 1)
		);

		$this->assertNotContains(
			(string) $this->registrationUid,
			$this->fixture->render()
		);
	}
}
?>