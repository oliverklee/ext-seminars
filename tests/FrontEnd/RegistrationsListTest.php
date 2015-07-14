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
 * @author Niels Pardon <mail@niels-pardon.de>
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class tx_seminars_FrontEnd_RegistrationsListTest extends tx_phpunit_testcase {
	/**
	 * @var tx_seminars_FrontEnd_RegistrationsList
	 */
	private $fixture;
	/**
	 * @var tx_oelib_testingFramework
	 */
	private $testingFramework;

	/**
	 * @var int the UID of a seminar to which the fixture relates
	 */
	private $seminarUid;

	/**
	 * @var int the UID of a front end user for testing purposes
	 */
	private $feUserUid = 0;

	/**
	 * @var int the UID of a registration for testing purposes
	 */
	private $registrationUid = 0;

	protected function setUp() {
		tx_oelib_configurationProxy::getInstance('seminars')->setAsBoolean('enableConfigCheck', FALSE);

		tx_oelib_headerProxyFactory::getInstance()->enableTestMode();

		$this->testingFramework = new tx_oelib_testingFramework('tx_seminars');
		$this->testingFramework->createFakeFrontEnd();

		$this->seminarUid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array(
				'object_type' => tx_seminars_Model_Event::TYPE_COMPLETE,
				'title' => 'Test event & more',
				'attendees_max' => 10,
				'needs_registration' => 1,
			)
		);

		$this->fixture = new tx_seminars_FrontEnd_RegistrationsList(
			array(
				'templateFile' => 'EXT:seminars/Resources/Private/Templates/FrontEnd/FrontEnd.html',
				'enableRegistration' => 1,
			),
			'list_registrations',
			$this->seminarUid,
			$GLOBALS['TSFE']->cObj
		);
	}

	protected function tearDown() {
		$this->testingFramework->cleanUp();

		tx_seminars_registrationmanager::purgeInstance();
	}


	///////////////////////
	// Utility functions.
	///////////////////////

	/**
	 * Creates an FE user, registers them to the seminar with the UID in
	 * $this->seminarUid and logs them in.
	 *
	 * Note: This function creates a registration record.
	 *
	 * @return void
	 */
	private function createLogInAndRegisterFrontEndUser() {
		$this->feUserUid = $this->testingFramework->createAndLoginFrontEndUser(
			'', array('name' => 'Tom & Jerry')
		);
		$this->registrationUid = $this->testingFramework->createRecord(
			'tx_seminars_attendances',
			array(
				'seminar' => $this->seminarUid,
				'user' => $this->feUserUid,
			)
		);
	}


	/////////////////////////////////////
	// Tests for the utility functions.
	/////////////////////////////////////

	/**
	 * @test
	 */
	public function createLogInAndRegisterFrontEndUserLogsInFrontEndUser() {
		$this->createLogInAndRegisterFrontEndUser();

		self::assertTrue(
			$this->testingFramework->isLoggedIn()
		);
	}

	/**
	 * @test
	 */
	public function createLogInAndRegisterFrontEndUserCreatesRegistrationRecord() {
		$this->createLogInAndRegisterFrontEndUser();

		self::assertTrue(
			$this->testingFramework->existsExactlyOneRecord(
				'tx_seminars_attendances'
			)
		);
	}


	////////////////////////////////////
	// Tests for creating the fixture.
	////////////////////////////////////

	/**
	 * @test
	 */
	public function createFixtureWithInvalidWhatToDisplayThrowsException() {
		$this->setExpectedException(
			'InvalidArgumentException',
			'The value "foo" of the first parameter $whatToDisplay is not valid.'
		);

		new tx_seminars_FrontEnd_RegistrationsList(
			array('templateFile' => 'EXT:seminars/Resources/Private/Templates/FrontEnd/FrontEnd.html'),
			'foo', 0, $GLOBALS['TSFE']->cObj
		);
	}

	/**
	 * @test
	 */
	public function createFixtureWithListRegistrationsAsWhatToDisplayDoesNotThrowException() {
		$fixture = new tx_seminars_FrontEnd_RegistrationsList(
			array('templateFile' => 'EXT:seminars/Resources/Private/Templates/FrontEnd/FrontEnd.html'),
			'list_registrations', 0, $GLOBALS['TSFE']->cObj
		);
	}

	/**
	 * @test
	 */
	public function createFixtureWithListVipRegistrationsAsWhatToDisplayDoesNotThrowException() {
		$fixture = new tx_seminars_FrontEnd_RegistrationsList(
			array('templateFile' => 'EXT:seminars/Resources/Private/Templates/FrontEnd/FrontEnd.html'),
			'list_vip_registrations', 0, $GLOBALS['TSFE']->cObj
		);
	}


	///////////////////////
	// Tests for render()
	///////////////////////

	/**
	 * @test
	 */
	public function renderContainsHtmlspecialcharedEventTitle() {
		self::assertContains(
			'Test event &amp; more',
			$this->fixture->render()
		);
	}

	/**
	 * @test
	 */
	public function renderWithNegativeSeminarUidReturnsHeader404() {
		$fixture = new tx_seminars_FrontEnd_RegistrationsList(
			array('templateFile' => 'EXT:seminars/Resources/Private/Templates/FrontEnd/FrontEnd.html'),
			'list_registrations', -1, $GLOBALS['TSFE']->cObj
		);
		$fixture->render();

		self::assertEquals(
			'Status: 404 Not Found',
			tx_oelib_headerProxyFactory::getInstance()->getHeaderProxy()->getLastAddedHeader()
		);
	}

	/**
	 * @test
	 */
	public function renderWithZeroSeminarUidReturnsHeader404() {
		$fixture = new tx_seminars_FrontEnd_RegistrationsList(
			array('templateFile' => 'EXT:seminars/Resources/Private/Templates/FrontEnd/FrontEnd.html'),
			'list_registrations', 0, $GLOBALS['TSFE']->cObj
		);
		$fixture->render();

		self::assertEquals(
			'Status: 404 Not Found',
			tx_oelib_headerProxyFactory::getInstance()->getHeaderProxy()->getLastAddedHeader()
		);
	}

	/**
	 * @test
	 */
	public function renderWithoutLoggedInFrontEndUserReturnsHeader403() {
		$this->fixture->render();

		self::assertEquals(
			'Status: 403 Forbidden',
			tx_oelib_headerProxyFactory::getInstance()->getHeaderProxy()->getLastAddedHeader()
		);
	}

	/**
	 * @test
	 */
	public function renderWithLoggedInAndNotRegisteredFrontEndUserReturnsHeader403() {
		$this->testingFramework->createFrontEndUser();
		$this->fixture->render();

		self::assertEquals(
			'Status: 403 Forbidden',
			tx_oelib_headerProxyFactory::getInstance()->getHeaderProxy()->getLastAddedHeader()
		);
	}

	/**
	 * @test
	 */
	public function renderWithLoggedInAndRegisteredFrontEndUserDoesNotReturnHeader403() {
		$this->createLogInAndRegisterFrontEndUser();
		$this->fixture->render();

		self::assertNotContains(
			'403',
			tx_oelib_headerProxyFactory::getInstance()->getHeaderProxy()->getLastAddedHeader()
		);
	}

	/**
	 * @test
	 */
	public function renderWithLoggedInAndRegisteredFrontEndUserCanContainHeaderForTheFrontEndUserUid() {
		$this->fixture->setConfigurationValue(
			'showFeUserFieldsInRegistrationsList', 'uid'
		);
		$this->createLogInAndRegisterFrontEndUser();

		self::assertContains(
			'<th scope="col">Number</th>',
			$this->fixture->render()
		);
	}

	/**
	 * @test
	 */
	public function renderWithLoggedInAndRegisteredFrontEndUserCanContainTheFrontEndUserUid() {
		$this->fixture->setConfigurationValue(
			'showFeUserFieldsInRegistrationsList', 'uid'
		);
		$this->createLogInAndRegisterFrontEndUser();

		self::assertContains(
			'<td>' . $this->feUserUid . '</td>',
			$this->fixture->render()
		);
	}

	/**
	 * @test
	 */
	public function renderWithLoggedInAndRegisteredFrontEndUserCanContainHeaderForTheFrontEndUserName() {
		$this->fixture->setConfigurationValue(
			'showFeUserFieldsInRegistrationsList', 'name'
		);
		$this->createLogInAndRegisterFrontEndUser();

		self::assertContains(
			'<th scope="col">Name:</th>',
			$this->fixture->render()
		);
	}

	/**
	 * @test
	 */
	public function renderWithLoggedInAndRegisteredFrontEndUserCanContainTheFrontEndUserName() {
		$this->fixture->setConfigurationValue(
			'showFeUserFieldsInRegistrationsList', 'name'
		);
		$this->createLogInAndRegisterFrontEndUser();

		self::assertContains(
			'<td>Tom &amp; Jerry</td>',
			$this->fixture->render()
		);
	}

	/**
	 * @test
	 */
	public function renderWithLoggedInAndRegisteredFrontEndUserCanContainHeaderForTheFrontEndUserUidAndName() {
		$this->fixture->setConfigurationValue(
			'showFeUserFieldsInRegistrationsList', 'uid,name'
		);
		$this->createLogInAndRegisterFrontEndUser();
		$result = $this->fixture->render();

		self::assertContains(
			'<th scope="col">Number</th>',
			$result
		);
		self::assertContains(
			'<th scope="col">Name:</th>',
			$result
		);
	}

	/**
	 * @test
	 */
	public function renderWithLoggedInAndRegisteredFrontEndUserCanContainTheFrontEndUserUidAndName() {
		$this->fixture->setConfigurationValue(
			'showFeUserFieldsInRegistrationsList', 'uid,name'
		);
		$this->createLogInAndRegisterFrontEndUser();
		$this->testingFramework->changeRecord(
			'fe_users',
			$this->feUserUid,
			array('name' => 'Tom & Jerry')
		);
		$result = $this->fixture->render();

		self::assertContains(
			'<td>' . $this->feUserUid . '</td>',
			$result
		);
		self::assertContains(
			'<td>Tom &amp; Jerry</td>',
			$result
		);
	}

	/**
	 * @test
	 */
	public function renderWithLoggedInAndRegisteredFrontEndUserCanContainHeaderForTheRegistrationUid() {
		$this->fixture->setConfigurationValue(
			'showRegistrationFieldsInRegistrationList', 'uid'
		);
		$this->createLogInAndRegisterFrontEndUser();

		self::assertContains(
			'<th scope="col">Ticket ID</th>',
			$this->fixture->render()
		);
	}

	/**
	 * @test
	 */
	public function renderWithLoggedInAndRegisteredFrontEndUserCanContainTheRegistrationUid() {
		$this->fixture->setConfigurationValue(
			'showRegistrationFieldsInRegistrationList', 'uid'
		);
		$this->createLogInAndRegisterFrontEndUser();

		self::assertContains(
			'<td>' . $this->registrationUid . '</td>',
			$this->fixture->render()
		);
	}

	/**
	 * @test
	 */
	public function renderWithLoggedInAndRegisteredFrontEndUserCanContainHeaderForTheRegistrationSeats() {
		$this->fixture->setConfigurationValue(
			'showRegistrationFieldsInRegistrationList', 'seats'
		);
		$this->createLogInAndRegisterFrontEndUser();

		self::assertContains(
			'<th scope="col">Seats</th>',
			$this->fixture->render()
		);
	}

	/**
	 * @test
	 */
	public function renderWithLoggedInAndRegisteredFrontEndUserCanContainTheRegistrationSeats() {
		$this->fixture->setConfigurationValue(
			'showRegistrationFieldsInRegistrationList', 'seats'
		);
		$this->createLogInAndRegisterFrontEndUser();
		$this->testingFramework->changeRecord(
			'tx_seminars_attendances',
			$this->registrationUid,
			array('seats' => 42)
		);

		self::assertContains(
			'<td>42</td>',
			$this->fixture->render()
		);
	}

	/**
	 * @test
	 */
	public function renderCanContainTheRegistrationInterests() {
		$this->fixture->setConfigurationValue(
			'showRegistrationFieldsInRegistrationList', 'interests'
		);
		$this->createLogInAndRegisterFrontEndUser();
		$this->testingFramework->changeRecord(
			'tx_seminars_attendances',
			$this->registrationUid,
			array('interests' => 'everything practical & theoretical',)
		);

		self::assertContains(
			'<td>everything practical &amp; theoretical</td>',
			$this->fixture->render()
		);
	}

	/**
	 * @test
	 */
	public function renderWithLoggedInAndRegisteredFrontEndUserCanContainHeaderForTheRegistrationUidAndSeats() {
		$this->fixture->setConfigurationValue(
			'showRegistrationFieldsInRegistrationList', 'uid,seats'
		);
		$this->createLogInAndRegisterFrontEndUser();

		self::assertContains(
			'<th scope="col">Ticket ID</th>',
			$this->fixture->render()
		);
		self::assertContains(
			'<th scope="col">Seats</th>',
			$this->fixture->render()
		);
	}

	/**
	 * @test
	 */
	public function renderWithLoggedInAndRegisteredFrontEndUserCanContainTheRegistrationUidAndSeats() {
		$this->fixture->setConfigurationValue(
			'showRegistrationFieldsInRegistrationList', 'uid,seats'
		);
		$this->createLogInAndRegisterFrontEndUser();
		$this->testingFramework->changeRecord(
			'tx_seminars_attendances',
			$this->registrationUid,
			array('seats' => 42)
		);

		self::assertContains(
			'<td>' . $this->registrationUid . '</td>',
			$this->fixture->render()
		);
		self::assertContains(
			'<td>42</td>',
			$this->fixture->render()
		);
	}

	/**
	 * @test
	 */
	public function renderWithEmptyShowFeUserFieldsInRegistrationsListDoesNotContainUnresolvedLabel() {
		$this->createLogInAndRegisterFrontEndUser();
		$this->fixture->setConfigurationValue(
			'showFeUserFieldsInRegistrationsList', ''
		);

		self::assertNotContains(
			'label_',
			$this->fixture->render()
		);
	}

	/**
	 * @test
	 */
	public function renderWithEmptyShowRegistrationFieldsInRegistrationListDoesNotContainUnresolvedLabel() {
		$this->createLogInAndRegisterFrontEndUser();
		$this->fixture->setConfigurationValue(
			'showRegistrationFieldsInRegistrationList', ''
		);

		self::assertNotContains(
			'label_',
			$this->fixture->render()
		);
	}

	/**
	 * @test
	 */
	public function renderWithDeletedUserForRegistrationHidesUsersRegistration() {
		$this->fixture->setConfigurationValue(
			'showRegistrationFieldsInRegistrationList', 'uid'
		);

		$this->createLogInAndRegisterFrontEndUser();

		$this->testingFramework->changeRecord(
			'fe_users', $this->feUserUid, array('deleted' => 1)
		);

		self::assertNotContains(
			(string) $this->registrationUid,
			$this->fixture->render()
		);
	}

	/**
	 * @test
	 */
	public function renderSeparatesMultipleRegistrationsWithTableRows() {
		$this->fixture->setConfigurationValue(
			'showRegistrationFieldsInRegistrationList', 'uid'
		);
		$this->createLogInAndRegisterFrontEndUser();

		$feUserUid = $this->testingFramework->createFrontEndUser();
		$secondRegistration = $this->testingFramework->createRecord(
			'tx_seminars_attendances',
			array(
				'seminar' => $this->seminarUid,
				'user' => $feUserUid,
				'crdate' => $GLOBALS['SIM_EXEC_TIME'] + 500,
			)
		);

		self::assertRegExp(
			'/' . $this->registrationUid . '<\/td>.*<\/tr>' .
				'.*<tr>.*<td>' . $secondRegistration . '/s',
			$this->fixture->render()
		);
	}


	///////////////////////////////////////////////////////
	// Tests concerning registrations on the waiting list
	///////////////////////////////////////////////////////

	/**
	 * @test
	 */
	public function renderForNoWaitingListRegistrationsNotContainsWaitingListLabel() {
		self::assertNotContains(
			$this->fixture->translate('label_waiting_list'),
			$this->fixture->render()
		);
	}

	/**
	 * @test
	 */
	public function renderForWaitingListRegistrationsContainsWaitingListLabel() {
		$this->fixture->setConfigurationValue(
			'showRegistrationFieldsInRegistrationList', 'uid'
		);
		$this->createLogInAndRegisterFrontEndUser();

		$feUserUid = $this->testingFramework->createFrontEndUser();
		$this->testingFramework->createRecord(
			'tx_seminars_attendances',
			array(
				'seminar' => $this->seminarUid,
				'user' => $feUserUid,
				'registration_queue' => 1,
			)
		);

		self::assertContains(
			$this->fixture->translate('label_waiting_list'),
			$this->fixture->render()
		);
	}

	/**
	 * @test
	 */
	public function renderCanContainWaitingListRegistrations() {
		$this->fixture->setConfigurationValue(
			'showRegistrationFieldsInRegistrationList', 'uid'
		);
		$this->createLogInAndRegisterFrontEndUser();

		$feUserUid = $this->testingFramework->createFrontEndUser();
		$secondRegistration = $this->testingFramework->createRecord(
			'tx_seminars_attendances',
			array(
				'seminar' => $this->seminarUid,
				'user' => $feUserUid,
				'registration_queue' => 1,
			)
		);

		self::assertRegExp(
			'/<td>' . $secondRegistration . '/s',
			$this->fixture->render()
		);
	}
}